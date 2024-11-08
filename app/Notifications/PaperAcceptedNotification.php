<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaperAcceptedNotification extends Notification
{
    use Queueable;

    protected $paper;

    /**
     * Create a new notification instance.
     *
     * @param $paper
     */
    public function __construct($paper)
    {
        $this->paper = $paper;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Congratulations! Your Paper Has Been Accepted')
            ->greeting('Dear ' . $this->paper->author->name . ',')
            ->line('We are pleased to inform you that your paper titled "' . $this->paper->title . '" has been accepted for publication in the Scholarly Paper Submission Platform. Congratulations on this achievement!')
            ->line('Our editorial team found your work to be an excellent contribution to the field, and we are excited to include it in our upcoming publication.')
            ->line('**Next Steps:**')
            ->line('We will be in touch shortly with further details regarding the publication timeline and any final formatting requirements.')
            ->line('Thank you for your valuable contribution. We look forward to sharing your work with our audience.')
            ->salutation('Best regards, The Scholarly Paper Submission Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'paper_title' => $this->paper->title,
            'author_name' => $this->paper->author->name,
        ];
    }
}
