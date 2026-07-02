"use client";

import { useEffect, useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import type { WithdrawalRequest } from "@/lib/types";

export function VendorWithdrawals({ token, onChange }: { token: string; onChange?: () => void }) {
  const [withdrawals, setWithdrawals] = useState<WithdrawalRequest[] | null>(null);
  const [amount, setAmount] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  function refresh() {
    apiFetch<WithdrawalRequest[]>("/vendor/withdrawals", { token }).then(setWithdrawals);
  }

  useEffect(refresh, [token]);

  async function requestWithdrawal(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    setIsSubmitting(true);
    try {
      await apiFetch("/vendor/withdrawals", { method: "POST", token, body: { amount: parseFloat(amount) } });
      setAmount("");
      refresh();
      onChange?.();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to request withdrawal.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <div className="glass mt-6 rounded-2xl p-5">
      <h3 className="font-semibold">Withdrawals</h3>

      <form onSubmit={requestWithdrawal} className="mt-3 flex items-end gap-2">
        <div>
          <label className="text-xs text-foreground/50">Amount (USD)</label>
          <input
            required
            type="number"
            min="1"
            step="0.01"
            value={amount}
            onChange={(e) => setAmount(e.target.value)}
            className="mt-1 block w-32 rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
          />
        </div>
        <button
          disabled={isSubmitting}
          className="rounded-lg border border-black/10 px-4 py-1.5 text-sm font-medium hover:bg-foreground hover:text-background disabled:opacity-60 dark:border-white/10"
        >
          Request Payout
        </button>
      </form>
      {error && <p className="mt-2 text-xs text-red-500">{error}</p>}

      <div className="mt-4 flex flex-col gap-2">
        {withdrawals?.map((withdrawal) => (
          <div key={withdrawal.id} className="flex items-center justify-between text-sm">
            <span>${withdrawal.amount}</span>
            <span className="text-foreground/50">{new Date(withdrawal.created_at).toLocaleDateString()}</span>
            <span
              className={
                withdrawal.status === "approved"
                  ? "font-semibold text-emerald-600"
                  : withdrawal.status === "rejected"
                    ? "font-semibold text-red-500"
                    : "font-semibold text-amber-600"
              }
            >
              {withdrawal.status}
            </span>
          </div>
        ))}
        {withdrawals?.length === 0 && <p className="text-sm text-foreground/50">No withdrawal requests yet.</p>}
      </div>
    </div>
  );
}
