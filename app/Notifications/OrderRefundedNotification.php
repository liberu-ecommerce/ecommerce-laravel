<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderRefundedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public float $refundAmount
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Refund Processed - Order #' . $this->order->id)
            ->greeting('Hello!')
            ->line("Your refund has been processed for order #{$this->order->id}.")
            ->line("Refund amount: $" . number_format($this->refundAmount, 2))
            ->line('The refund should appear in your account within 5-10 business days.')
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('Thank you for your business!');
    }

    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'refund_amount' => $this->refundAmount,
            'message' => "Refund of $" . number_format($this->refundAmount, 2) . " processed for order #{$this->order->id}",
        ];
    }
}
