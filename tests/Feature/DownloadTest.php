<?php

namespace Tests\Feature;

use App\Models\DownloadableProduct;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadTest extends TestCase
{
    use RefreshDatabase;

    private function downloadableProduct(): Product
    {
        Storage::fake('local');
        Storage::disk('local')->put('downloads/file.zip', 'PAYLOAD');

        $product = Product::factory()->create([
            'is_downloadable' => true, 'pricing_type' => 'fixed', 'inventory_count' => 5,
        ]);
        DownloadableProduct::create([
            'product_id' => $product->id,
            'file_url' => 'downloads/file.zip',
            'download_limit' => 5,
            'downloads_count' => 0,
        ]);

        return $product;
    }

    private function purchase(User $user, Product $product): void
    {
        $order = Order::create([
            'user_id' => $user->id,
            'customer_email' => $user->email,
            'total_amount' => 10,
            'status' => Order::STATUS_PAID,
        ]);
        $order->items()->create(['product_id' => $product->id, 'quantity' => 1, 'price' => 10]);
    }

    public function test_buyer_can_download_their_purchased_product(): void
    {
        // The route param mismatch ({category}/{product} vs $productId) made the
        // controller look up by the category id → 404 for legitimate buyers.
        $user = User::factory()->create();
        $product = $this->downloadableProduct();
        $this->purchase($user, $product);

        $this->actingAs($user)->get(route('download.serve-file', $product->id))
            ->assertStatus(200);
    }

    public function test_non_purchaser_is_denied(): void
    {
        $user = User::factory()->create();
        $product = $this->downloadableProduct(); // no purchase for this user

        $this->actingAs($user)->get(route('download.serve-file', $product->id))
            ->assertStatus(403);
    }

    public function test_download_url_generates_from_a_single_product_arg(): void
    {
        // Guards the route arity: the two-segment {category}/{product} route threw
        // UrlGenerationException from the blade's single-arg route() call → 500 on
        // every product page carrying a download button.
        $product = Product::factory()->create();

        $url = route('download.serve-file', $product->id);

        $this->assertStringContainsString('/download/file/'.$product->id, $url);
    }
}
