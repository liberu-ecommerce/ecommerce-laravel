<?php

namespace Tests\Feature\Frontend;

use App\Models\Product;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    // Test listing products when there are no products
    public function test_list_products_empty()
    {
        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products', function ($products) {
            return $products->isEmpty();
        });
    }

    // Test listing products with one product
    public function test_list_products_with_one_product()
    {
        $productName = "Test Product";
        $product = Product::factory()->create([
            "name" => $productName
        ]);

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products', function ($products) use ($product, $productName) {
            return (
                $products->count() === 1
                && $products->first()->id === $product->id
                && $products->first()->name === $productName
            );
        });
    }


    // Test listing products with multiple products
    public function test_list_products_with_multiple_products()
    {
        $products = Product::factory()->count(3)->sequence(
            ["name" => "Product 1"],
            ["name" => "Product 2"],
            ["name" => "Product 3"]
        )->create();

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products', function ($viewProducts) use ($products) {
            if ($viewProducts->count() !== 3) {
                return false;
            }

            $viewProductNames = $viewProducts->pluck('name');
            $productNames = $products->pluck('name');

            return $productNames->every(fn ($name) => $viewProductNames->contains($name));
        });
    }


    // Test showing a single product returns 404 when there are no products
    public function test_show_product_returns_404_when_no_products_exist()
    {
        $response = $this->get(route('products.show', ['product' => 999]));
        $response->assertStatus(404);
    }

    // Test showing a single product returns 404 when other products exist 
    public function test_show_product_returns_404_when_other_products_exist()
    {
        Product::factory()->count(10)->create();

        $response = $this->get(route('products.show', ['product' => 999]));
        $response->assertStatus(404);
    }


    // Test showing a single product returns the correct product
    public function test_show_product_returns_correct_product()
    {
        $productName = "Test Product";
        $product = Product::factory()->create([
            'name' => $productName
        ]);

        $response = $this->get(route('products.show', ['product' => $product->id]));

        $response->assertStatus(200);
        $response->assertViewIs('products.show');
        $response->assertViewHas('product', function ($viewProduct) use ($product, $productName) {
            return $viewProduct->id === $product->id
                && $viewProduct->name === $productName;
        });
    }

    public function test_filter_products_by_name_returns_no_results_for_non_existent_name()
    {
        Product::factory()->count(3)->sequence(
            ['name' => 'Product 1'],
            ['name' => 'Product 2'],
            ['name' => 'Product 3']
        )->create();

        // URL: GET /products?filter[name]=NonExistentProduct
        $response = $this->get(route('products.index', ['filter[name]' => 'NonExistentProduct']));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products', function ($products) {
            return $products->isEmpty();
        });
    }

    public function test_filter_products_by_name_returns_matching_product()
    {
        $productName = 'ProductMatch';
        Product::factory()->count(3)->sequence(
            ['name' => 'Product 1'],
            ['name' => 'Product 2'],
            ['name' => $productName]
        )->create();

        // URL: GET /products?filter[name]=ProductMatch
        $response = $this->get(route('products.index', ['filter[name]' => $productName]));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products', function ($products) use ($productName) {
            return $products->count() === 1
                && $products->first()->name === $productName;
        });
    }


    public function test_filter_products_by_name_returns_multiple_matching_products()
    {
        $productName = 'ProductMatch';
        Product::factory()->count(3)->sequence(
            ['name' => $productName . ' 1'],
            ['name' => 'OtherProduct 1'],
            ['name' => $productName . ' 2'],
            ['name' => 'OtherProduct 2'],
            ['name' => 'OtherProduct 3'],
        )->create();

        // URL: GET /products?filter[name]=ProductMatch
        $response = $this->get(route('products.index', ['filter[name]' => $productName]));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products', function ($products) use ($productName) {
            $expectedNames = [$productName . ' 1', $productName . ' 2'];
            return $products->count() === 2
                && $products->pluck('name')->intersect($expectedNames)->count() === 2;
        });
    }

    // Test sorting products by name in ascending order
    public function test_sort_products_by_name_ascending()
    {
        $products = Product::factory()->count(3)->sequence(
            ['name' => 'Product 3'],
            ['name' => 'Product 1'],
            ['name' => 'Product 2']
        )->create();

        $response = $this->get(route('products.index', ['sort' => 'name']));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products', function ($viewProducts) use ($products) {
            return $viewProducts->pluck('name')->toArray() === ['Product 1', 'Product 2', 'Product 3'];
        });
    }

    // Test sorting products by name in descending order
    public function test_sort_products_by_name_descending()
    {
        $products = Product::factory()->count(3)->sequence(
            ['name' => 'Product 3'],
            ['name' => 'Product 1'],
            ['name' => 'Product 2']
        )->create();

        $response = $this->get(route('products.index', ['sort' => '-name']));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products', function ($viewProducts) use ($products) {
            return $viewProducts->pluck('name')->toArray() === ['Product 3', 'Product 2', 'Product 1'];
        });
    }
    // Test sorting products by created date in ascending order
    public function test_sort_products_by_created_date_ascending()
    {
        $timestamps = [
            Carbon::now()->subDays(1),
            Carbon::now()->subDays(3),
            Carbon::now()
        ];

        $products = Product::factory()->count(3)->sequence(
            ['created_at' => $timestamps[0]],
            ['created_at' => $timestamps[1]],
            ['created_at' => $timestamps[2]]
        )->create();

        $expectedTimestamps = [
            ["created_at" => $timestamps[1]->toDateTimeString(), "id" => $products->get(1)->id],
            ["created_at" => $timestamps[0]->toDateTimeString(), "id" => $products->get(0)->id],
            ["created_at" => $timestamps[2]->toDateTimeString(), "id" => $products->get(2)->id]
        ];

        $response = $this->get(route('products.index', ['sort' => 'created_at']));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products', function ($viewProducts) use ($expectedTimestamps) {
            $viewProductsArray = $viewProducts->map(function ($product) {
                return [
                    'created_at' => $product->created_at->toDateTimeString(),
                    'id' => $product->id
                ];
            })->toArray();

            return $viewProductsArray === $expectedTimestamps;
        });
    }


    // Test sorting products by created date in descending order
    public function test_sort_products_by_created_date_descending()
    {
        $timestamps = [
            Carbon::now()->subDays(1),
            Carbon::now()->subDays(3),
            Carbon::now()
        ];

        $products = Product::factory()->count(3)->sequence(
            ['created_at' => $timestamps[0]],
            ['created_at' => $timestamps[1]],
            ['created_at' => $timestamps[2]]
        )->create();

        $expectedTimestamps = [
            ["created_at" => $timestamps[2]->toDateTimeString(), "id" => $products->get(2)->id],
            ["created_at" => $timestamps[0]->toDateTimeString(), "id" => $products->get(0)->id],
            ["created_at" => $timestamps[1]->toDateTimeString(), "id" => $products->get(1)->id]
        ];

        $response = $this->get(route('products.index', ['sort' => '-created_at']));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products', function ($viewProducts) use ($expectedTimestamps) {
            $viewProductsArray = $viewProducts->map(function ($product) {
                return [
                    'created_at' => $product->created_at->toDateTimeString(),
                    'id' => $product->id
                ];
            })->toArray();

            return $viewProductsArray === $expectedTimestamps;
        });
    }

    public function test_filter_products_by_price_min()
    {
        $products = Product::factory()->count(5)->sequence(
            ['price' => 5],
            ['price' => 15],
            ['price' => 25],
            ['price' => 35],
            ['price' => 45]
        )->create();

        $response = $this->get(route('products.index', ['filter' => ['price_min' => 20]]));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products', function ($viewProducts) {
            dump($viewProducts);
            return $viewProducts->pluck('price')->toArray() === [25, 35, 45];
        });
    }

    public function test_filter_products_by_price_max()
    {
        $products = Product::factory()->count(5)->sequence(
            ['price' => 5],
            ['price' => 15],
            ['price' => 25],
            ['price' => 35],
            ['price' => 45]
        )->create();

        $response = $this->get(route('products.index', ['filter' => ['price_max' => 30]]));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products', function ($viewProducts) {
            return $viewProducts->pluck('price')->toArray() === [5, 15, 25];
        });
    }

    public function test_filter_products_by_price_range()
    {
        $products = Product::factory()->count(5)->sequence(
            ['price' => 5],
            ['price' => 15],
            ['price' => 25],
            ['price' => 35],
            ['price' => 45]
        )->create();

        $response = $this->get(route('products.index', ['filter' => ['price_min' => 10, 'price_max' => 30]]));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products', function ($viewProducts) {
            // Ensure products are between $10 and $30
            return $viewProducts->pluck('price')->toArray() === [15, 25];
        });
    }



    // Test combined filter and sort
    public function test_combined_sorting_and_filtering()
    {
        $timestamps = [
            Carbon::now()->subDays(5),
            Carbon::now()->subDays(4),
            Carbon::now()->subDays(3),
            Carbon::now()->subDays(2),
            Carbon::now()->subDays(1)
        ];

        $products = Product::factory()->count(5)->sequence(
            ['name' => 'AlphaX', 'price' => 15, 'created_at' => $timestamps[4]],
            ['name' => 'Bravo', 'price' => 25, 'created_at' => $timestamps[1]],
            ['name' => 'CharlieX', 'price' => 40, 'created_at' => $timestamps[2]],
            ['name' => 'DeltaX', 'price' => 20, 'created_at' => $timestamps[0]],
            ['name' => 'EchoX', 'price' => 5, 'created_at' => $timestamps[4]]
        )->create();

        // Define the expected sorted order by creation date (ascending)
        $expectedProducts = [
            ['name' => 'DeltaX', 'price' => 20, 'created_at' => $timestamps[0]->toDateTimeString()],
            ['name' => 'AlphaX', 'price' => 15, 'created_at' => $timestamps[4]->toDateTimeString()],
        ];

        // Filter by name containing 'a', price range 10 to 30, and sort by created date ascending
        $response = $this->get(route('products.index', [
            'filter' => ['name' => 'x', 'price_min' => 10, 'price_max' => 30],
            'sort' => 'created_at'
        ]));

        $response->assertStatus(200);        

        $response->assertViewHas('products', function ($viewProducts) use ($expectedProducts) {
            $viewProductsArray = $viewProducts->map(function ($product) {
                return [
                    'name' => $product->name,
                    'price' => $product->price,
                    'created_at' => $product->created_at->toDateTimeString()
                ];
            })->toArray();

            return $viewProductsArray === $expectedProducts;
        });
    }
}
