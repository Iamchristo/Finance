<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product:slug}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/coupons/validate', [CouponController::class, 'validateCode']);

Route::get('/downloads/file/{orderItem}', [DownloadController::class, 'file'])
    ->middleware('signed')
    ->name('downloads.file');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::post('/vendor/apply', [VendorController::class, 'apply']);
    Route::get('/vendor/me', [VendorController::class, 'me']);
    Route::get('/vendor/products', [ProductController::class, 'mine']);
    Route::post('/vendor/products', [ProductController::class, 'store']);
    Route::post('/vendor/products/{product}/files', [ProductController::class, 'uploadFile']);
    Route::post('/vendor/products/{product}/publish', [ProductController::class, 'publish']);

    Route::get('/wallet', [WalletController::class, 'show']);
    Route::post('/wallet/topup', [WalletController::class, 'topup']);

    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    Route::get('/downloads', [DownloadController::class, 'index']);
    Route::post('/downloads/{product}/request', [DownloadController::class, 'requestLink']);

    Route::middleware('role:administrator,super_administrator')->prefix('admin')->group(function () {
        Route::get('/vendors', [VendorController::class, 'index']);
        Route::post('/vendors/{vendor}/approve', [VendorController::class, 'approve']);
        Route::post('/orders/{order}/confirm-payment', [OrderController::class, 'confirmPayment']);
    });
});
