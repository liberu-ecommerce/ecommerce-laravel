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
Route::prefix('payment_methods')->group(function () {
    Route::get('/', 'PaymentMethodController@index')->name('payment_methods.index');
    Route::post('/store', 'PaymentMethodController@addPaymentMethod')->name('payment_methods.store');
    Route::get('/edit/{id}', 'PaymentMethodController@editPaymentMethod')->name('payment_methods.edit');
    Route::post('/update/{id}', 'PaymentMethodController@editPaymentMethod')->name('payment_methods.update');
    Route::delete('/destroy/{id}', 'PaymentMethodController@deletePaymentMethod')->name('payment_methods.destroy');
    Route::post('/set_default/{id}', 'PaymentMethodController@setDefaultPaymentMethod')->name('payment_methods.setDefault');
});
