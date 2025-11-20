<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', 'Your premier ecommerce destination')">
    <meta name="keywords" content="@yield('meta_keywords', 'ecommerce, online shopping')">

    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="@yield('og_title', config('app.name'))">
    <meta property="og:description" content="@yield('og_description', 'Your premier ecommerce destination')">
    <meta property="og:image" content="@yield('og_image', asset('images/logo.png'))">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <x-home-navbar />

    <!-- Enhanced Flash Messages -->
    <div id="flash-messages" class="fixed top-4 right-4 z-50 space-y-2">
        @if (session('success'))
            <div class="alert alert-success shadow-lg max-w-md animate-slide-in-right" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                    <button type="button" class="ml-auto text-green-600 hover:text-green-800" onclick="this.parentElement.parentElement.remove()">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error shadow-lg max-w-md animate-slide-in-right" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                    <button type="button" class="ml-auto text-red-600 hover:text-red-800" onclick="this.parentElement.parentElement.remove()">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning shadow-lg max-w-md animate-slide-in-right" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">{{ session('warning') }}</span>
                    <button type="button" class="ml-auto text-yellow-600 hover:text-yellow-800" onclick="this.parentElement.parentElement.remove()">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <main class="grow">
        @yield('content')
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">{{ config('app.name') }}</h3>
                    <p class="text-gray-300">Your premier ecommerce destination for quality products.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-gray-300 hover:text-white">Home</a></li>
                        <li><a href="{{ route('products.index') }}" class="text-gray-300 hover:text-white">Products</a></li>
                        <li><a href="{{ route('contact') }}" class="text-gray-300 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Customer Service</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white">FAQ</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white">Shipping Policy</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white">Return Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Connect With Us</h3>
                    <div class="flex space-x-4">
                        <a href="{{ app(\App\Settings\GeneralSettings::class)->facebook_url }}" class="text-gray-300 hover:text-white"><i class="fab fa-facebook"></i></a>
                        <a href="{{ app(\App\Settings\GeneralSettings::class)->twitter_url }}" class="text-gray-300 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="{{ app(\App\Settings\GeneralSettings::class)->youtube_url }}" class="text-gray-300 hover:text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-4 text-center text-gray-400">
                <p>{{ app(\App\Settings\GeneralSettings::class)->footer_copyright }}</p>
            </div>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')

    <!-- Enhanced JavaScript for UI/UX -->
    <script>
        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('#flash-messages .alert');
            flashMessages.forEach(function(message) {
                setTimeout(function() {
                    message.style.opacity = '0';
                    message.style.transform = 'translateX(100%)';
                    setTimeout(function() {
                        message.remove();
                    }, 300);
                }, 5000);
            });
        });

        // Enhanced loading states for forms
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    const submitButton = form.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<span class="spinner mr-2"></span>Loading...';
                        submitButton.classList.add('loading');
                    }
                });
            });
        });

        // Enhanced tooltips
        function initTooltips() {
            const tooltipElements = document.querySelectorAll('[data-tooltip]');
            tooltipElements.forEach(function(element) {
                element.addEventListener('mouseenter', function() {
                    const tooltipText = this.getAttribute('data-tooltip');
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    tooltip.textContent = tooltipText;
                    tooltip.id = 'tooltip-' + Math.random().toString(36).substr(2, 9);

                    document.body.appendChild(tooltip);

                    const rect = this.getBoundingClientRect();
                    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';

                    this.tooltipId = tooltip.id;
                });

                element.addEventListener('mouseleave', function() {
                    if (this.tooltipId) {
                        const tooltip = document.getElementById(this.tooltipId);
                        if (tooltip) {
                            tooltip.remove();
                        }
                    }
                });
            });
        }

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', initTooltips);

        // Enhanced smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Enhanced image lazy loading with fade-in effect
        function initLazyLoading() {
            const images = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('opacity-0');
                        img.classList.add('opacity-100');
                        observer.unobserve(img);
                    }
                });
            });

            images.forEach(img => {
                img.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                imageObserver.observe(img);
            });
        }

        // Initialize lazy loading
        document.addEventListener('DOMContentLoaded', initLazyLoading);

        // Enhanced search with debouncing
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Add search suggestions functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInputs = document.querySelectorAll('input[name="search"]');
            searchInputs.forEach(function(input) {
                const debouncedSearch = debounce(function(query) {
                    if (query.length > 2) {
                        // Add search suggestions logic here
                        console.log('Searching for:', query);
                    }
                }, 300);

                input.addEventListener('input', function() {
                    debouncedSearch(this.value);
                });
            });
        });
    </script>

    <!-- Custom CSS for animations -->
    <style>
        @keyframes slide-in-right {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-slide-in-right {
            animation: slide-in-right 0.3s ease-out;
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fade-in-up 0.6s ease-out;
        }

        @keyframes pulse-scale {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .animate-pulse-scale {
            animation: pulse-scale 2s infinite;
        }

        /* Enhanced focus styles for accessibility */
        *:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* Enhanced button hover effects */
        .btn:hover {
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Enhanced card hover effects */
        .card:hover {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Enhanced loading spinner */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            animation: spin 1s linear infinite;
        }

        /* Enhanced skeleton loading */
        @keyframes skeleton-loading {
            0% {
                background-position: -200px 0;
            }
            100% {
                background-position: calc(200px + 100%) 0;
            }
        }

        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200px 100%;
            animation: skeleton-loading 1.5s infinite;
        }
    </style>
</body>
</html>