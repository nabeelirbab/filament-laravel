<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaperMajorReviewNotification extends Notification
{
    use Queueable;

    protected $paper;
    protected $revisionDetails;
    protected $resubmissionDueDate;

    public function __construct($paper, $revisionDetails, $resubmissionDueDate)
    {
        $this->paper = $paper;
        $this->revisionDetails = $revisionDetails;
        $this->resubmissionDueDate = $resubmissionDueDate;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Major Revisions Required for Your Paper')
            ->greeting('Dear ' . $this->paper->author->name . ',')
            ->line('Thank you for submitting your paper titled "' . $this->paper->title . '".')
            ->line('Our editorial team has determined that major revisions are necessary.')
            ->line('Revisions: ' . $this->revisionDetails)
            ->line('Please submit your revised paper by ' . $this->resubmissionDueDate . ' through your author portal.')
            ->salutation('Best regards, The Scholarly Paper Submission Team');
    }
}
