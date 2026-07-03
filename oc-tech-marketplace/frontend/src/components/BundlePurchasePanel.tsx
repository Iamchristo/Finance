"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import { useAuthStore } from "@/store/auth";
import type { Bundle, Order } from "@/lib/types";

export function BundlePurchasePanel({ bundle }: { bundle: Bundle }) {
  const router = useRouter();
  const token = useAuthStore((s) => s.token);
  const [paymentMethod, setPaymentMethod] = useState<"wallet" | "bank_transfer">("wallet");
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function buy() {
    if (!token) {
      router.push("/login");
      return;
    }
    setIsSubmitting(true);
    setError(null);
    try {
      const order = await apiFetch<Order>("/checkout/bundle", {
        method: "POST",
        token,
        body: { bundle_id: bundle.id, payment_method: paymentMethod },
      });
      router.push(`/checkout/success?order=${order.order_number}&status=${order.status}`);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to complete purchase.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <div className="glass h-fit rounded-3xl p-6">
      <h2 className="text-lg font-semibold">Buy this bundle</h2>
      <p className="mt-2 text-3xl font-bold">${bundle.bundle_price}</p>

      <div className="mt-6">
        <label className="text-sm font-medium">Payment method</label>
        <div className="mt-2 flex flex-col gap-2">
          <label className="flex items-center gap-2 text-sm">
            <input type="radio" checked={paymentMethod === "wallet"} onChange={() => setPaymentMethod("wallet")} />
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

      {error && <p className="mt-4 text-sm text-red-500">{error}</p>}

      <button
        onClick={buy}
        disabled={isSubmitting}
        className="brand-gradient mt-6 w-full rounded-xl px-6 py-3 text-sm font-semibold text-white transition-transform hover:scale-[1.02] disabled:opacity-60"
      >
        {isSubmitting ? "Processing..." : "Buy Bundle"}
      </button>
    </div>
  );
}
