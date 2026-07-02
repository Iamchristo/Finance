"use client";

import { useParams } from "next/navigation";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { useRequireAuth } from "@/lib/useRequireAuth";
import type { Order } from "@/lib/types";

export default function OrderDetailPage() {
  const { id } = useParams<{ id: string }>();
  const { token, ready } = useRequireAuth();
  const [order, setOrder] = useState<Order | null | undefined>(undefined);

  useEffect(() => {
    if (ready && token) {
      apiFetch<Order>(`/orders/${id}`, { token })
        .then(setOrder)
        .catch(() => setOrder(null));
    }
  }, [ready, token, id]);

  if (!ready || order === undefined) {
    return (
      <main className="flex-1 px-6 py-12">
        <div className="mx-auto max-w-3xl">
          <div className="glass h-96 animate-pulse rounded-3xl" />
        </div>
      </main>
    );
  }

  if (order === null) {
    return (
      <main className="flex-1 px-6 py-24 text-center">
        <p className="text-lg text-foreground/60">Order not found.</p>
      </main>
    );
  }

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-3xl">
        <div className="glass rounded-3xl p-8">
          <div className="flex items-center justify-between border-b border-black/10 pb-6 dark:border-white/10">
            <div>
              <h1 className="text-2xl font-bold tracking-tight">Invoice</h1>
              <p className="mt-1 font-mono text-sm text-foreground/50">{order.order_number}</p>
            </div>
            <span
              className={
                order.status === "completed"
                  ? "rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700"
                  : "rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700"
              }
            >
              {order.status}
            </span>
          </div>

          <div className="mt-6 flex justify-between text-sm text-foreground/60">
            <span>Date: {new Date(order.created_at).toLocaleString()}</span>
            <span>Payment: {order.payment_gateway ?? "—"}</span>
          </div>

          <div className="mt-6 flex flex-col gap-3">
            {order.items?.map((item) => (
              <div key={item.id} className="flex items-center justify-between border-b border-black/5 pb-3 dark:border-white/5">
                <div>
                  <p className="font-medium">{item.product?.title}</p>
                  <p className="text-sm text-foreground/50">{item.license?.name}</p>
                </div>
                <span className="font-semibold">${item.price}</span>
              </div>
            ))}
          </div>

          <div className="mt-6 flex flex-col gap-2 text-sm">
            <div className="flex justify-between">
              <span className="text-foreground/60">Subtotal</span>
              <span>${order.subtotal}</span>
            </div>
            {parseFloat(order.discount_total) > 0 && (
              <div className="flex justify-between text-emerald-600">
                <span>Discount</span>
                <span>-${order.discount_total}</span>
              </div>
            )}
            <div className="flex justify-between border-t border-black/10 pt-2 text-base font-bold dark:border-white/10">
              <span>Total</span>
              <span>${order.grand_total}</span>
            </div>
          </div>
        </div>
      </div>
    </main>
  );
}
