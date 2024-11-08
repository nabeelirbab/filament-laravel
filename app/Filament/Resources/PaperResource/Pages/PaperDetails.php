<?php

namespace App\Filament\Resources\PaperResource\Pages;

use App\Filament\Resources\PaperResource;
use App\Models\Paper;
use Filament\Resources\Pages\Page;

class PaperDetails extends Page
{
    protected static string $resource = PaperResource::class;

    protected static string $view = 'filament.resources.paper-resource.pages.paper-details';
    public function mount(Paper $record): void
    {
        $this->record = $record;
    }
}
