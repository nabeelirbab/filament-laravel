<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Filament\Resources\ReviewResource\Widgets\PaperDetailWidget;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Review;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            PaperDetailWidget::class,
        ];
    }

    protected function getHeaderWidgetsData(): array
    {
        return [
            'record' => $this->record, // Passing the record to the widget
        ];
    }
}
