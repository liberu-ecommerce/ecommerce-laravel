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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/checkout', function () {
    return view('checkout');
})->name('checkout');

Route::post('/payment', 'App\Http\Controllers\StripePaymentController@createOneTimePayment')->name('payment.create');

Route::get('/subscriptions', 'App\Http\Controllers\SubscriptionController@viewAvailableSubscriptions')->name('subscriptions.view');

Route::post('/subscription', 'App\Http\Controllers\SubscriptionController@subscribeToPlan')->name('subscription.create');

Route::patch('/subscription/change', 'App\Http\Controllers\SubscriptionController@changePlan')->name('subscription.change-plan');

Route::delete('/subscription/cancel', 'App\Http\Controllers\SubscriptionController@cancelSubscription')->name('subscription.cancel');
