<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CheckLowStockItemsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate('super_admin', 'web');

        return User::factory()->create()->assignRole('super_admin');
    }

    public function test_notifies_admins_when_a_product_is_low_on_stock(): void
    {
        Notification::fake();
        $admin = $this->admin();
        Product::factory()->create(['inventory_count' => 1, 'low_stock_threshold' => 5]);

        $this->artisan('inventory:check-low-stock')->assertExitCode(0);

        Notification::assertSentTo($admin, LowStockNotification::class);
    }

    public function test_no_notifications_when_stock_is_healthy(): void
    {
        Notification::fake();
        $this->admin();
        Product::factory()->create(['inventory_count' => 50, 'low_stock_threshold' => 5]);

        $this->artisan('inventory:check-low-stock')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_non_admins_are_not_notified(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $customer = User::factory()->create(); // no role
        Product::factory()->create(['inventory_count' => 0, 'low_stock_threshold' => 5]);

        $this->artisan('inventory:check-low-stock')->assertExitCode(0);

        Notification::assertSentTo($admin, LowStockNotification::class);
        Notification::assertNotSentTo($customer, LowStockNotification::class);
    }
}
