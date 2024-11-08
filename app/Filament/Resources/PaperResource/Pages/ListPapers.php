<?php

namespace App\Filament\Resources\PaperResource\Pages;

use App\Filament\Resources\PaperResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPapers extends ListRecords
{
    protected static string $resource = PaperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        // Check if the user has the 'editor' role
        if ($user->hasRole('editor')) {
            // Fetch and check query results directly
            $papers = parent::getTableQuery()->get();
            return parent::getTableQuery();
        }

        // Check if the user has the 'author' role
        if ($user->hasRole('author')) {
            // Debug to confirm that the author role condition is hit
            return parent::getTableQuery()->where('author_id', $user->id);
        }

        // Check if the user has the 'associate_editor' role
        if ($user->hasRole('associate_editor')) {
            // Debug to confirm that the associate editor role condition is hit
            return parent::getTableQuery()->where('associate_editor_id', $user->id);
        }

        // Restrict access for any other roles or if no role matches
        return parent::getTableQuery()->whereRaw('0 = 1');
    }
}
