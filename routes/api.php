<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DropshippingController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CollectionController;

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

// Product Management API routes
Route::middleware('auth:sanctum')->prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/{identifier}', [ProductController::class, 'show']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::patch('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});
// Collection API routes
Route::middleware('auth:sanctum')->prefix('collections')->group(function () {
    Route::get('/', [CollectionController::class, 'index']);
    Route::post('/', [CollectionController::class, 'store']);
    Route::get('/{idOrSlug}', [CollectionController::class, 'show']);
    Route::put('/{id}', [CollectionController::class, 'update']);
    Route::post('/{id}/products', [CollectionController::class, 'addProducts']);
    Route::delete('/{id}/products', [CollectionController::class, 'removeProducts']);
    Route::delete('/{id}', [CollectionController::class, 'destroy']);
});