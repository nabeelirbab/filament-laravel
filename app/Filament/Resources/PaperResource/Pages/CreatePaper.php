<?php

namespace App\Filament\Resources\PaperResource\Pages;

use App\Filament\Resources\PaperResource;
use App\Jobs\NotifyEditorsOfNewPaper;
use App\Mail\Visualbuilder\EmailTemplates\PaperSubmissionConfirmation;
use App\Models\User;
use App\Notifications\PaperSubmittedNotification;
use App\Notifications\SuperAdminEditorInChiefNotification;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Illuminate\Support\Facades\Notification;

class CreatePaper extends CreateRecord
{
    protected static string $resource = PaperResource::class;

    protected function beforeFill(): void
    {
        // Automatically set the author_id to the current logged-in user
        $this->data['author_id'] = auth()->id();
    }
    protected function afterCreate(): void
    {
        // Send notification to the Editor-in-Chief after paper submission
        $tokenHelper = app(TokenHelperInterface::class);
        // Prepare author data for the email notification
        $submissionDate = Carbon::parse($this->record->created_at)->format('d.m.Y');
        // Send registration notification
        $paper = (object) [
            'title' => $this->record->title,
            'name' => $this->record->author->name,
            'email' => $this->record->author->email,
            'date_of_submission' => $submissionDate,
        ];

        $users = User::whereIn('role', ['super_admin', 'editor'])->get(); // Corrected array syntax
        foreach ($users as $user) {
            $superadminEditorName = $user->name; // Dynamic name for each user
            Notification::send($user, new SuperAdminEditorInChiefNotification($this->record, $superadminEditorName));
        }

        Mail::to($this->record->author->email)->send(new PaperSubmissionConfirmation($paper, $tokenHelper));
    }
}
