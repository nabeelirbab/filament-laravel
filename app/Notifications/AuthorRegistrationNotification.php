<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuthorRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $authorData;

    /**
     * Create a new notification instance.
     */
    public function __construct($authorData)
    {
        $this->authorData = $authorData;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to the Paper Submission Platform!')
            ->greeting('Dear ' . $this->authorData['name'] . ' ' . $this->authorData['surname'] . '!')
            ->line('Thank you for registering an account on our Paper Submission Platform. We are thrilled to have you as part of our community.')
            ->line('Here’s a quick summary of your registration details:')
            ->line('**Name**: ' . $this->authorData['name'])
            ->line('**Surname**: ' . $this->authorData['surname'])
            ->line('**Email**: ' . $this->authorData['email'])
            ->line('**Affiliation**: ' . $this->authorData['affiliation'])
            ->line('You can now log in and submit your research papers for review. Our editorial team will ensure that your submissions are processed efficiently and reviewed by expert referees.')
            ->line('If you have any questions or need assistance, please feel free to reach out to our support team.')
            ->salutation('Best regards,')
            ->line('The Paper Submission Team');
    }
}
