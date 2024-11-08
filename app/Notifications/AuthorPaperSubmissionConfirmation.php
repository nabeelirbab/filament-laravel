<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class AuthorPaperSubmissionConfirmation extends Notification
{
    use Queueable;

    protected $paper;

    public function __construct($paper)
    {
        $this->paper = $paper;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Paper Submission Confirmation – Paper Submission Platform')
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('We have successfully received your paper titled "' . $this->paper->title . '". Thank you for submitting your work to our Paper Submission Platform.')
            ->line('Your paper is currently under preliminary review, and we will update you once it’s assigned to referees for detailed evaluation.')
            ->line('Here’s a summary of your submission:')
            ->line('**Paper Title**: ' . $this->paper->title)
            ->line('**Submission Date**: ' . Carbon::now()->format('M d, Y'))
            ->line('We appreciate your contribution to the field and look forward to working with you to publish your research.')
            ->line('For any queries, feel free to contact our support team at any time.')
            ->salutation('Warm regards,')
            ->line('The Paper Submission Team');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Your paper "' . $this->paper->title . '" has been successfully submitted.',
            'paper_id' => $this->paper->id,
        ];
    }
}
