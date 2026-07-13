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
        // Mail only: every caller sends this admin alert to an AnonymousNotifiable
        // via Notification::route('mail', ...), which has no 'database' route — the
        // database channel would call ->create() on null and throw on every supplier
        // failure. There is no User here to persist a DB/bell notification for.
        return ['mail'];
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
