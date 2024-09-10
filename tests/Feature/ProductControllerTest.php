<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\ProductController;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function testCreate()
    {
        $response = $this->get('/products/create');
        $response->assertOk();
        $response->assertViewIs('products.create');
    }

    public function testList()
    {
        $response = $this->get('/products');
        $response->assertOk();
        $response->assertViewIs('products.list');
    }

    public function testShow()
    {
        $product = \App\Models\Product::factory()->create();
        $response = $this->get("/products/{$product->id}");
        $response->assertOk();
        $response->assertViewIs('products.show');
        $response->assertViewHas('product', $product);
    }

    public function testUpdate()
    {
        $product = \App\Models\Product::factory()->create();
        $updatedData = ['name' => 'Updated Product Name', 'description' => 'Updated Description'];
        $response = $this->put("/products/{$product->id}", $updatedData);
        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', $updatedData);
    }

    public function testDelete()
    {
        $product = \App\Models\Product::factory()->create();
        $response = $this->delete("/products/{$product->id}");
        $response->assertRedirect('/products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

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
