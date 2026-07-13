<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiSearchSortTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_invalid_sort_order_does_not_500(): void
    {
        Product::factory()->count(2)->create();

        // orderBy() throws InvalidArgumentException on anything but asc/desc;
        // an unvalidated sort_order therefore crashed the endpoint.
        $this->getJson('/api/products?sort_order=lowest')->assertStatus(200);
        $this->getJson('/api/products?sort_by=price&sort_order=ascending')->assertStatus(200);
    }

    public function test_search_matches_terms_containing_an_apostrophe(): void
    {
        Product::factory()->create(['name' => "O'Neill Wetsuit"]);

        // The old sanitizer stripped the apostrophe -> "ONeill" -> no match.
        $response = $this->getJson('/api/products?search='.urlencode("O'Neill"));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertSame("O'Neill Wetsuit", $response->json('data.0.name'));
    }

    public function test_like_wildcard_in_search_is_escaped(): void
    {
        Product::factory()->create(['name' => 'Ordinary Product']);

        // A bare "%" must not match every product (it did — raw wildcard in LIKE).
        $response = $this->getJson('/api/products?search='.urlencode('%'));

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }
}
