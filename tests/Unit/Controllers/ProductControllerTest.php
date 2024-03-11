<?php

namespace Tests\Unit\Controllers;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testCreate()
    {
        $payload = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 0, 100),
            'category' => $this->faker->word,
            'inventory_count' => $this->faker->numberBetween(0, 100),
        ];

        $response = $this->post('/products', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', $payload);
        $this->assertDatabaseHas('inventory_logs', [
            'quantity_change' => $payload['inventory_count'],
            'reason' => 'Initial stock setup',
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
