<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefereeAccessNotification extends Notification
{
    use Queueable;

    protected $paper;
    protected $referee;
    protected $review;

    public function __construct($paper, $referee, $review)
    {
        $this->paper = $paper;
        $this->referee = $referee;
        $this->review = $review;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $acceptUrl = url('admin/reviews/' . $this->review->id . '?paper_id=' . $this->paper->id . '&referee_id=' . $this->referee->id);
        $declineUrl = route('review.reject', $this->review);

        return (new MailMessage)
            ->subject('Access to Paper for Review – Paper Submission Platform')
            ->greeting('Dear ' . $this->referee->name . ',')
            ->line('Thank you for considering to review the paper titled "' . $this->paper->title . '."')
            ->line('We greatly appreciate your time and expertise in contributing to the quality and integrity of our publication process.')
            ->line('Please choose one of the options below:')
            ->line('[Accept Review](' . $acceptUrl . ')')
            ->line('[Decline Review](' . $declineUrl . ')')
            ->line('**Paper Details:**')
            ->line('**Title:** ' . $this->paper->title)
            ->line('**Authors:** ' . $this->paper->author->name)
            ->line('**Abstract:** ' . $this->paper->abstract)
            ->line('Please ensure that your review covers the main areas of scientific validity, clarity, originality, and relevance.')
            ->line('If you have any questions or concerns, please don’t hesitate to contact us.')
            ->salutation('Best regards, The Paper Submission Team');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'You have been assigned to review the paper: ' . $this->paper->title,
            'paper_id' => $this->paper->id,
        ];
    }
}
