<?php

namespace App\Filament\Resources\ReviewResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Review;

class PaperDetailWidget extends Widget
{
    protected static string $view = 'filament.resources.review-resource.pages.paper-detail-widget';

    public Review $record;

    public function mount(): void
    {
        $this->record = $this->record;
    }
}
