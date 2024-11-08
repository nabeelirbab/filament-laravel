<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Mail\Visualbuilder\EmailTemplates\UserWelcome;
use App\Notifications\AuthorRegistrationNotification;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function mount(): void
    {
        parent::mount();

        if (session()->has('error')) {
            $this->notify('danger', session('error'));
        }
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        try {
            if ($record->hasRole('author')) {
                $tokenHelper = app(TokenHelperInterface::class);
                // Prepare author data for the email notification

                // Send registration notification
                Mail::to($record->email)->send(new UserWelcome($record, $tokenHelper));
            }
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while sending the email notification.');
        }
    }
}
