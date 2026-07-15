<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\DummyData\DummyDataSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The demo catalogue is the first thing an evaluator sees after
 * `php artisan migrate --seed`. It has to be shoppable, and it has to show off
 * the three stock states the storefront renders (in stock / low stock / sold
 * out — see resources/views/components/product-card.blade.php).
 *
 * Assertions are proportional, not exact, so re-balancing the demo spread
 * doesn't break the suite.
 */
class DummyDataSeederTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalogue(): Collection
    {
        $this->seed(DummyDataSeeder::class);

        $products = Product::all();
        $this->assertGreaterThan(10, $products->count(), 'Demo catalogue seeded no meaningful products.');

        return $products;
    }

    public function test_demo_catalogue_is_mostly_in_stock(): void
    {
        $products = $this->seedCatalogue();

        $inStock = $products->where('inventory_count', '>', 0)->count();

        $this->assertGreaterThanOrEqual(
            0.7 * $products->count(),
            $inStock,
            'Most demo products must be buyable; a storefront where nothing can be added to a cart is broken.'
        );
    }

    public function test_demo_catalogue_has_featured_products(): void
    {
        $products = $this->seedCatalogue();

        $featured = $products->where('is_featured', true);

        $this->assertGreaterThanOrEqual(1, $featured->count(), 'Home page "Featured" section renders nothing without featured products.');
        $this->assertLessThanOrEqual(0.5 * $products->count(), $featured->count(), 'Featuring everything features nothing.');
        $this->assertTrue(
            $featured->every(fn (Product $p) => $p->inventory_count > 0),
            'Featured products are the shop window — they must not be sold out.'
        );
    }

    public function test_demo_catalogue_exercises_the_sold_out_state(): void
    {
        $products = $this->seedCatalogue();

        $outOfStock = $products->where('inventory_count', '<=', 0);

        $this->assertGreaterThanOrEqual(1, $outOfStock->count(), 'Demo should show the sold-out UI state at least once.');
        $this->assertLessThanOrEqual(0.2 * $products->count(), $outOfStock->count(), 'Sold-out should be the exception, not the rule.');
    }

    public function test_demo_catalogue_exercises_the_low_stock_state(): void
    {
        $products = $this->seedCatalogue();

        $this->assertTrue(
            $products->every(fn (Product $p) => $p->low_stock_threshold > 0),
            'A zero low_stock_threshold makes the "Low stock" badge unreachable.'
        );

        // Matches the card's own rule: low = in stock, but at or under threshold.
        $lowStock = $products->filter(
            fn (Product $p) => $p->inventory_count > 0 && $p->inventory_count <= $p->low_stock_threshold
        );

        $this->assertGreaterThanOrEqual(1, $lowStock->count(), 'Demo should show the low-stock UI state at least once.');
        $this->assertLessThanOrEqual(0.2 * $products->count(), $lowStock->count(), 'Low stock should be a highlight, not the norm.');
    }

    public function test_demo_catalogue_has_varied_stock_levels(): void
    {
        $products = $this->seedCatalogue();

        $distinct = $products->pluck('inventory_count')->unique();

        $this->assertGreaterThanOrEqual(5, $distinct->count(), 'Uniform stock counts look like fixture data, not a real shop.');
    }
}
