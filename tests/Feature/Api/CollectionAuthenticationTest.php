<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication_to_list_collections()
    {
        $response = $this->getJson('/api/collections');
        $response->assertStatus(401);
    }

    public function test_requires_authentication_to_create_collection()
    {
        $response = $this->postJson('/api/collections', [
            'name' => 'Test Collection'
        ]);
        $response->assertStatus(401);
    }

    public function test_requires_authentication_to_show_collection()
    {
        $response = $this->getJson('/api/collections/1');
        $response->assertStatus(401);
    }

    public function test_requires_authentication_to_update_collection()
    {
        $response = $this->putJson('/api/collections/1', [
            'name' => 'Updated'
        ]);
        $response->assertStatus(401);
    }

    public function test_requires_authentication_to_add_products()
    {
        $response = $this->postJson('/api/collections/1/products', [
            'product_ids' => [1]
        ]);
        $response->assertStatus(401);
    }

    public function test_requires_authentication_to_remove_products()
    {
        $response = $this->deleteJson('/api/collections/1/products', [
            'product_ids' => [1]
        ]);
        $response->assertStatus(401);
    }

    public function test_requires_authentication_to_delete_collection()
    {
        $response = $this->deleteJson('/api/collections/1');
        $response->assertStatus(401);
    }
}
