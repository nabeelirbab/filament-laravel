<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuperAdminEditorInChiefNotification extends Notification
{
    use Queueable;

    protected $paper;
    protected $superadminEditorName;

    public function __construct($paper, $superadminEditorName)
    {
        $this->paper = $paper;
        $this->superadminEditorName = $superadminEditorName;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $submissionDate = $this->paper->created_at->format('d.m.Y'); // Formatting the date

        return (new MailMessage)
            ->subject('New Paper Submission')
            ->greeting('Dear ' . $this->superadminEditorName . ',')
            ->line('A new paper titled "' . $this->paper->title . '" has been submitted by author ' . $this->paper->author->name . '.')
            ->line('Please assign it to an associate editor.')
            ->line('**Submission Details:**')
            ->line('**Paper Title:** ' . $this->paper->title)
            ->line('**Submission Date:** ' . $submissionDate)
            ->salutation('Warm regards, The Paper Submission Team');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'A new paper titled "' . $this->paper->title . '" has been submitted.',
            'paper_id' => $this->paper->id,
        ];
    }
}
