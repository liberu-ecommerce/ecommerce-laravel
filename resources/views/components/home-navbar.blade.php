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
            <a href="{{ route('home') }}" class="shrink-0">
                <h1 class="text-2xl font-bold text-blue-600">{{ config('app.name') }}</h1>
            </a>

            <!-- Enhanced Search Bar -->
            <div class="hidden md:block grow mx-8">
                <div class="search-container">
                    <form action="{{ route('products.index') }}" method="GET" class="relative">
                        <div class="search-icon">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               placeholder="Search for products, brands, categories..." 
                               class="search-input"
                               autocomplete="off"
                               id="search-input">
                        <button type="submit" 
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 btn btn-primary btn-sm">
                            Search
                        </button>

                        <!-- Search Suggestions Dropdown -->
                        <div id="search-suggestions" 
                             class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden z-50 max-h-96 overflow-y-auto">
                            <!-- Dynamic suggestions will be populated here -->
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enhanced Navigation Icons -->
            <div class="flex items-center space-x-4">
                <!-- Wishlist -->
                <a href="{{ route('wishlist.index') }}" 
                   class="relative p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 group"
                   data-tooltip="Wishlist">
                    <svg class="h-6 w-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center transform transition-transform group-hover:scale-110">
                        0
                    </span>
                </a>

                <!-- Shopping Cart -->
                <div class="relative">
                    <a href="{{ route('cart.index') }}" 
                       class="relative p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 group"
                       data-tooltip="Shopping Cart">
                        <svg class="h-6 w-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h8"></path>
                        </svg>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center transform transition-transform group-hover:scale-110">
                            <livewire:cart-count />
                        </span>
                    </a>
                </div>

                <!-- User Account -->
                @auth
                    <div class="relative dropdown">
                        <button class="flex items-center p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                data-tooltip="Account">
                            <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-blue-600">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </span>
                            </div>
                        </button>
                        <div class="dropdown-menu hidden">
                            <div class="py-1">
                                <a href="{{ route('profile.show') }}" class="dropdown-item">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Profile
                                </a>
                                <a href="{{ route('orders.index') }}" class="dropdown-item">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    My Orders
                                </a>
                                <a href="{{ route('wishlist.index') }}" class="dropdown-item">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    Wishlist
                                </a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item w-full text-left text-red-600">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" 
                       class="btn btn-secondary btn-sm">
                        Login
                    </a>
                @endauth

                <!-- Mobile Menu Button -->
                <button class="md:hidden p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200" 
                        id="mobile-menu-button"
                        data-tooltip="Menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Enhanced Mobile Search -->
        <div class="mt-4 md:hidden">
            <div class="search-container">
                <form action="{{ route('products.index') }}" method="GET" class="relative">
                    <div class="search-icon">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           placeholder="Search products..." 
                           class="search-input pr-20"
                           autocomplete="off"
                           id="mobile-search-input">
                    <button type="submit" 
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 btn btn-primary btn-sm">
                        Search
                    </button>
                </form>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 bg-white border-t border-gray-200 py-4">
            <div class="space-y-2">
                @guest
                    <a href="{{ route('login') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">Login</a>
                    <a href="{{ route('register') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">Register</a>
                @else
                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">Profile</a>
                    <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">My Orders</a>
                    <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">Wishlist</a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-md">
                            Logout
                        </button>
                    </form>
                @endguest
            </div>
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
        // Enhanced Mobile Menu Toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');

                // Animate menu icon
                const icon = this.querySelector('svg');
                if (mobileMenu.classList.contains('hidden')) {
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    icon.style.transform = 'rotate(90deg)';
                }
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                    mobileMenu.classList.add('hidden');
                    mobileMenuButton.querySelector('svg').style.transform = 'rotate(0deg)';
                }
            });
        }

        // Enhanced Dropdown Functionality
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(function(dropdown) {
            const button = dropdown.querySelector('button');
            const menu = dropdown.querySelector('.dropdown-menu');

            if (button && menu) {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();

                    // Close other dropdowns
                    dropdowns.forEach(function(otherDropdown) {
                        if (otherDropdown !== dropdown) {
                            otherDropdown.querySelector('.dropdown-menu').classList.add('hidden');
                        }
                    });

                    // Toggle current dropdown
                    menu.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function() {
                    menu.classList.add('hidden');
                });
            }
        });

        // Enhanced Search Functionality
        const searchInputs = document.querySelectorAll('#search-input, #mobile-search-input');

        searchInputs.forEach(function(searchInput) {
            const suggestionsContainer = document.getElementById('search-suggestions');
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                clearTimeout(searchTimeout);

                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        fetchSearchSuggestions(query, suggestionsContainer);
                    }, 300);
                } else {
                    if (suggestionsContainer) {
                        suggestionsContainer.classList.add('hidden');
                    }
                }
            });

            searchInput.addEventListener('focus', function() {
                const query = this.value.trim();
                if (query.length >= 2 && suggestionsContainer) {
                    suggestionsContainer.classList.remove('hidden');
                }
            });

            // Close suggestions when clicking outside
            document.addEventListener('click', function(event) {
                if (suggestionsContainer && !searchInput.contains(event.target) && !suggestionsContainer.contains(event.target)) {
                    suggestionsContainer.classList.add('hidden');
                }
            });
        });

        // Search Suggestions Function
        function fetchSearchSuggestions(query, container) {
            if (!container) return;

            // Show loading state
            container.innerHTML = `
                <div class="p-4 text-center">
                    <div class="spinner mx-auto mb-2"></div>
                    <span class="text-sm text-gray-500">Searching...</span>
                </div>
            `;
            container.classList.remove('hidden');

            // Simulate API call (replace with actual endpoint)
            setTimeout(() => {
                const suggestions = [
                    { type: 'product', name: 'iPhone 15 Pro', category: 'Electronics' },
                    { type: 'product', name: 'Samsung Galaxy S24', category: 'Electronics' },
                    { type: 'category', name: 'Electronics', count: 150 },
                    { type: 'brand', name: 'Apple', count: 45 }
                ].filter(item => item.name.toLowerCase().includes(query.toLowerCase()));

                if (suggestions.length > 0) {
                    container.innerHTML = suggestions.map(suggestion => {
                        const icon = getSuggestionIcon(suggestion.type);
                        return `
                            <a href="#" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors duration-150">
                                ${icon}
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">${suggestion.name}</div>
                                    ${suggestion.category ? `<div class="text-xs text-gray-500">${suggestion.category}</div>` : ''}
                                    ${suggestion.count ? `<div class="text-xs text-gray-500">${suggestion.count} items</div>` : ''}
                                </div>
                            </a>
                        `;
                    }).join('');
                } else {
                    container.innerHTML = `
                        <div class="p-4 text-center text-gray-500">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span class="text-sm">No results found for "${query}"</span>
                        </div>
                    `;
                }
            }, 500);
        }

        function getSuggestionIcon(type) {
            const icons = {
                product: `<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>`,
                category: `<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>`,
                brand: `<svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>`
            };
            return icons[type] || icons.product;
        }

        // Cart Animation
        window.addToCartAnimation = function(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="spinner mr-2"></span>Adding...';
            button.disabled = true;

            setTimeout(() => {
                button.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Added!';
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');

                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-primary');
                }, 2000);
            }, 1000);
        };

        // Quick View Modal
        window.quickView = function(productId) {
            // Create modal overlay
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content max-w-4xl">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Quick View</h3>
                            <button onclick="this.closest('.modal-overlay').remove()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="text-center py-8">
                            <div class="spinner mx-auto mb-4"></div>
                            <p class="text-gray-500">Loading product details...</p>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // Simulate loading product data
            setTimeout(() => {
                modal.querySelector('.modal-content').innerHTML = `
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold">Product Quick View</h3>
                            <button onclick="this.closest('.modal-overlay').remove()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <img src="/images/placeholder.png" alt="Product" class="w-full h-64 object-cover rounded-lg">
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold mb-2">Sample Product</h4>
                                <p class="text-gray-600 mb-4">This is a sample product description for the quick view modal.</p>
                                <div class="text-2xl font-bold text-blue-600 mb-4">$99.99</div>
                                <button class="btn btn-primary w-full">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                `;
            }, 1000);
        };

        // Add to Wishlist
        window.addToWishlist = function(productId) {
            // Show success message
            const message = document.createElement('div');
            message.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in-right';
            message.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Added to wishlist!
                </div>
            `;
            document.body.appendChild(message);

            setTimeout(() => {
                message.remove();
            }, 3000);
        };
    });
</script>