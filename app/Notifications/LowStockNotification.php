<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Product;

class LowStockNotification extends Notification
{
    use Queueable;

    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Low Stock Alert')
                    ->line('The product "' . $this->product->name . '" is running low on stock.')
                    ->line('Current stock: ' . $this->product->inventory_count)
                    ->line('Low stock threshold: ' . $this->product->low_stock_threshold)
                    ->action('View Product', url('/admin/products/' . $this->product->id));
    }

    public function toArray($notifiable)
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'current_stock' => $this->product->inventory_count,
            'low_stock_threshold' => $this->product->low_stock_threshold,
        ];
    }
}