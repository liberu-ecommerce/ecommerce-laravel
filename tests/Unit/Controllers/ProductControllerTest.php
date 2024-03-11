<?php

namespace Tests\Unit\Controllers;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

public function testCreateMethodCreatesInventoryLogs()
{
    // Create a mock request with the necessary data
    $request = $this->mock(Request::class);
    $request->shouldReceive('validate')->andReturn([
        'name' => 'Test Product',
        'description' => 'Test Description',
        'price' => 10.99,
        'category' => 'Test Category',
        'inventory_count' => 100,
    ]);

    // Create a mock product
    $product = $this->mock(Product::class);
    $product->shouldReceive('create')->once()->andReturn($product);

    // Call the create method on the ProductController
    $controller = new ProductController();
    $response = $controller->create($request);

    // Assert that the inventory log is created
    $this->assertDatabaseHas('inventory_logs', [
        'product_id' => $product->id,
        'quantity_change' => 100,
        'reason' => 'Inventory adjustment',
    ]);
    }

    public function testUpdate()
    {
        $product = Product::factory()->create();

        $payload = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 0, 100),
            'category' => $this->faker->word,
            'inventory_count' => $this->faker->numberBetween(0, 100),
        ];

        $response = $this->put("/products/{$product->id}", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', $payload);
        $this->assertDatabaseHas('inventory_logs', [
            'quantity_change' => $payload['inventory_count'] - $product->inventory_count,
            'reason' => 'Inventory adjustment',
        ]);
    }
}
