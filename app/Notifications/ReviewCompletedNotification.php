<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class ReviewCompletedNotification extends Notification
{
    use Queueable;
    protected $review;

    public function __construct($review)
    {
        $this->review = $review;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Review Completed')
            ->line('A review has been completed for a paper you are overseeing.')
            ->action('View Review', url('/admin/reviews/' . $this->review->id));
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'A review has been completed.',
            'review_id' => $this->review->id,
        ];
    }
}
