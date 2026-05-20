<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductBackInStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Product $product,
        public ?ProductVariant $variant = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $productName = $this->product->name;
        if ($this->variant) {
            $productName .= ' - ' . $this->variant->name;
        }

        return (new MailMessage)
            ->subject('Product Back in Stock: ' . $productName)
            ->greeting('Good news!')
            ->line("The product you were waiting for is now back in stock:")
            ->line("**{$productName}**")
            ->action('View Product', url('/products/' . $this->product->id))
            ->line('Hurry! Limited stock available.');
    }
}
