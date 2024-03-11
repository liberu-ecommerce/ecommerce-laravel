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
Route::post('/checkout/guest', 'App\Http\Controllers\CheckoutController@guestCheckout')->name('checkout.guest');
Route::get('/checkout/summary', 'App\Http\Controllers\CheckoutController@summary')->name('checkout.summary');
