<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPerformance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        $category = ProductCategory::create([
            'name' => 'Perf Cat',
            'slug' => 'perf-cat-' . uniqid(),
        ]);

        return Product::create([
            'name' => 'Perf Product',
            'slug' => 'perf-prod-' . uniqid(),
            'price' => 10.00,
            'category_id' => $category->id,
            'inventory_count' => 10,
        ]);
    }

    /**
     * Regression: the model must map to the `product_performance` table.
     * Without an explicit $table, Eloquent pluralises the class to
     * `product_performances`, which does not exist -> every read/write fatals.
     */
    public function test_model_uses_the_migrated_table_name(): void
    {
        $this->assertSame('product_performance', (new ProductPerformance())->getTable());
    }

    public function test_record_view_creates_and_increments_a_single_daily_row(): void
    {
        $product = $this->makeProduct();

        ProductPerformance::recordView($product->id, '2026-07-14');
        ProductPerformance::recordView($product->id, '2026-07-14');

        $rows = ProductPerformance::where('product_id', $product->id)->get();

        $this->assertCount(1, $rows, 'Same product+date must reuse one row');
        $this->assertEquals(2, $rows->first()->views);
    }

    public function test_record_purchase_accumulates_and_computes_conversion(): void
    {
        $product = $this->makeProduct();

        // 10 views, then 2 purchases -> conversion 20%
        for ($i = 0; $i < 10; $i++) {
            ProductPerformance::recordView($product->id, '2026-07-14');
        }
        ProductPerformance::recordPurchase($product->id, 25.00, '2026-07-14');
        ProductPerformance::recordPurchase($product->id, 25.00, '2026-07-14');

        $row = ProductPerformance::where('product_id', $product->id)->first();

        $this->assertEquals(10, $row->views);
        $this->assertEquals(2, $row->purchases);
        $this->assertEquals(50.00, $row->revenue);
        $this->assertEquals(20.00, $row->conversion_rate);
    }

    public function test_calculate_conversion_rate_is_zero_without_views(): void
    {
        $product = $this->makeProduct();

        $row = ProductPerformance::create([
            'product_id' => $product->id,
            'date' => '2026-07-14',
            'views' => 0,
            'purchases' => 3,
        ]);
        $row->calculateConversionRate();

        $this->assertEquals(0, $row->conversion_rate);
    }

    public function test_calculate_return_rate(): void
    {
        $product = $this->makeProduct();

        $row = ProductPerformance::create([
            'product_id' => $product->id,
            'date' => '2026-07-14',
            'purchases' => 4,
            'returns' => 1,
        ]);
        $row->calculateReturnRate();

        $this->assertEquals(25.00, $row->return_rate);
    }
}
