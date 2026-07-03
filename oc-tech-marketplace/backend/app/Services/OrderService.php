<?php

namespace App\Services;

use App\Models\Bundle;
use App\Models\Coupon;
use App\Models\FlashSale;
use App\Models\License;
use App\Models\Order;
use App\Models\ReferralReward;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Orders above this amount are held in escrow: the buyer's payment is
     * captured immediately, but the vendor's earnings aren't credited until
     * the buyer confirms receipt via releaseEscrow().
     */
    private const ESCROW_THRESHOLD = 200.0;

    public function __construct(private readonly WebhookDispatcher $webhooks) {}

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

                $price = $this->effectivePrice($license);
                $subtotal += $price;
                $lineItems[] = ['license' => $license, 'price' => $price];
            }

            $coupon = $this->resolveCoupon($couponCode);
            $discount = $this->calculateDiscount($coupon, $subtotal);
            $grandTotal = round($subtotal - $discount, 2);
            $fraudReasons = $this->assessFraudRisk($user, $grandTotal);

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'OCT-'.strtoupper(Str::random(10)),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount_total' => $discount,
                'tax_total' => 0,
                'grand_total' => $grandTotal,
                'coupon_id' => $coupon?->id,
                'held_in_escrow' => $grandTotal > self::ESCROW_THRESHOLD,
                'is_flagged' => ! empty($fraudReasons),
                'fraud_reasons' => $fraudReasons ?: null,
            ]);

            foreach ($lineItems as $lineItem) {
                $license = $lineItem['license'];
                $price = $lineItem['price'];
                $vendor = $license->product->vendor;
                $commissionRate = (float) $vendor->commission_rate;
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

    public function checkoutBundle(User $user, Bundle $bundle, string $paymentMethod): Order
    {
        return DB::transaction(function () use ($user, $bundle, $paymentMethod) {
            $products = $bundle->products()->with(['vendor', 'licenses'])->get();

            if ($products->isEmpty()) {
                throw ValidationException::withMessages(['bundle' => 'This bundle has no products.']);
            }

            foreach ($products as $product) {
                if ($product->licenses->isEmpty()) {
                    throw ValidationException::withMessages(['bundle' => 'One of the bundled products has no license to grant.']);
                }
            }

            $bundlePrice = (float) $bundle->bundle_price;
            $baseSum = (float) $products->sum('base_price');
            $count = $products->count();
            $fraudReasons = $this->assessFraudRisk($user, $bundlePrice);

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'OCT-'.strtoupper(Str::random(10)),
                'status' => 'pending',
                'subtotal' => $bundlePrice,
                'discount_total' => 0,
                'tax_total' => 0,
                'grand_total' => $bundlePrice,
                'bundle_id' => $bundle->id,
                'held_in_escrow' => $bundlePrice > self::ESCROW_THRESHOLD,
                'is_flagged' => ! empty($fraudReasons),
                'fraud_reasons' => $fraudReasons ?: null,
            ]);

            $allocated = 0;

            foreach ($products->values() as $index => $product) {
                $license = $product->licenses->sortBy('price')->first();
                $share = $baseSum > 0 ? (float) $product->base_price / $baseSum : 1 / $count;
                $price = $index === $count - 1
                    ? round($bundlePrice - $allocated, 2)
                    : round($bundlePrice * $share, 2);
                $allocated += $price;

                $vendor = $product->vendor;
                $commissionRate = (float) $vendor->commission_rate;
                $commissionAmount = round($price * ($commissionRate / 100), 2);
                $vendorEarnings = round($price - $commissionAmount, 2);

                $order->items()->create([
                    'product_id' => $product->id,
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

    private function effectivePrice(License $license): float
    {
        $price = (float) $license->price;

        $flashSale = FlashSale::where('product_id', $license->product_id)->active()->first();

        if ($flashSale) {
            $price = round($price * (1 - $flashSale->discount_percent / 100), 2);
        }

        return $price;
    }

    public function markOrderPaid(Order $order, string $gateway, ?string $reference = null): Order
    {
        if ($order->status === 'completed') {
            return $order;
        }

        $order = DB::transaction(function () use ($order, $gateway, $reference) {
            $order->update([
                'status' => 'completed',
                'payment_gateway' => $gateway,
                'payment_reference' => $reference,
                'paid_at' => now(),
            ]);

            foreach ($order->items()->with('product')->get() as $item) {
                $item->product->increment('sales_count');
            }

            if (! $order->held_in_escrow) {
                $this->creditVendorEarnings($order);
            }

            if ($order->coupon_id) {
                $order->coupon()->increment('used_count');
            }

            $this->maybeCreditReferralBonus($order);

            return $order->fresh(['items.product', 'items.vendor']);
        });

        $this->webhooks->dispatch('order.completed', $order->user, [
            'order_number' => $order->order_number,
            'grand_total' => (float) $order->grand_total,
            'held_in_escrow' => $order->held_in_escrow,
        ]);

        return $order;
    }

    /**
     * For orders held in escrow, the buyer calls this once they've confirmed
     * receipt to actually release the held funds into each vendor's wallet.
     */
    public function releaseEscrow(Order $order): Order
    {
        if (! $order->held_in_escrow || $order->escrow_released_at || $order->status !== 'completed') {
            throw ValidationException::withMessages(['order' => 'This order is not awaiting an escrow release.']);
        }

        return DB::transaction(function () use ($order) {
            $this->creditVendorEarnings($order);

            $order->update(['escrow_released_at' => now()]);

            return $order->fresh(['items.product', 'items.vendor']);
        });
    }

    private function creditVendorEarnings(Order $order): void
    {
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
        }
    }

    /**
     * @return array<int, string>
     */
    private function assessFraudRisk(User $user, float $grandTotal): array
    {
        $reasons = [];

        if ($user->created_at && $user->created_at->diffInMinutes(now()) < 5 && $grandTotal > 50) {
            $reasons[] = 'Account created less than 5 minutes ago placing an order over $50.';
        }

        $recentOrderCount = Order::where('user_id', $user->id)->where('created_at', '>=', now()->subHour())->count();

        if ($recentOrderCount >= 5) {
            $reasons[] = 'More than 5 orders placed by this account in the last hour.';
        }

        return $reasons;
    }

    private function maybeCreditReferralBonus(Order $order): void
    {
        $buyer = $order->user;

        if (! $buyer->referred_by_user_id) {
            return;
        }

        $isFirstCompletedOrder = Order::where('user_id', $buyer->id)->where('status', 'completed')->count() === 1;

        if (! $isFirstCompletedOrder) {
            return;
        }

        $referrer = $buyer->referredBy;

        if (! $referrer) {
            return;
        }

        $bonus = 5.00;

        $wallet = Wallet::firstOrCreate(['user_id' => $referrer->id]);
        $wallet->increment('balance', $bonus);
        $wallet->transactions()->create([
            'type' => 'credit',
            'amount' => $bonus,
            'balance_after' => $wallet->balance,
            'reference' => $order->order_number,
            'description' => "Referral bonus for {$buyer->name}'s first purchase",
        ]);

        ReferralReward::create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $buyer->id,
            'order_id' => $order->id,
            'amount' => $bonus,
        ]);
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
