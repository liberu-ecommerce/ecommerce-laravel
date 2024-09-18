<div>
    <section class="products-section">
        <h2>Our Products</h2>
        <p></p>
        <div class="products-grid grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($products as $product)
                <div class="product-card col-span-1 max-w-sm bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                    <img class="rounded-t-lg" src="{{ $product->imageUrl }}" alt="{{ $product->name }}">
                    <div class="px-5 pb-5">
                        <a href="{{ route('products.show', ["product" => $product]) }}">
                            <h5 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                                {{ $product->name }}
                            </h5>
                        </a>
                        <p>{{ $product->description }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($product->price, 2) }}</span>
                            <button class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" wire:click="addToCart('{{ $product->id }}')">Add to Cart</button>
                        </div>
                        
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
