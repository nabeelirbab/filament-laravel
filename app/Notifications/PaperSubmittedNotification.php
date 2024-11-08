<?php


namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaperSubmittedNotification extends Notification
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
            ->subject('New Paper Submitted')
            ->line('A new paper has been submitted.')
            ->action('View Paper', url('/admin/papers/' . $this->paper->id))
            ->line('Please review and assign an Associate Editor.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'A new paper has been submitted.',
            'paper_id' => $this->paper->id,
        ];
    }
}
