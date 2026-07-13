<?php

namespace Tests\Feature;

use App\Models\DownloadableProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DownloadTest extends TestCase
{
    use RefreshDatabase;

    private function downloadableProduct(int $limit = 5): Product
    {
        Storage::fake('local');
        Storage::disk('local')->put('downloads/file.zip', 'PAYLOAD');

        $product = Product::factory()->create([
            'is_downloadable' => true, 'pricing_type' => 'fixed', 'inventory_count' => 5,
        ]);
        DownloadableProduct::create([
            'product_id' => $product->id,
            'file_url' => 'downloads/file.zip',
            'download_limit' => $limit,
            'downloads_count' => 0,
        ]);

        return $product;
    }

    private function purchase(User $user, Product $product, array $itemOverrides = []): OrderItem
    {
        $order = Order::create([
            'user_id' => $user->id,
            'customer_email' => $user->email,
            'total_amount' => 10,
            'status' => Order::STATUS_PAID,
        ]);

        return $order->items()->create(array_merge([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 10,
            'download_link' => Str::random(64),
            'download_expires_at' => now()->addDays(30),
            'download_count' => 0,
        ], $itemOverrides));
    }

    public function test_buyer_can_download_within_limit_and_window(): void
    {
        $user = User::factory()->create();
        $product = $this->downloadableProduct(limit: 3);
        $item = $this->purchase($user, $product);

        $this->actingAs($user)->get(route('download.serve-file', $product->id))->assertStatus(200);

        // The buyer's own line item is what gets counted.
        $this->assertSame(1, $item->fresh()->download_count);
    }

    public function test_non_purchaser_is_denied(): void
    {
        $user = User::factory()->create();
        $product = $this->downloadableProduct(); // no purchase for this user

        $this->actingAs($user)->get(route('download.serve-file', $product->id))->assertStatus(403);
    }

    public function test_download_blocked_after_per_purchase_limit_reached(): void
    {
        $user = User::factory()->create();
        $product = $this->downloadableProduct(limit: 2);
        $this->purchase($user, $product, ['download_count' => 2]); // allotment used up

        $this->actingAs($user)->get(route('download.serve-file', $product->id))->assertStatus(403);
    }

    public function test_expired_download_is_blocked(): void
    {
        $user = User::factory()->create();
        $product = $this->downloadableProduct();
        $this->purchase($user, $product, ['download_expires_at' => now()->subDay()]);

        $this->actingAs($user)->get(route('download.serve-file', $product->id))->assertStatus(403);
    }

    public function test_download_limit_is_per_buyer_not_global(): void
    {
        // A product-global counter would let one buyer exhaust the limit for
        // everyone. Each buyer must have their own allotment.
        $product = $this->downloadableProduct(limit: 1);

        $buyerA = User::factory()->create();
        $this->purchase($buyerA, $product);
        $buyerB = User::factory()->create();
        $this->purchase($buyerB, $product);

        $this->actingAs($buyerA)->get(route('download.serve-file', $product->id))->assertStatus(200);
        $this->actingAs($buyerB)->get(route('download.serve-file', $product->id))->assertStatus(200);
    }

    public function test_download_url_generates_from_a_single_product_arg(): void
    {
        $product = Product::factory()->create();

        $url = route('download.serve-file', $product->id);

        $this->assertStringContainsString('/download/file/'.$product->id, $url);
    }
}
