<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WithdrawalRequest;

class AdminAnalyticsController extends Controller
{
    public function index()
    {
        $completedOrders = Order::where('status', 'completed');

        $topVendors = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('status', 'completed'))
            ->selectRaw('vendor_id, SUM(vendor_earnings) as total_earnings')
            ->groupBy('vendor_id')
            ->orderByDesc('total_earnings')
            ->limit(5)
            ->with('vendor:id,store_name')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->vendor_id,
                'store_name' => $row->vendor->store_name,
                'total_earnings' => round((float) $row->total_earnings, 2),
            ]);

        return response()->json([
            'total_gmv' => round((float) (clone $completedOrders)->sum('grand_total'), 2),
            'platform_revenue' => round((float) OrderItem::whereHas('order', fn ($q) => $q->where('status', 'completed'))->sum('commission_amount'), 2),
            'total_orders' => (clone $completedOrders)->count(),
            'total_users' => User::count(),
            'total_vendors' => Vendor::count(),
            'total_products' => Product::where('status', 'published')->count(),
            'pending_vendor_approvals' => Vendor::where('verification_status', 'pending')->count(),
            'pending_withdrawals' => WithdrawalRequest::where('status', 'pending')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'top_vendors' => $topVendors,
        ]);
    }
}
