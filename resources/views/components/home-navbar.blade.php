<header class="bg-white shadow-md">
    <!-- Top Bar -->
    <div class="bg-blue-600 text-white py-2">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="mailto:support@example.com" class="text-sm hover:text-blue-200 flex items-center">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    support@example.com
                </a>
                <a href="tel:+1234567890" class="text-sm hover:text-blue-200 flex items-center">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    (123) 456-7890
                </a>
            </div>
            <div class="flex items-center space-x-4">
                @guest
                    <a href="{{ route('login') }}" class="text-sm hover:text-blue-200">Login</a>
                    <a href="{{ route('register') }}" class="text-sm hover:text-blue-200">Register</a>
                @else
                    <div class="relative group">
                        <button class="text-sm hover:text-blue-200 flex items-center">
                            {{ Auth::user()->name }}
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Orders</a>
                            <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Wishlist</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @endguest
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex-shrink-0">
                <h1 class="text-2xl font-bold text-blue-600">{{ config('app.name') }}</h1>
            </a>

            <!-- Search Bar -->
            <div class="hidden md:block flex-grow mx-8">
                <form action="{{ route('products.index') }}" method="GET" class="flex">
                    <input type="text" name="search" placeholder="Search for products..." class="w-full px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Navigation Icons -->
            <div class="flex items-center space-x-6">
                <a href="{{ route('wishlist.index') }}" class="text-gray-600 hover:text-blue-600 relative">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                </a>
                <a href="{{ route('cart.index') }}" class="text-gray-600 hover:text-blue-600 relative">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        @livewire('cart-count')
                    </span>
                </a>
                <button class="md:hidden text-gray-600 hover:text-blue-600" id="mobile-menu-button">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Search (visible only on mobile) -->
        <div class="mt-4 md:hidden">
            <form action="{{ route('products.index') }}" method="GET" class="flex">
                <input type="text" name="search" placeholder="Search for products..." class="w-full px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-li nejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Category Navigation -->
    <nav class="bg-gray-100 py-3 shadow-inner">
        <div class="container mx-auto px-4">
            <ul class="flex space-x-8 overflow-x-auto pb-1 hide-scrollbar">
                <li><a href="{{ route('products.index') }}" class="text-gray-700 hover:text-blue-600 whitespace-nowrap font-medium">All Products</a></li>
                <li><a href="{{ route('products.index', ['category' => 'electronics']) }}" class="text-gray-700 hover:text-blue-600 whitespace-nowrap">Electronics</a></li>
                <li><a href="{{ route('products.index', ['category' => 'clothing']) }}" class="text-gray-700 hover:text-blue-600 whitespace-nowrap">Clothing</a></li>
                <li><a href="{{ route('products.index', ['category' => 'home']) }}" class="text-gray-700 hover:text-blue-600 whitespace-nowrap">Home & Living</a></li>
                <li><a href="{{ route('products.index', ['category' => 'books']) }}" class="text-gray-700 hover:text-blue-600 whitespace-nowrap">Books</a></li>
                <li><a href="{{ route('products.index', ['category' => 'beauty']) }}" class="text-gray-700 hover:text-blue-600 whitespace-nowrap">Beauty</a></li>
                <li><a href="{{ route('products.index', ['category' => 'sports']) }}" class="text-gray-700 hover:text-blue-600 whitespace-nowrap">Sports</a></li>
                <li><a href="{{ route('products.index', ['category' => 'toys']) }}" class="text-gray-700 hover:text-blue-600 whitespace-nowrap">Toys</a></li>
            </ul>
        </div>
    </nav>
</header>

<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }
    });
</script>