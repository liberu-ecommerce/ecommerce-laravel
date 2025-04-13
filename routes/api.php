<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DropshippingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Dropshipping API routes
Route::middleware('auth:sanctum')->prefix('dropshipping')->group(function () {
    Route::get('/suppliers', [DropshippingController::class, 'suppliers']);
    Route::post('/check-availability', [DropshippingController::class, 'checkAvailability']);
    Route::post('/place-order', [DropshippingController::class, 'placeOrder']);
    Route::post('/track-order', [DropshippingController::class, 'trackOrder']);
});