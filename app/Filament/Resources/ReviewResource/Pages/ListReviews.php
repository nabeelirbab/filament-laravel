<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        // Restrict query based on the referee's role
        $query = parent::getTableQuery();

        if (auth()->user()->hasRole('referee')) {
            // If the user is a referee, show only reviews assigned to them
            $query->where('referee_id', auth()->id());
        }

        return $query;
    }
}
