<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Services\OrderService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.license_id' => ['required', 'integer'],
            'coupon_code' => ['nullable', 'string'],
            'payment_method' => ['required', 'in:wallet,bank_transfer'],
        ]);

        $order = $this->orders->checkout(
            $request->user(),
            $data['items'],
            $data['coupon_code'] ?? null,
            $data['payment_method'],
        );

        return response()->json($order, 201);
    }

    public function storeBundle(Request $request)
    {
        $data = $request->validate([
            'bundle_id' => ['required', 'exists:bundles,id'],
            'payment_method' => ['required', 'in:wallet,bank_transfer'],
        ]);

        $bundle = Bundle::where('is_active', true)->findOrFail($data['bundle_id']);

        $order = $this->orders->checkoutBundle($request->user(), $bundle, $data['payment_method']);

        return response()->json($order, 201);
    }
}
