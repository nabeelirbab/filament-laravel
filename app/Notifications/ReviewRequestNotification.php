<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewRequestNotification extends Notification
{
    use Queueable;
    protected $paper;

    public function __construct($paper)
    {
        $this->paper = $paper;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Request for Paper Review')
            ->line('You have been requested to review a new paper.')
            ->action('Accept Review', url('admin/reviews/' . $this->paper->id))
            ->line('If you cannot review, please reject.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'You have been requested to review a paper.',
            'paper_id' => $this->paper->id,
        ];
    }
}
