"use client";

import Link from "next/link";
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
  const flaggedOnly = searchParams.get("flagged") === "1";
  const [orders, setOrders] = useState<AdminOrder[] | null>(null);

  function refresh() {
    if (!token) return;
    const params = new URLSearchParams();
    if (status) params.set("status", status);
    if (flaggedOnly) params.set("flagged", "1");
    const query = params.toString() ? `?${params.toString()}` : "";
    apiFetch<Paginated<AdminOrder>>(`/admin/orders${query}`, { token }).then((res) => setOrders(res.data));
  }

  useEffect(refresh, [token, status, flaggedOnly]);

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
      <div className="flex items-center justify-end">
        <Link
          href={flaggedOnly ? "/admin/orders" : "/admin/orders?flagged=1"}
          className={`rounded-full px-4 py-1.5 text-xs font-medium ${
            flaggedOnly ? "brand-gradient text-white" : "border border-black/10 text-foreground/70 dark:border-white/10"
          }`}
        >
          ⚠ Flagged only
        </Link>
      </div>
      {orders.length === 0 && <p className="text-sm text-foreground/50">No orders found.</p>}
      {orders.map((order) => (
        <div key={order.id} className="glass flex items-center justify-between rounded-2xl p-5">
          <div>
            <p className="font-mono text-sm text-foreground/50">
              {order.order_number}
              {order.is_flagged && (
                <span
                  title={order.fraud_reasons?.join(" ")}
                  className="ml-2 rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-950/40 dark:text-red-400"
                >
                  ⚠ Flagged
                </span>
              )}
              {order.held_in_escrow && !order.escrow_released_at && (
                <span className="ml-2 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">
                  Escrow held
                </span>
              )}
            </p>
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
