"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { useRequireAuth } from "@/lib/useRequireAuth";
import type { Order } from "@/lib/types";

export default function OrdersPage() {
  const { token, ready } = useRequireAuth();
  const [orders, setOrders] = useState<Order[] | null>(null);

  useEffect(() => {
    if (ready && token) {
      apiFetch<Order[]>("/orders", { token }).then(setOrders);
    }
  }, [ready, token]);

  if (!ready || orders === null) {
    return (
      <main className="flex-1 px-6 py-12">
        <div className="mx-auto max-w-4xl">
          <div className="glass h-64 animate-pulse rounded-3xl" />
        </div>
      </main>
    );
  }

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-4xl">
        <h1 className="text-3xl font-bold tracking-tight">Order History</h1>

        {orders.length === 0 ? (
          <p className="mt-8 text-foreground/50">You haven&apos;t placed any orders yet.</p>
        ) : (
          <div className="mt-8 flex flex-col gap-4">
            {orders.map((order) => (
              <Link
                key={order.id}
                href={`/account/orders/${order.id}`}
                className="glass flex items-center justify-between rounded-2xl p-5 transition-transform hover:-translate-y-0.5"
              >
                <div>
                  <p className="font-mono text-sm text-foreground/50">{order.order_number}</p>
                  <p className="mt-1 text-sm text-foreground/50">
                    {new Date(order.created_at).toLocaleDateString()} &middot; {order.items?.length ?? 0} item(s)
                  </p>
                </div>
                <div className="text-right">
                  <p className="font-bold">${order.grand_total}</p>
                  <span
                    className={
                      order.status === "completed"
                        ? "text-xs font-semibold text-emerald-600"
                        : "text-xs font-semibold text-amber-600"
                    }
                  >
                    {order.status}
                  </span>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>
    </main>
  );
}
