<?php

namespace App\Filament\Resources\RegisterResource\Pages\Auth;

use App\Filament\Resources\RegisterResource;
use Filament\Resources\Pages\Page;

class Register extends Page
{
    protected static string $resource = RegisterResource::class;

    protected static string $view = 'filament.resources.register-resource.pages.auth.register';
}
