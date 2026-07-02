<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validateCode(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $coupon = Coupon::where('code', $data['code'])->where('is_active', true)->first();

        if (! $coupon
            || ($coupon->starts_at && $coupon->starts_at->isFuture())
            || ($coupon->expires_at && $coupon->expires_at->isPast())
            || ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)
        ) {
            return response()->json(['message' => 'Invalid or expired coupon.'], 422);
        }

        $discount = $coupon->type === 'percentage'
            ? $data['subtotal'] * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        return response()->json([
            'code' => $coupon->code,
            'discount' => round(min($discount, $data['subtotal']), 2),
        ]);
    }
}
