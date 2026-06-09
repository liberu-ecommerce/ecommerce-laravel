<?php

namespace Tests\Unit;

use App\Models\TaxonomyCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxonomyCategoryModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(array $overrides = []): TaxonomyCategory
    {
        return TaxonomyCategory::create(array_merge([
            'name' => 'Electronics',
            'slug' => 'electronics-' . uniqid(),
            'is_active' => true,
            'level' => 0,
        ], $overrides));
    }

    public function test_taxonomy_category_can_be_created(): void
    {
        $cat = $this->makeCategory();

        $this->assertInstanceOf(TaxonomyCategory::class, $cat);
        $this->assertEquals('Electronics', $cat->name);
    }

    public function test_is_active_is_boolean_cast(): void
    {
        $cat = $this->makeCategory(['is_active' => true]);

        $this->assertIsBool($cat->fresh()->is_active);
        $this->assertTrue($cat->fresh()->is_active);
    }

    public function test_metadata_is_array_cast(): void
    {
        $cat = $this->makeCategory([
            'metadata' => ['schema_type' => 'Product', 'breadcrumb' => true],
        ]);

        $this->assertIsArray($cat->fresh()->metadata);
        $this->assertEquals('Product', $cat->fresh()->metadata['schema_type']);
    }

    public function test_parent_child_relationship(): void
    {
        $parent = $this->makeCategory(['name' => 'Parent']);
        $child = $this->makeCategory([
            'name' => 'Child',
            'parent_id' => $parent->id,
            'level' => 1,
        ]);

        $this->assertInstanceOf(TaxonomyCategory::class, $child->parent);
        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertCount(1, $parent->children);
    }

    public function test_active_scope(): void
    {
        $active = $this->makeCategory(['name' => 'Active Cat', 'is_active' => true]);
        $inactive = $this->makeCategory(['name' => 'Inactive Cat', 'is_active' => false]);

        $results = TaxonomyCategory::active()->pluck('id');

        $this->assertContains($active->id, $results);
        $this->assertNotContains($inactive->id, $results);
    }
}
