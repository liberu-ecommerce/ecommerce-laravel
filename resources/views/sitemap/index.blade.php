<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
    </url>
    @foreach($products as $product)
    <url>
        <loc>{{ route('products.show', $product) }}</loc>
        <lastmod>{{ optional($product->updated_at)->toAtomString() }}</lastmod>
    </url>
    @endforeach
    @foreach($categories as $category)
    <url>
        <loc>{{ url('/products?category=' . $category->slug) }}</loc>
    </url>
    @endforeach
</urlset>
