"use client";

import { useSearchParams } from "next/navigation";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { useAuthStore } from "@/store/auth";
import type { WithdrawalRequest } from "@/lib/types";

type AdminWithdrawal = WithdrawalRequest & { vendor?: { store_name: string; user?: { name: string; email: string } } };

export function AdminWithdrawalsView() {
  const token = useAuthStore((s) => s.token);
  const searchParams = useSearchParams();
  const status = searchParams.get("status") ?? "";
  const [withdrawals, setWithdrawals] = useState<AdminWithdrawal[] | null>(null);

  function refresh() {
    if (!token) return;
    const query = status ? `?status=${status}` : "";
    apiFetch<AdminWithdrawal[]>(`/admin/withdrawals${query}`, { token }).then(setWithdrawals);
  }

  useEffect(refresh, [token, status]);

  async function approve(withdrawal: AdminWithdrawal) {
    if (!token) return;
    await apiFetch(`/admin/withdrawals/${withdrawal.id}/approve`, { method: "POST", token });
    refresh();
  }

  async function reject(withdrawal: AdminWithdrawal) {
    if (!token) return;
    await apiFetch(`/admin/withdrawals/${withdrawal.id}/reject`, { method: "POST", token });
    refresh();
  }

  if (withdrawals === null) {
    return <div className="glass h-64 animate-pulse rounded-3xl" />;
  }

  return (
    <div className="flex flex-col gap-4">
      {withdrawals.length === 0 && <p className="text-sm text-foreground/50">No withdrawal requests found.</p>}
      {withdrawals.map((withdrawal) => (
        <div key={withdrawal.id} className="glass flex items-center justify-between rounded-2xl p-5">
          <div>
            <p className="font-semibold">{withdrawal.vendor?.store_name}</p>
            <p className="text-sm text-foreground/50">
              ${withdrawal.amount} &middot; {new Date(withdrawal.created_at).toLocaleDateString()}
            </p>
          </div>
          <div className="flex items-center gap-3">
            <span
              className={
                withdrawal.status === "approved"
                  ? "text-xs font-semibold text-emerald-600"
                  : withdrawal.status === "rejected"
                    ? "text-xs font-semibold text-red-500"
                    : "text-xs font-semibold text-amber-600"
              }
            >
              {withdrawal.status}
            </span>
            {withdrawal.status === "pending" && (
              <>
                <button
                  onClick={() => approve(withdrawal)}
                  className="brand-gradient rounded-lg px-3 py-1.5 text-xs font-semibold text-white"
                >
                  Approve
                </button>
                <button
                  onClick={() => reject(withdrawal)}
                  className="rounded-lg border border-black/10 px-3 py-1.5 text-xs font-medium hover:bg-foreground hover:text-background dark:border-white/10"
                >
                  Reject
                </button>
              </>
            )}
          </div>
        </div>
      ))}
    </div>
  );
}
