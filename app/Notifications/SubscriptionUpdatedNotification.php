<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscriptionDetails;

    public function __construct(array $subscriptionDetails)
    {
        $this->subscriptionDetails = $subscriptionDetails;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
                    ->subject('Subscription Update')
                    ->greeting('Hello!');

        if ($this->subscriptionDetails['type'] === 'plan_change') {
            $mailMessage->line('Your subscription plan has been successfully updated.')
                        ->line('Old Plan: ' . $this->subscriptionDetails['old_plan'])
                        ->line('New Plan: ' . $this->subscriptionDetails['new_plan'])
                        ->line('Effective Date: ' . $this->subscriptionDetails['effective_date']);
        } elseif ($this->subscriptionDetails['type'] === 'cancellation') {
            $mailMessage->line('Your subscription has been cancelled.')
                        ->line('Cancellation Date: ' . $this->subscriptionDetails['cancellation_date'])
                        ->line('Your subscription will not renew.');
        }

        $mailMessage->action('View Subscription', url('/subscription'))
                    ->line('Thank you for using our application!');

        return $mailMessage;
    }

    public function toArray($notifiable)
    {
        return [
            'subscription_details' => $this->subscriptionDetails,
        ];
    }
}
