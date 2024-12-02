<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Mail\Visualbuilder\EmailTemplates\ReviewCompleted;
use App\Models\Review;
use App\Models\User;
use App\Notifications\ReviewCompletedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('comments')
                    ->label('Review Comments')
                    ->required(),
                Forms\Components\Select::make('decision')
                    ->label('Decision')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'minor_revision' => 'Minor Revision',
                        'major_revision' => 'Major Revision',
                        'rejected' => 'Rejected',
                        'revision_requested' => 'Revision Requested',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paper.title')
                    ->label('Paper Title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('referee.name')
                    ->label('Referee')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('decision')
                    ->label('Decision')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reviewed At')
                    ->dateTime()
                    ->sortable(),
            ])->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),

                // Action for Referee to submit a review
                Action::make('submitReview')
                    ->label('Submit Review')
                    ->visible(fn($record) => auth()->user()->hasRole('referee') && $record->referee_id === auth()->id() && $record->decision === 'pending')
                    ->action(function ($record, $data) {
                        $associateEditor = $record->paper->associateEditor;
                        $record->update([
                            'comments' => $data['comments'],
                            'decision' => $data['decision'],
                        ]);

                        $tokenHelper = app(TokenHelperInterface::class);
                        $review = (object) [
                            'title' => $record->paper->title,
                            'name' => $associateEditor->name,
                            'email' => $associateEditor->email,
                            'link' => url('/admin/reviews/' . $record->id),
                        ];
                        // Notify Associate Editor that the review is complete

                        if ($associateEditor) {
                            Mail::to($associateEditor->email)->send(new ReviewCompleted($review, $tokenHelper));
                        }

                        // Check if all reviews are completed
                        $allReviewsCompleted = $record->paper->reviews()->whereNull('comments')->doesntExist();
                        if ($allReviewsCompleted) {
                            // Update paper status to ready for decision
                            $record->paper->status = 'ready_for_decision';
                            $record->paper->save();
                        }
                    })
                    ->form([
                        Forms\Components\Textarea::make('comments')
                            ->label('Review Comments')
                            ->required(),
                        Forms\Components\Select::make('decision')
                            ->label('Decision')
                            ->options([
                                'approved' => 'Approved',
                                'minor_revision' => 'Minor Revision',
                                'major_revision' => 'Major Revision',
                                'rejected' => 'Rejected',
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
            'view' => Pages\ViewReview::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['referee']);
    }
}
