<?php

namespace App\Filament\Resources\PaperResource\Pages;

use App\Filament\Resources\PaperResource;
use App\Mail\Visualbuilder\EmailTemplates\NewPaperSubmission;
use App\Mail\Visualbuilder\EmailTemplates\PaperSubmissionConfirmation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;

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

        $roles = ['super_admin', 'editor']; // Define roles to target

        // Get users by roles
        $users = User::role($roles)->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(new NewPaperSubmission($paper, $tokenHelper));
        }

        Mail::to($this->record->author->email)->send(new PaperSubmissionConfirmation($paper, $tokenHelper));
    }
}
