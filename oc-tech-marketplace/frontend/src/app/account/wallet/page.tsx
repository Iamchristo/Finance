"use client";

import { useEffect, useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import { useRequireAuth } from "@/lib/useRequireAuth";
import type { Wallet } from "@/lib/types";

export default function WalletPage() {
  const { token, ready } = useRequireAuth();
  const [wallet, setWallet] = useState<Wallet | null>(null);
  const [amount, setAmount] = useState("50");
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function loadWallet(authToken: string) {
    const res = await apiFetch<Wallet>("/wallet", { token: authToken });
    setWallet(res);
  }

  useEffect(() => {
    if (ready && token) loadWallet(token);
  }, [ready, token]);

  async function topup() {
    if (!token) return;
    setError(null);
    setIsSubmitting(true);
    try {
      await apiFetch("/wallet/topup", { method: "POST", token, body: { amount: parseFloat(amount) } });
      await loadWallet(token);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Top-up failed.");
    } finally {
      setIsSubmitting(false);
    }
  }

  if (!ready || !wallet) {
    return (
      <main className="flex-1 px-6 py-12">
        <div className="mx-auto max-w-3xl">
          <div className="glass h-64 animate-pulse rounded-3xl" />
        </div>
      </main>
    );
  }

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-3xl">
        <h1 className="text-3xl font-bold tracking-tight">My Wallet</h1>

        <div className="glass mt-6 rounded-3xl p-8">
          <p className="text-sm text-foreground/60">Available balance</p>
          <p className="brand-gradient-text text-4xl font-bold">${wallet.balance}</p>

          <div className="mt-6 flex gap-2">
            <input
              type="number"
              min="1"
              value={amount}
              onChange={(e) => setAmount(e.target.value)}
              className="w-32 rounded-lg border border-black/10 bg-transparent px-3 py-2 text-sm outline-none dark:border-white/10"
            />
            <button
              onClick={topup}
              disabled={isSubmitting}
              className="brand-gradient rounded-lg px-5 py-2 text-sm font-semibold text-white disabled:opacity-60"
            >
              {isSubmitting ? "Adding..." : "Top Up via Bank Transfer"}
            </button>
          </div>
          {error && <p className="mt-2 text-sm text-red-500">{error}</p>}
          <p className="mt-2 text-xs text-foreground/40">
            Simulates a reconciled bank transfer deposit for this development environment.
          </p>
        </div>

        <h2 className="mt-10 text-lg font-semibold">Transaction History</h2>
        <div className="mt-4 flex flex-col gap-2">
          {wallet.transactions?.length ? (
            wallet.transactions.map((tx) => (
              <div key={tx.id} className="glass flex items-center justify-between rounded-xl p-4 text-sm">
                <div>
                  <p className="font-medium">{tx.description}</p>
                  <p className="text-xs text-foreground/50">{new Date(tx.created_at).toLocaleString()}</p>
                </div>
                <span className={tx.type === "credit" ? "font-semibold text-emerald-600" : "font-semibold text-red-500"}>
                  {tx.type === "credit" ? "+" : "-"}${tx.amount}
                </span>
              </div>
            ))
          ) : (
            <p className="text-sm text-foreground/50">No transactions yet.</p>
          )}
        </div>
      </div>
    </main>
  );
}
