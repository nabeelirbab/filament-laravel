<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class PaperAssignedNotification extends Notification
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
            ->subject('Paper Assigned to You')
            ->line('A new paper has been assigned to you for review.')
            ->action('View Paper', url('/admin/papers/' . $this->paper->id));
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'A new paper has been assigned to you.',
            'paper_id' => $this->paper->id,
        ];
    }
}
