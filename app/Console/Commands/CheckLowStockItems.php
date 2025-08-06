<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;

class CheckLowStockItems extends Command
{
    protected $signature = 'inventory:check-low-stock';

    protected $description = 'Check for low stock items and send notifications';

    public function handle()
    {
        $lowStockProducts = Product::where('inventory_count', '<=', DB::raw('low_stock_threshold'))->get();

        if ($lowStockProducts->isNotEmpty()) {
            $admins = User::where('is_admin', true)->get();

            foreach ($lowStockProducts as $product) {
                Notification::send($admins, new LowStockNotification($product));
                $this->info("Low stock notification sent for product: {$product->name}");
            }
        } else {
            $this->info('No low stock items found.');
        }
    }
}