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
Route::post('/orders/create', 'App\Http\Controllers\OrderController@createOrder')->name('orders.store');
Route::get('/orders/confirmation/{orderId}', 'App\Http\Controllers\OrderController@showOrderConfirmation')->name('orders.confirmation');
Route::get('/orders/history/{customerId}', 'App\Http\Controllers\OrderController@listUserOrders')->name('orders.history');
