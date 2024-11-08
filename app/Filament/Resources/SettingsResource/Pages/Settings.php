<?php

namespace App\Filament\Resources\SettingsResource\Pages;

use App\Filament\Resources\SettingsResource;
use Filament\Resources\Pages\Page;

class Settings extends Page
{
    protected static string $resource = SettingsResource::class;

    protected static string $view = 'filament.resources.settings-resource.pages.settings';
}
