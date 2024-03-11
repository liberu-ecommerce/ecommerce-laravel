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
}
