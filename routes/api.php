<?php

use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\DropshippingController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderRefundController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReturnRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
// Order API routes (customer-facing: read-only, scoped to the authenticated user)
Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/{id}', [OrderController::class, 'show']);
    // Staff-only (role checked in the controller): initiate a refund on an order.
    Route::post('/{order}/refund', [OrderRefundController::class, 'store']);
    // Customer requests a return for their own order.
    Route::post('/{order}/returns', [ReturnRequestController::class, 'store']);
});

// Returns: customers read their own, staff read/act on all (roles checked in the controller).
Route::middleware('auth:sanctum')->prefix('returns')->group(function () {
    Route::get('/', [ReturnRequestController::class, 'index']);
    Route::get('/{returnRequest}', [ReturnRequestController::class, 'show']);
    Route::post('/{returnRequest}/approve', [ReturnRequestController::class, 'approve']);
    Route::post('/{returnRequest}/received', [ReturnRequestController::class, 'markReceived']);
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
