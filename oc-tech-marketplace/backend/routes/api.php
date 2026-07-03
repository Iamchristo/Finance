<?php

use App\Http\Controllers\Api\AdminAnalyticsController;
use App\Http\Controllers\Api\AdminAuditLogController;
use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\AdminReviewController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\VendorAnalyticsController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\VendorCouponController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\WithdrawalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

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
    Route::patch('/vendor/me', [VendorController::class, 'update']);
    Route::get('/vendor/products', [ProductController::class, 'mine']);
    Route::post('/vendor/products', [ProductController::class, 'store']);
    Route::post('/vendor/products/{product}/files', [ProductController::class, 'uploadFile']);
    Route::post('/vendor/products/{product}/publish', [ProductController::class, 'publish']);
    Route::get('/vendor/analytics', [VendorAnalyticsController::class, 'index']);
    Route::get('/vendor/coupons', [VendorCouponController::class, 'index']);
    Route::post('/vendor/coupons', [VendorCouponController::class, 'store']);
    Route::patch('/vendor/coupons/{coupon}', [VendorCouponController::class, 'update']);
    Route::delete('/vendor/coupons/{coupon}', [VendorCouponController::class, 'destroy']);
    Route::get('/vendor/withdrawals', [WithdrawalController::class, 'index']);
    Route::post('/vendor/withdrawals', [WithdrawalController::class, 'store']);
    Route::post('/reviews/{review}/reply', [ReviewController::class, 'reply']);
    Route::post('/reviews/{review}/report', [ReviewController::class, 'report']);

    Route::get('/wallet', [WalletController::class, 'show']);
    Route::post('/wallet/topup', [WalletController::class, 'topup']);

    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    Route::get('/downloads', [DownloadController::class, 'index']);
    Route::post('/downloads/{product}/request', [DownloadController::class, 'requestLink']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{product}', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy']);

    Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);

    Route::get('/support/tickets', [SupportTicketController::class, 'index']);
    Route::post('/support/tickets', [SupportTicketController::class, 'store']);
    Route::get('/support/tickets/{supportTicket}', [SupportTicketController::class, 'show']);
    Route::post('/support/tickets/{supportTicket}/reply', [SupportTicketController::class, 'reply']);
    Route::post('/support/tickets/{supportTicket}/close', [SupportTicketController::class, 'close']);

    Route::middleware('role:administrator,super_administrator')->prefix('admin')->group(function () {
        Route::get('/analytics', [AdminAnalyticsController::class, 'index']);
        Route::get('/audit-logs', [AdminAuditLogController::class, 'index']);

        Route::get('/vendors', [VendorController::class, 'index']);
        Route::post('/vendors/{vendor}/approve', [VendorController::class, 'approve']);
        Route::post('/vendors/{vendor}/reject', [VendorController::class, 'reject']);

        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::post('/orders/{order}/confirm-payment', [OrderController::class, 'confirmPayment']);

        Route::get('/withdrawals', [WithdrawalController::class, 'adminIndex']);
        Route::post('/withdrawals/{withdrawalRequest}/approve', [WithdrawalController::class, 'approve']);
        Route::post('/withdrawals/{withdrawalRequest}/reject', [WithdrawalController::class, 'reject']);

        Route::get('/reviews/reported', [AdminReviewController::class, 'index']);
        Route::post('/reviews/{review}/dismiss-report', [AdminReviewController::class, 'dismiss']);
        Route::post('/reviews/{review}/hide', [AdminReviewController::class, 'hide']);
    });
});
