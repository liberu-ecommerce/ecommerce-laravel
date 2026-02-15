<?php

namespace App\Notifications;

use App\Models\LoyaltyPoints;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoyaltyPointsEarnedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $pointsEarned,
        public int $totalBalance,
        public ?string $reason = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('You Earned ' . $this->pointsEarned . ' Loyalty Points!')
            ->greeting('Congratulations!')
            ->line("You've earned {$this->pointsEarned} loyalty points!");

        if ($this->reason) {
            $message->line("Reason: {$this->reason}");
        }

        return $message
            ->line("Your current balance: {$this->totalBalance} points")
            ->action('View Rewards', url('/loyalty/rewards'))
            ->line('Thank you for being a valued customer!');
    }

    public function toArray($notifiable): array
    {
        return [
            'points_earned' => $this->pointsEarned,
            'total_balance' => $this->totalBalance,
            'reason' => $this->reason,
            'message' => "You earned {$this->pointsEarned} loyalty points! New balance: {$this->totalBalance}",
        ];
    }
}
