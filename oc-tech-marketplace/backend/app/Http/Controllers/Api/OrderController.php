<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->orders()->with('items.product')->latest()->get()
        );
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return response()->json($order->load('items.product', 'items.vendor'));
    }

    public function confirmPayment(Order $order, OrderService $orders)
    {
        $confirmed = $orders->markOrderPaid($order, $order->payment_gateway ?? 'bank_transfer', $order->payment_reference);

        AuditLog::record('order.payment_confirmed', $order, ['order_number' => $order->order_number, 'amount' => $order->grand_total]);

        return response()->json($confirmed);
    }
}
