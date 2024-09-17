<?php

namespace Tests\Feature\Frontend;

use App\Models\Product;
use App\Models\ProductCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductCollectionControllerTest extends TestCase
{
    use RefreshDatabase;

    // Test that no collections exist
    public function test_list_collections_when_none_exist()
    {
        $response = $this->get(route('collections.index'));

        $response->assertStatus(200);
        $response->assertViewIs('collections.index');
        $response->assertViewHas('collections', function ($collections) {
            return $collections->isEmpty();
        });
    }

    // Test that exactly one collection exists
    public function test_list_collections_with_one_collection()
    {
        $collectionName = "Test Collection";
        $collection = ProductCollection::factory()->create([
            "name" => $collectionName
        ]);

        $response = $this->get(route('collections.index'));

        $response->assertStatus(200);
        $response->assertViewIs('collections.index');
        $response->assertViewHas('collections', function ($collections) use ($collection, $collectionName) {
            return (
                $collections->count() === 1
                && $collections->first()->id === $collection->id
                && $collections->first()->name === $collectionName
            );
        });
    }

    // Test that multiple collections exist
    public function test_list_collections_with_multiple_collections()
    {
        $collections = ProductCollection::factory()->count(3)->sequence(
            ["name" => "Collection 1"],
            ["name" => "Collection 2"],
            ["name" => "Collection 3"]
        )->create();

        $response = $this->get(route('collections.index'));

        $response->assertStatus(200);
        $response->assertViewIs('collections.index');
        $response->assertViewHas('collections', function ($viewCollections) use ($collections) {
            if ($viewCollections->count() !== 3) {
                return false;
            }

            $viewCollectionNames = $viewCollections->pluck('name');
            $collectionNames = $collections->pluck('name');

            return $collectionNames->every(fn ($name) => $viewCollectionNames->contains($name));
        });
    }

    // Test retrieving a collection returns 404 for non-existing collection
    public function test_retrieve_collection_returns_404_for_non_existing()
    {
        $response = $this->get(route('collections.show', ['collection' => 999]));
        $response->assertStatus(404);
    }

    // Test showing a single collection returns 404 when other collections exist 
    public function test_retrieve_collection_returns_404_when_other_collections_exist()
    {
        ProductCollection::factory()->count(10)->create();

        $response = $this->get(route('collections.show', ['collection' => 999]));
        $response->assertStatus(404);
    }

    // Test retrieving a collection 
    public function test_can_retrieve_correct_collection()
    {
        $collectionName = "Test Collection";
        $collection = ProductCollection::factory()->create([
            'name' => $collectionName
        ]);
        ProductCollection::factory(5)->create();

        $response = $this->get(route('collections.show', ['collection' => $collection]));

        $response->assertStatus(200);
        $response->assertViewIs('collections.show');
        $response->assertViewHas('collection', function ($viewCollection) use ($collection, $collectionName) {
            return $viewCollection->id === $collection->id
                && $viewCollection->name === $collectionName;
        });
    }

    // Test retrieving a collection with products
    public function test_can_retrieve_collection_with_its_products()
    {
        $collection = ProductCollection::factory()->create();
        $product = Product::factory()->create();
        $collection->products()->attach($product->id);

        $response = $this->get(route('collections.products', $collection->id));

        $response->assertStatus(200);
        $response->assertViewHas('collection', function ($viewCollection) use ($collection, $product) {
            return $viewCollection->id === $collection->id
                && $viewCollection->products->contains($product);
        });
    }

    // Test can view the collection products page
    public function test_can_view_collection_products_page()
    {
        $collection = ProductCollection::factory()->create();

        $response = $this->get(route('collections.products', ["collection" => $collection]));

        $response->assertStatus(200);
        $response->assertViewIs('collections.products');
        $response->assertViewHas('products', function ($viewProducts) {
            return $viewProducts->count() === 0;
        });
    }

    // Test collection products page shows only products belonging to the specific collection
    public function test_collection_products_page_shows_only_its_own_products()
    {
        $collection1 = ProductCollection::factory()->create();
        $collection2 = ProductCollection::factory()->create();

        $products = Product::factory()->count(5)->sequence(
            ['name' => 'Product 1'],
            ['name' => 'Product 2'],
            ['name' => 'Product 3'],
            ['name' => 'Product 4'],
            ['name' => 'Product 5']
        )->create();

        $collection1->products()->attach([$products->get(0)->id, $products->get(2)->id]);
        $collection2->products()->attach([$products->get(1)->id, $products->get(3)->id, $products->get(4)->id]);

        $response = $this->get(route('collections.products', ["collection" => $collection1]));

        $response->assertStatus(200);
        $response->assertViewIs('collections.products');
        $response->assertViewHas('products', function ($viewProducts) use ($products) {
            return (
                $viewProducts->count() === 2 &&
                $viewProducts->pluck('id')->diff([$products->get(0)->id, $products->get(2)->id])->isEmpty()
            );
        });
    }

    // Test fetching products for a given collection with pagination
    public function test_can_view_paginated_products_for_collection()
    {
        $collection = ProductCollection::factory()->create();
        $products = Product::factory()->count(5)->create();
        $collection->products()->attach($products->pluck('id')->toArray());
        config(['pagination.per_page' => 3]);

        $response = $this->get(route('collections.products', ['collection' => $collection, 'page' => 2]));

        $response->assertStatus(200);
        $response->assertViewIs('collections.products');
        $response->assertViewHas('products', function ($viewProducts) {
            return $viewProducts->count() === 2;
        });
    }
}
