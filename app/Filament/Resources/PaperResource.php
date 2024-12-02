<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaperResource\Pages;
use App\Mail\Visualbuilder\EmailTemplates\PaperAcceptedNotification as EmailTemplatesPaperAcceptedNotification;
use App\Mail\Visualbuilder\EmailTemplates\PaperAssignedNotification as EmailTemplatesPaperAssignedNotification;
use App\Mail\Visualbuilder\EmailTemplates\PaperMajorReview;
use App\Mail\Visualbuilder\EmailTemplates\PaperMinorReview;
use App\Mail\Visualbuilder\EmailTemplates\PaperNotAccepted;
use App\Mail\Visualbuilder\EmailTemplates\RefereeAccess;
use App\Models\Field;
use App\Models\Paper;
use App\Models\Review;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;

class PaperResource extends Resource
{
    protected static ?string $model = Paper::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('abstract')
                    ->required(),

                Forms\Components\TextInput::make('keywords')
                    ->label('Keywords')
                    ->helperText('Enter keywords separated by commas')
                    ->required(),

                Forms\Components\FileUpload::make('file_path')
                    ->label('Paper File')
                    ->required()
                    ->acceptedFileTypes(['application/pdf', 'application/x-latex']),
                // Conditional visibility for revision file upload
                Forms\Components\FileUpload::make('revision_file')
                    ->label('Upload Revision File')
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->visible(fn($get) => $get('status') === 'ready_for_major_revision')
                    ->helperText('Upload the revised file based on reviewer feedback'),
                Forms\Components\CheckboxList::make('fields')
                    ->label('Fields')
                    ->options(Field::pluck('name', 'id')->toArray()) // Load field options dynamically
                    ->required()
                    ->helperText('Select relevant fields for the paper')
                    ->required()
                    ->helperText('Select relevant fields for the paper'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('author.name')->label('Author')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('status')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Submitted At')->dateTime()->sortable(),
            ])->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // Assign to Associate Editor
                Action::make('assignToAssociateEditor')
                    ->label('Assign to Associate Editor')
                    ->visible(fn($record) => auth()->user()->hasRole('editor') && $record->status === 'submitted')
                    ->action(function ($record, $data) {
                        $associateEditor = User::find($data['associate_editor_id']);
                        $record->associate_editor_id = $associateEditor->id;
                        $record->status = 'under_review';
                        $record->save();
                        $tokenHelper = app(TokenHelperInterface::class);
                        $paper = (object) [
                            'title' => $record->title,
                            'name' => $associateEditor->name,
                            'email' => $associateEditor->email,
                            'link' => url('/admin/papers/' . $record->id),
                        ];
                        Mail::to($associateEditor->email)->send(new EmailTemplatesPaperAssignedNotification($paper, $tokenHelper));
                    })
                    ->form([
                        Forms\Components\Select::make('associate_editor_id')
                            ->label('Select Associate Editor')
                            ->options(User::role('associate_editor')->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->button(),

                // Assign Referees or Quick Reject
                Action::make('assignRefereesOrQuickReject')
                    ->label('Assign Referees / Quick Reject')
                    ->visible(fn($record) => auth()->user()->hasRole('associate_editor') && $record->status === 'under_review' || $record->status === 'ready_for_major_revision')
                    ->action(function ($record, $data) {
                        if ($data['decision'] === 'reject') {
                            // Handle rejection
                            $record->status = 'rejected';
                            $record->revision_comment = $data['revision_comment']; // Save the revision comment
                            $record->save();

                            $tokenHelper = app(TokenHelperInterface::class);
                            $paper = (object) [
                                'title' => $record->title,
                                'name' => $record->author->name,
                                'reason' => $data['revision_comment'],
                                'email' => $record->author->email,
                                'comment' => $data['revision_comment'],
                            ];
                            Mail::to($record->author->email)->send(new PaperNotAccepted($paper, $tokenHelper));
                        } else {
                            // Handle referee assignment
                            $record->status = 'under_review';
                            $record->save();

                            $existingReferees = $record->reviews->pluck('referee_id')->toArray(); // Get already assigned referees

                            foreach ($data['referees'] as $refereeId) {
                                if (in_array($refereeId, $existingReferees)) {
                                    continue; // Skip already assigned referees
                                }

                                $referee = User::find($refereeId);
                                $review = Review::create([
                                    'paper_id' => $record->id,
                                    'referee_id' => $referee->id,
                                    'comments' => 'Pending review comments',
                                ]);

                                $tokenHelper = app(TokenHelperInterface::class);
                                $reviewData = (object) [
                                    'title' => $record->title,
                                    'name' => $referee->name,
                                    'author' => $record->author->name,
                                    'email' => $referee->email,
                                    'abstract' => $record->abstract,
                                    'accept_url' => url('admin/reviews/' . $review->id . '?paper_id=' . $record->id . '&referee_id=' . $referee->id),
                                    'decline_url' => route('review.reject', $review),
                                ];
                                Mail::to($referee->email)->send(new RefereeAccess($reviewData, $tokenHelper));
                            }
                        }
                    })
                    ->form([
                        Forms\Components\Select::make('decision')
                            ->label('Decision')
                            ->options([
                                'assign' => 'Assign Referees',
                                'reject' => 'Quick Reject',
                            ])
                            ->reactive()
                            ->required(),
                        Forms\Components\Textarea::make('revision_comment')
                            ->label('Revision Comment')
                            ->visible(fn($get) => $get('decision') === 'reject')
                            ->required(fn($get) => $get('decision') === 'reject'),
                        Forms\Components\Select::make('referees')
                            ->label('Select Referees')
                            ->options(User::role('referee')->pluck('name', 'id'))
                            ->multiple()
                            ->maxItems(3)
                            ->visible(fn($get) => $get('decision') === 'assign')
                            ->required(fn($get) => $get('decision') === 'assign'),
                    ])
                    ->requiresConfirmation()
                    ->button(),


                // Final Decision by Associate Editor
                Action::make('finalDecision')
                    ->label('Make Final Decision')
                    ->visible(fn($record) => auth()->user()->hasRole('associate_editor') && $record->status === 'ready_for_decision')
                    ->action(function ($record, $data) {
                        $decision = $data['decision'];
                        $revisionComment = $data['revision_comment'] ?? null;
                        // Update paper status based on the final decision
                        $record->status = match ($decision) {
                            'accepted' => 'accepted',
                            'minor_revision' => 'ready_for_minor_revision',
                            'major_revision' => 'ready_for_major_revision',
                            'rejected' => 'rejected',
                        };
                        $record->revision_comment = $revisionComment;

                        $record->save();

                        // Update each review's status based on the paper's final decision
                        $record->reviews->each(function ($review) use ($decision) {
                            $review->decision = match ($decision) {
                                'accepted' => 'approved',
                                'minor_revision' => 'revision_requested',
                                'major_revision' => 'revision_requested',
                                'rejected' => 'rejected',
                            };
                            $review->save();
                        });

                        // Notify authors about the final decision
                        $record->load('author');
                        if ($record->author) {
                            $tokenHelper = app(TokenHelperInterface::class);
                            $paper = (object) [
                                'title' => $record->title,
                                'name' => $record->author->name,
                                'email' => $record->author->email,
                            ];
                            $paperreject = (object) [
                                'title' => $record->title,
                                'name' => $record->author->name,
                                'reason' => $revisionComment,
                                'email' => $record->author->email,
                            ];

                            $paperminor = (object) [
                                'title' => $record->title,
                                'name' => $record->author->name,
                                'revison' => $revisionComment,
                                'email' => $record->author->email,
                                'date' => now()->addDays(10)->format('d.m.Y'),
                            ];

                            $papermajor = (object) [
                                'title' => $record->title,
                                'name' => $record->author->name,
                                'revison' => $revisionComment,
                                'email' => $record->author->email,
                                'link' => url('admin/papers/' . $record->id . '/edit'),
                                'date' => now()->addDays(20)->format('d.m.Y'),
                            ];

                            match ($decision) {
                                'accepted' => Mail::to($record->author->email)->send(new EmailTemplatesPaperAcceptedNotification($paper, $tokenHelper)),
                                'rejected' => Mail::to($record->author->email)->send(new PaperNotAccepted($paperreject, $tokenHelper)),
                                'minor_revision' => Mail::to($record->author->email)->send(new PaperMinorReview($paperminor, $tokenHelper)),
                                'major_revision' => Mail::to($record->author->email)->send(new PaperMajorReview($papermajor, $tokenHelper)),
                            };
                        }

                        // Notify referees if revision is requested
                        // if (in_array($decision, ['minor_revision', 'major_revision'])) {
                        //     foreach ($record->reviews as $review) {
                        //         $review->referee->notify(new ReviewRequestNotification($record));
                        //     }
                        // }
                    })
                    ->form([
                        Forms\Components\Select::make('decision')
                            ->label('Decision')
                            ->options([
                                'accepted' => 'Accept',
                                'minor_revision' => 'Minor Revision',
                                'major_revision' => 'Major Revision',
                                'rejected' => 'Reject',
                            ])
                            ->reactive() // Ensure updates to this field trigger form reactivity
                            ->required(),

                        Forms\Components\Textarea::make('revision_comment')
                            ->label('Revision Comment')
                            ->placeholder('Enter comments about required revisions...')
                            ->rows(4)
                            ->visible(fn($get) => $get('decision') === 'major_revision' || $get('decision') === 'minor_revision' || $get('decision') === 'rejected') // Conditional visibility
                            ->required(fn($get) => $get('decision') === 'major_revision' || $get('decision') === 'minor_revision' || $get('decision') === 'rejected'),

                        // Make it required for major revisions

                    ])
                    ->requiresConfirmation()
                    ->button(),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPapers::route('/'),
            'create' => Pages\CreatePaper::route('/create'),
            'edit' => Pages\EditPaper::route('/{record}/edit'),
            'view' => Pages\PaperDetails::route('/{record}'),
        ];
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole('editor');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('author');
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['author', 'editor', 'associate_editor']);
    }
}
