<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class VendorCouponController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->activeVendor();

        abort_unless($vendor, 404, 'No vendor profile found.');

        return response()->json($vendor->coupons()->latest()->get());
    }

    public function store(Request $request)
    {
        $vendor = $request->user()->activeVendor();

        abort_unless($vendor, 404, 'No vendor profile found.');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $coupon = $vendor->coupons()->create([
            'code' => strtoupper($data['code']),
            'type' => $data['type'],
            'value' => $data['value'],
            'usage_limit' => $data['usage_limit'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => true,
        ]);

        return response()->json($coupon, 201);
    }

    public function update(Request $request, Coupon $coupon)
    {
        abort_unless($coupon->vendor_id === $request->user()->activeVendor()?->id, 403);

        $data = $request->validate([
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $coupon->update($data);

        return response()->json($coupon);
    }

    public function destroy(Request $request, Coupon $coupon)
    {
        abort_unless($coupon->vendor_id === $request->user()->activeVendor()?->id, 403);

        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted.']);
    }
}
