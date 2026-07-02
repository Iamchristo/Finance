"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import { cartSubtotal, useCartStore } from "@/store/cart";
import { useAuthStore } from "@/store/auth";
import type { Order } from "@/lib/types";

export default function CartPage() {
  const router = useRouter();
  const [mounted, setMounted] = useState(false);
  const { items, removeItem, clear } = useCartStore();
  const { token } = useAuthStore();
  const [couponCode, setCouponCode] = useState("");
  const [discount, setDiscount] = useState<number | null>(null);
  const [couponError, setCouponError] = useState<string | null>(null);
  const [paymentMethod, setPaymentMethod] = useState<"wallet" | "bank_transfer">("wallet");
  const [checkoutError, setCheckoutError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => setMounted(true), []);

  const subtotal = cartSubtotal(items);
  const grandTotal = Math.max(subtotal - (discount ?? 0), 0);

  async function applyCoupon() {
    setCouponError(null);
    setDiscount(null);
    try {
      const res = await apiFetch<{ discount: number }>("/coupons/validate", {
        method: "POST",
        body: { code: couponCode, subtotal },
      });
      setDiscount(res.discount);
    } catch (err) {
      setCouponError(err instanceof ApiError ? err.message : "Unable to validate coupon.");
    }
  }

  async function checkout() {
    if (!token) {
      router.push("/login");
      return;
    }
    setIsSubmitting(true);
    setCheckoutError(null);
    try {
      const order = await apiFetch<Order>("/checkout", {
        method: "POST",
        token,
        body: {
          items: items.map((i) => ({ product_id: i.productId, license_id: i.licenseId })),
          coupon_code: couponCode || undefined,
          payment_method: paymentMethod,
        },
      });
      clear();
      router.push(`/checkout/success?order=${order.order_number}&status=${order.status}`);
    } catch (err) {
      setCheckoutError(err instanceof ApiError ? err.message : "Checkout failed.");
    } finally {
      setIsSubmitting(false);
    }
  }

  if (!mounted) return null;

  if (items.length === 0) {
    return (
      <main className="flex-1 px-6 py-24 text-center">
        <p className="text-lg text-foreground/60">Your cart is empty.</p>
        <Link href="/products" className="brand-gradient mt-6 inline-block rounded-full px-6 py-3 text-sm font-semibold text-white">
          Browse Products
        </Link>
      </main>
    );
  }

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto grid max-w-5xl grid-cols-1 gap-10 lg:grid-cols-3">
        <div className="lg:col-span-2">
          <h1 className="text-3xl font-bold tracking-tight">Your Cart</h1>
          <div className="mt-6 flex flex-col gap-4">
            {items.map((item) => (
              <div key={item.licenseId} className="glass flex items-center justify-between rounded-2xl p-4">
                <div>
                  <p className="font-semibold">{item.productTitle}</p>
                  <p className="text-sm text-foreground/50">{item.licenseName}</p>
                </div>
                <div className="flex items-center gap-4">
                  <span className="font-bold">${item.price.toFixed(2)}</span>
                  <button
                    onClick={() => removeItem(item.licenseId)}
                    className="text-sm text-red-500 hover:underline"
                  >
                    Remove
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="glass h-fit rounded-3xl p-6">
          <h2 className="text-lg font-semibold">Order Summary</h2>

          <div className="mt-4 flex gap-2">
            <input
              value={couponCode}
              onChange={(e) => setCouponCode(e.target.value)}
              placeholder="Coupon code"
              className="flex-1 rounded-lg border border-black/10 bg-transparent px-3 py-2 text-sm outline-none dark:border-white/10"
            />
            <button
              onClick={applyCoupon}
              className="rounded-lg border border-black/10 px-4 py-2 text-sm font-medium hover:bg-foreground hover:text-background dark:border-white/10"
            >
              Apply
            </button>
          </div>
          {couponError && <p className="mt-2 text-xs text-red-500">{couponError}</p>}
          {discount !== null && <p className="mt-2 text-xs text-emerald-600">Coupon applied: -${discount.toFixed(2)}</p>}

          <div className="mt-6 flex flex-col gap-2 text-sm">
            <div className="flex justify-between">
              <span className="text-foreground/60">Subtotal</span>
              <span>${subtotal.toFixed(2)}</span>
            </div>
            {discount !== null && (
              <div className="flex justify-between text-emerald-600">
                <span>Discount</span>
                <span>-${discount.toFixed(2)}</span>
              </div>
            )}
            <div className="flex justify-between border-t border-black/10 pt-2 text-base font-bold dark:border-white/10">
              <span>Total</span>
              <span>${grandTotal.toFixed(2)}</span>
            </div>
          </div>

          <div className="mt-6">
            <label className="text-sm font-medium">Payment method</label>
            <div className="mt-2 flex flex-col gap-2">
              <label className="flex items-center gap-2 text-sm">
                <input
                  type="radio"
                  checked={paymentMethod === "wallet"}
                  onChange={() => setPaymentMethod("wallet")}
                />
                Wallet balance
              </label>
              <label className="flex items-center gap-2 text-sm">
                <input
                  type="radio"
                  checked={paymentMethod === "bank_transfer"}
                  onChange={() => setPaymentMethod("bank_transfer")}
                />
                Bank transfer (manual confirmation)
              </label>
            </div>
          </div>

          {checkoutError && <p className="mt-4 text-sm text-red-500">{checkoutError}</p>}

          <button
            onClick={checkout}
            disabled={isSubmitting}
            className="brand-gradient mt-6 w-full rounded-xl px-6 py-3 text-sm font-semibold text-white transition-transform hover:scale-[1.02] disabled:opacity-60"
          >
            {isSubmitting ? "Processing..." : "Complete Purchase"}
          </button>
        </div>
      </div>
    </main>
  );
}
