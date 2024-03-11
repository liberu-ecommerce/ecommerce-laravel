<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::post('/checkout/guest', 'App\Http\Controllers\CheckoutController@guestCheckout')->name('checkout.guest');
Route::get('/checkout/summary', 'App\Http\Controllers\CheckoutController@summary')->name('checkout.summary');


Route::prefix('payment_methods')->group(function () {
    Route::get('/', 'PaymentMethodController@index')->name('payment_methods.index');
    Route::post('/store', 'PaymentMethodController@addPaymentMethod')->name('payment_methods.store');
    Route::get('/edit/{id}', 'PaymentMethodController@editPaymentMethod')->name('payment_methods.edit');
    Route::post('/update/{id}', 'PaymentMethodController@editPaymentMethod')->name('payment_methods.update');
    Route::delete('/destroy/{id}', 'PaymentMethodController@deletePaymentMethod')->name('payment_methods.destroy');
    Route::post('/set_default/{id}', 'PaymentMethodController@setDefaultPaymentMethod')->name('payment_methods.setDefault');
});


Route::get('/checkout', function () {
    return view('checkout');
})->name('checkout');

Route::post('/payment', 'App\Http\Controllers\StripePaymentController@createOneTimePayment')->name('payment.create');

Route::get('/subscriptions', 'App\Http\Controllers\SubscriptionController@viewAvailableSubscriptions')->name('subscriptions.view');

Route::post('/subscription', 'App\Http\Controllers\SubscriptionController@subscribeToPlan')->name('subscription.create');

Route::patch('/subscription/change', 'App\Http\Controllers\SubscriptionController@changePlan')->name('subscription.change-plan');

Route::delete('/subscription/cancel', 'App\Http\Controllers\SubscriptionController@cancelSubscription')->name('subscription.cancel');
Route::post('/paypal/payment', 'App\Http\Controllers\PayPalPaymentController@createOneTimePayment')->name('paypal.payment.create');
Route::post('/paypal/subscription', 'App\Http\Controllers\PayPalPaymentController@createSubscription')->name('paypal.subscription.create');
Route::patch('/paypal/subscription/update', 'App\Http\Controllers\PayPalPaymentController@updateSubscription')->name('paypal.subscription.update');
Route::delete('/paypal/subscription/cancel', 'App\Http\Controllers\PayPalPaymentController@cancelSubscription')->name('paypal.subscription.cancel');

use App\Http\Controllers\ProductController;

Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products', [ProductController::class, 'list'])->name('products.list');
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





