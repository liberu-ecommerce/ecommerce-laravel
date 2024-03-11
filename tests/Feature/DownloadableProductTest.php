&lt;?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\DownloadableProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DownloadableProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_file_upload_for_downloadable_product()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post(route('products.store'), [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100,
            'category' => 'Test Category',
            'inventory_count' => 10,
            'product_file' => UploadedFile::fake()->create('testfile.pdf', 1000),
            'download_limit' => 5,
        ]);

        $this->assertDatabaseHas('downloadable_products', [
            'product_id' => $product->id,
            'file_url' => Storage::url('public/downloadable_products/testfile.pdf'),
            'download_limit' => 5,
        ]);
    }

    public function test_secure_download_link_generation()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        DownloadableProduct::factory()->create([
            'product_id' => $product->id,
            'file_url' => 'downloadable_products/testfile.pdf',
            'download_limit' => 5,
        ]);

        $response = $this->actingAs($user)->get(route('download.generate-link', $product->id));

        $response->assertStatus(200);
        $response->assertJson(['url' => true]);
    }

    public function test_download_limits()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $downloadableProduct = DownloadableProduct::factory()->create([
            'product_id' => $product->id,
            'file_url' => 'downloadable_products/testfile.pdf',
            'download_limit' => 1,
        ]);

        $this->actingAs($user)->get(route('download.serve-file', $product->id));
        $response = $this->actingAs($user)->get(route('download.serve-file', $product->id));

        $response->assertStatus(403);
    }

    public function test_access_control_for_downloadable_products()
    {
        $user = User::factory()->create();
        $unauthorizedUser = User::factory()->create();
        $product = Product::factory()->create();
        DownloadableProduct::factory()->create([
            'product_id' => $product->id,
            'file_url' => 'downloadable_products/testfile.pdf',
            'download_limit' => 5,
        ]);

        $response = $this->actingAs($unauthorizedUser)->get(route('download.serve-file', $product->id));

        $response->assertStatus(403);
    }
}
