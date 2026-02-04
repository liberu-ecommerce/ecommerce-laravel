<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupplierFailureNotification extends Notification
{
    use Queueable;

    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Dropshipping supplier error')
                    ->line($this->message)
                    ->line('Please review the order and contact the supplier.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
        ];
    }
}
