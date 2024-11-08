<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaperResource\Pages;
use App\Models\Field;
use App\Models\Paper;
use App\Models\Review;
use App\Models\User;
use App\Notifications\PaperAcceptedNotification;
use App\Notifications\PaperAssignedNotification;
use App\Notifications\PaperDecisionNotification;
use App\Notifications\PaperMajorReviewNotification;
use App\Notifications\PaperMinorReviewNotification;
use App\Notifications\PaperNotAcceptedNotification;
use App\Notifications\RefereeAccessNotification;
use App\Notifications\ReviewRequestNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

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
                        $associateEditor->notify(new PaperAssignedNotification($record));
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
                    ->visible(fn($record) => auth()->user()->hasRole('associate_editor') && $record->status === 'under_review')
                    ->action(function ($record, $data) {
                        if ($data['decision'] === 'reject') {
                            $record->status = 'rejected';
                            $record->save();
                            $record->author->notify(new PaperNotAcceptedNotification($record, 'Relevance or scope issues'));
                        } else {
                            $record->status = 'under_review';
                            $record->save();
                            foreach ($data['referees'] as $refereeId) {
                                $referee = User::find($refereeId);
                                $review = Review::create([
                                    'paper_id' => $record->id,
                                    'referee_id' => $referee->id,
                                    'comments' => 'Pending review comments',
                                ]);
                                $referee->notify(new RefereeAccessNotification($record, $referee, $review));
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
                            ->required(),
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

                        // Update paper status based on the final decision
                        $record->status = match ($decision) {
                            'accepted' => 'accepted',
                            'minor_revision' => 'ready_for_minor_revision',
                            'major_revision' => 'ready_for_major_revision',
                            'rejected' => 'rejected',
                        };
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
                            match ($decision) {
                                'accepted' => $record->author->notify(new PaperAcceptedNotification($record)),
                                'rejected' => $record->author->notify(new PaperNotAcceptedNotification($record, 'Relevance or scope issues')),
                                'minor_revision' => $record->author->notify(new PaperMinorReviewNotification(
                                    $record,
                                    'Clarifying certain points and updating references',
                                    now()->addDays(10)->format('d.m.Y')
                                )),
                                'major_revision' => $record->author->notify(new PaperMajorReviewNotification(
                                    $record,
                                    'Restructuring sections and adding more data',
                                    now()->addDays(20)->format('d.m.Y')
                                )),
                            };
                        }

                        // Notify referees if revision is requested
                        if (in_array($decision, ['minor_revision', 'major_revision'])) {
                            foreach ($record->reviews as $review) {
                                $review->referee->notify(new ReviewRequestNotification($record));
                            }
                        }
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
                            ->required(),
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
