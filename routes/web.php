<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ShippingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', 'App\Http\Controllers\HomeController@index');

// Checkout routes
Route::get('/checkout', [CheckoutController::class, 'initiateCheckout'])->name('checkout.initiate');
Route::post('/checkout/process', [CheckoutController::class, 'processCheckout'])->name('checkout.process');
Route::get('/checkout/confirmation/{order}', [CheckoutController::class, 'showConfirmation'])->name('checkout.confirmation');

// Shipping routes
Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');
Route::post('/shipping', [ShippingController::class, 'store'])->name('shipping.store');
Route::put('/shipping/{shippingMethod}', [ShippingController::class, 'update'])->name('shipping.update');
Route::delete('/shipping/{shippingMethod}', [ShippingController::class, 'destroy'])->name('shipping.destroy');

Route::prefix('payment_methods')->group(function () {
    Route::get('/', 'PaymentMethodController@index')->name('payment_methods.index');
    Route::post('/store', 'PaymentMethodController@addPaymentMethod')->name('payment_methods.store');
    Route::get('/edit/{id}', 'PaymentMethodController@editPaymentMethod')->name('payment_methods.edit');
    Route::post('/update/{id}', 'PaymentMethodController@editPaymentMethod')->name('payment_methods.update');
    Route::delete('/destroy/{id}', 'PaymentMethodController@deletePaymentMethod')->name('payment_methods.destroy');
    Route::post('/set_default/{id}', 'PaymentMethodController@setDefaultPaymentMethod')->name('payment_methods.setDefault');
});

Route::post('/payment', 'App\Http\Controllers\StripePaymentController@createOneTimePayment')->name('payment.create');

Route::get('/subscriptions', 'App\Http\Controllers\SubscriptionController@viewAvailableSubscriptions')->name('subscriptions.view');
Route::post('/subscription', 'App\Http\Controllers\SubscriptionController@subscribeToPlan')->name('subscription.create');
Route::patch('/subscription/change', 'App\Http\Controllers\SubscriptionController@changePlan')->name('subscription.change-plan');
Route::delete('/subscription/cancel', 'App\Http\Controllers\SubscriptionController@cancelSubscription')->name('subscription.cancel');

Route::post('/paypal/payment', 'App\Http\Controllers\PayPalPaymentController@createOneTimePayment')->name('paypal.payment.create');
Route::post('/paypal/subscription', 'App\Http\Controllers\PayPalPaymentController@createSubscription')->name('paypal.subscription.create');
Route::patch('/paypal/subscription/update', 'App\Http\Controllers\PayPalPaymentController@updateSubscription')->name('paypal.subscription.update');
Route::delete('/paypal/subscription/cancel', 'App\Http\Controllers\PayPalPaymentController@cancelSubscription')->name('paypal.subscription.cancel');

// Product routes
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products', [ProductController::class, 'list'])->name('products.list');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::patch('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'delete'])->name('products.delete');

Route::middleware('auth')->group(function () {
    Route::get('/download/{productId}', 'App\Http\Controllers\DownloadController@generateSecureLink')->name('download.generate-link');
    Route::get('/download/file/{productId}', 'App\Http\Controllers\DownloadController@serveFile')->name('download.serve-file');
});

Route::post('/reviews', 'App\Http\Controllers\ReviewController@store')->name('reviews.store');
Route::post('/reviews/approve/{id}', 'App\Http\Controllers\ReviewController@approve')->name('reviews.approve');
Route::get('/products/{product}/reviews', 'App\Http\Controllers\ReviewController@show')->name('reviews.show');
Route::get('/products/{product}/ratings/average', 'App\Http\Controllers\RatingController@calculateAverageRating')->name('ratings.average');

Route::get('/site-settings', 'App\Http\Controllers\SiteSettingController@index')->name('site_settings.index');
Route::get('/site-settings/{id}/edit', 'App\Http\Controllers\SiteSettingController@edit')->name('site_settings.edit');
Route::post('/site-settings/{id}', 'App\Http\Controllers\SiteSettingController@update')->name('site_settings.update');

require __DIR__.'/socialstream.php';
