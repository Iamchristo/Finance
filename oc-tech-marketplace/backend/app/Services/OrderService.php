<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\License;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * @param  array<int, array{product_id: int, license_id: int}>  $items
     */
    public function checkout(User $user, array $items, ?string $couponCode, string $paymentMethod): Order
    {
        return DB::transaction(function () use ($user, $items, $couponCode, $paymentMethod) {
            $licenses = License::with(['product.vendor'])
                ->whereIn('id', array_column($items, 'license_id'))
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $lineItems = [];

            foreach ($items as $item) {
                $license = $licenses->get($item['license_id']);

                if (! $license || $license->product_id !== (int) $item['product_id'] || $license->product->status !== 'published') {
                    throw ValidationException::withMessages(['items' => 'One or more items are no longer available.']);
                }

                $subtotal += (float) $license->price;
                $lineItems[] = $license;
            }

            $coupon = $this->resolveCoupon($couponCode);
            $discount = $this->calculateDiscount($coupon, $subtotal);
            $grandTotal = round($subtotal - $discount, 2);

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'OCT-'.strtoupper(Str::random(10)),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount_total' => $discount,
                'tax_total' => 0,
                'grand_total' => $grandTotal,
                'coupon_id' => $coupon?->id,
            ]);

            foreach ($lineItems as $license) {
                $vendor = $license->product->vendor;
                $commissionRate = (float) $vendor->commission_rate;
                $price = (float) $license->price;
                $commissionAmount = round($price * ($commissionRate / 100), 2);
                $vendorEarnings = round($price - $commissionAmount, 2);

                $order->items()->create([
                    'product_id' => $license->product_id,
                    'license_id' => $license->id,
                    'vendor_id' => $vendor->id,
                    'price' => $price,
                    'vendor_earnings' => $vendorEarnings,
                    'commission_amount' => $commissionAmount,
                ]);
            }

            match ($paymentMethod) {
                'wallet' => $this->payWithWallet($user, $order),
                'bank_transfer' => $order->update(['payment_gateway' => 'bank_transfer']),
                default => throw ValidationException::withMessages(['payment_method' => 'Unsupported payment method.']),
            };

            return $order->fresh(['items.product', 'items.vendor']);
        });
    }

    public function markOrderPaid(Order $order, string $gateway, ?string $reference = null): Order
    {
        if ($order->status === 'completed') {
            return $order;
        }

        return DB::transaction(function () use ($order, $gateway, $reference) {
            $order->update([
                'status' => 'completed',
                'payment_gateway' => $gateway,
                'payment_reference' => $reference,
                'paid_at' => now(),
            ]);

            foreach ($order->items()->with('product')->get() as $item) {
                $wallet = Wallet::firstOrCreate(['user_id' => $item->vendor->user_id]);
                $wallet->increment('balance', $item->vendor_earnings);
                $wallet->transactions()->create([
                    'type' => 'credit',
                    'amount' => $item->vendor_earnings,
                    'balance_after' => $wallet->balance,
                    'reference' => $order->order_number,
                    'description' => "Sale of {$item->product->title}",
                ]);

                $item->product->increment('sales_count');
            }

            if ($order->coupon_id) {
                $order->coupon()->increment('used_count');
            }

            return $order->fresh(['items.product', 'items.vendor']);
        });
    }

    private function payWithWallet(User $user, Order $order): void
    {
        $wallet = Wallet::query()->lockForUpdate()->firstOrCreate(['user_id' => $user->id]);

        if ((float) $wallet->balance < (float) $order->grand_total) {
            throw ValidationException::withMessages(['wallet' => 'Insufficient wallet balance.']);
        }

        $wallet->decrement('balance', $order->grand_total);
        $wallet->transactions()->create([
            'type' => 'debit',
            'amount' => $order->grand_total,
            'balance_after' => $wallet->balance,
            'reference' => $order->order_number,
            'description' => 'Order payment',
        ]);

        $this->markOrderPaid($order->fresh(), 'wallet');
    }

    private function resolveCoupon(?string $code): ?Coupon
    {
        if (! $code) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->where('is_active', true)->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['coupon' => 'Invalid coupon code.']);
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw ValidationException::withMessages(['coupon' => 'This coupon is not active yet.']);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw ValidationException::withMessages(['coupon' => 'This coupon has expired.']);
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['coupon' => 'This coupon has reached its usage limit.']);
        }

        return $coupon;
    }

    private function calculateDiscount(?Coupon $coupon, float $subtotal): float
    {
        if (! $coupon) {
            return 0;
        }

        $discount = $coupon->type === 'percentage'
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        return round(min($discount, $subtotal), 2);
    }
}
