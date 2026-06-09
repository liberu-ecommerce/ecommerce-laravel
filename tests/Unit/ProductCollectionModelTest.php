<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCollectionModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeCollection(array $overrides = []): ProductCollection
    {
        return ProductCollection::create(array_merge([
            'name' => 'Summer Collection',
            'slug' => 'summer-collection-' . uniqid(),
            'description' => 'Best summer products',
            'price' => 99.99,
        ], $overrides));
    }

    private function makeProduct(): Product
    {
        $category = ProductCategory::create([
            'name' => 'Coll Cat',
            'slug' => 'coll-cat-' . uniqid(),
        ]);
        return Product::create([
            'name' => 'Collection Product',
            'slug' => 'coll-prod-' . uniqid(),
            'price' => 25.00,
            'category_id' => $category->id,
            'inventory_count' => 5,
        ]);
    }

    public function test_collection_can_be_created(): void
    {
        $collection = $this->makeCollection();

        $this->assertInstanceOf(ProductCollection::class, $collection);
        $this->assertEquals('Summer Collection', $collection->name);
    }

    public function test_get_price_returns_price(): void
    {
        $collection = $this->makeCollection(['price' => 149.99]);

        $this->assertEquals(149.99, $collection->getPrice());
    }

    public function test_get_name_returns_name(): void
    {
        $collection = $this->makeCollection(['name' => 'Winter Sale']);

        $this->assertEquals('Winter Sale', $collection->getName());
    }

    public function test_products_relationship(): void
    {
        $collection = $this->makeCollection();
        $product = $this->makeProduct();

        $collection->products()->attach($product->id, ['quantity' => 2]);

        $this->assertCount(1, $collection->products);
        $this->assertEquals($product->id, $collection->products->first()->id);
    }

    public function test_soft_deletes_collection(): void
    {
        $collection = $this->makeCollection();
        $id = $collection->id;

        $collection->delete();

        $this->assertNull(ProductCollection::find($id));
        $this->assertNotNull(ProductCollection::withTrashed()->find($id));
    }
}
