<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaperNotAcceptedNotification extends Notification
{
    use Queueable;

    protected $paper;
    protected $briefReason;

    public function __construct($paper, $briefReason)
    {
        $this->paper = $paper;
        $this->briefReason = $briefReason;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Update on Your Paper Submission')
            ->greeting('Dear ' . $this->paper->author->name . ',')
            ->line('Thank you for submitting your paper titled "' . $this->paper->title . '".')
            ->line('After thorough review, we regret to inform you that your paper has not been accepted.')
            ->line('Reason: ' . $this->briefReason)
            ->line('We encourage you to continue your research and consider submitting future work.')
            ->salutation('Best regards, The Scholarly Paper Submission Team');
    }
}
