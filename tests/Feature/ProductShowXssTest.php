<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductShowXssTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_json_ld_neutralizes_a_script_breakout_in_the_name(): void
    {
        $product = Product::factory()->create([
            'name' => 'Evil</script><script>alert(1)</script>',
            'pricing_type' => 'fixed',
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertStatus(200);
        // The raw JSON-LD echo must not emit a script tag that would execute
        // (JSON_HEX_TAG escapes < and > so the </script> breakout can't close it).
        $response->assertDontSee('<script>alert(1)', false);
    }

    public function test_product_json_ld_neutralizes_a_breakout_in_the_description(): void
    {
        $product = Product::factory()->create([
            'name' => 'Clean name',
            'description' => 'x</script><script>alert(2)</script>',
            'pricing_type' => 'fixed',
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertDontSee('<script>alert(2)', false);
    }
}
