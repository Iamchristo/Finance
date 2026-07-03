"use client";

import { useSearchParams } from "next/navigation";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { useAuthStore } from "@/store/auth";
import type { Order, Paginated } from "@/lib/types";

type AdminOrder = Order & { user?: { name: string; email: string } };

export function AdminOrdersView() {
  const token = useAuthStore((s) => s.token);
  const searchParams = useSearchParams();
  const status = searchParams.get("status") ?? "";
  const [orders, setOrders] = useState<AdminOrder[] | null>(null);

  function refresh() {
    if (!token) return;
    const query = status ? `?status=${status}` : "";
    apiFetch<Paginated<AdminOrder>>(`/admin/orders${query}`, { token }).then((res) => setOrders(res.data));
  }

  useEffect(refresh, [token, status]);

  async function confirmPayment(order: AdminOrder) {
    if (!token) return;
    await apiFetch(`/admin/orders/${order.id}/confirm-payment`, { method: "POST", token });
    refresh();
  }

  if (orders === null) {
    return <div className="glass h-64 animate-pulse rounded-3xl" />;
  }

  return (
    <div className="flex flex-col gap-4">
      {orders.length === 0 && <p className="text-sm text-foreground/50">No orders found.</p>}
      {orders.map((order) => (
        <div key={order.id} className="glass flex items-center justify-between rounded-2xl p-5">
          <div>
            <p className="font-mono text-sm text-foreground/50">{order.order_number}</p>
            <p className="mt-1 text-sm">
              {order.user?.name} &middot; ${order.grand_total} &middot; {order.payment_gateway}
            </p>
          </div>
          <div className="flex items-center gap-3">
            <span
              className={
                order.status === "completed"
                  ? "text-xs font-semibold text-emerald-600"
                  : "text-xs font-semibold text-amber-600"
              }
            >
              {order.status}
            </span>
            {order.status === "pending" && order.payment_gateway === "bank_transfer" && (
              <button
                onClick={() => confirmPayment(order)}
                className="brand-gradient rounded-lg px-3 py-1.5 text-xs font-semibold text-white"
              >
                Confirm Payment
              </button>
            )}
          </div>
        </div>
      ))}
    </div>
  );
}
