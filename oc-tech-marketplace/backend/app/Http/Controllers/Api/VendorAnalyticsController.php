<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Wallet;
use Illuminate\Http\Request;

class VendorAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;

        abort_unless($vendor, 404, 'No vendor profile found.');

        $items = OrderItem::query()
            ->where('vendor_id', $vendor->id)
            ->whereHas('order', fn ($q) => $q->where('status', 'completed'));

        $totalRevenue = (clone $items)->sum('vendor_earnings');
        $totalSales = (clone $items)->count();

        $topProducts = $vendor->products()
            ->withCount(['reviews'])
            ->orderByDesc('sales_count')
            ->limit(5)
            ->get(['id', 'title', 'sales_count', 'average_rating']);

        $recentOrders = (clone $items)
            ->with(['order', 'product'])
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'total_revenue' => round((float) $totalRevenue, 2),
            'total_sales' => $totalSales,
            'wallet_balance' => Wallet::firstOrCreate(['user_id' => $vendor->user_id])->balance,
            'product_count' => $vendor->products()->count(),
            'top_products' => $topProducts,
            'recent_orders' => $recentOrders,
        ]);
    }
}
