"use client";

import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { useRequireAuth } from "@/lib/useRequireAuth";
import type { ReferralSummary } from "@/lib/types";

export default function ReferralsPage() {
  const { token, ready } = useRequireAuth();
  const [summary, setSummary] = useState<ReferralSummary | null>(null);
  const [copied, setCopied] = useState(false);

  useEffect(() => {
    if (ready && token) {
      apiFetch<ReferralSummary>("/referrals/me", { token }).then(setSummary);
    }
  }, [ready, token]);

  if (!ready || !summary) {
    return (
      <main className="flex-1 px-6 py-12">
        <div className="mx-auto max-w-3xl">
          <div className="glass h-64 animate-pulse rounded-3xl" />
        </div>
      </main>
    );
  }

  const link = typeof window !== "undefined" ? `${window.location.origin}/register?ref=${summary.referral_code}` : "";

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-3xl">
        <h1 className="text-3xl font-bold tracking-tight">Refer &amp; Earn</h1>
        <p className="mt-2 text-sm text-foreground/50">
          Share your link. When someone you refer makes their first purchase, you earn a $5 wallet bonus.
        </p>

        <div className="glass mt-6 rounded-2xl p-6">
          <p className="text-sm font-medium">Your referral link</p>
          <div className="mt-2 flex items-center gap-2">
            <input
              readOnly
              value={link}
              className="flex-1 rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none dark:border-white/10"
            />
            <button
              onClick={() => {
                navigator.clipboard.writeText(link);
                setCopied(true);
                setTimeout(() => setCopied(false), 2000);
              }}
              className="brand-gradient rounded-xl px-4 py-2.5 text-sm font-semibold text-white"
            >
              {copied ? "Copied!" : "Copy"}
            </button>
          </div>
        </div>

        <div className="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-2">
          <div className="glass rounded-2xl p-6 text-center">
            <p className="text-3xl font-bold">{summary.referred_count}</p>
            <p className="mt-1 text-sm text-foreground/50">People referred</p>
          </div>
          <div className="glass rounded-2xl p-6 text-center">
            <p className="text-3xl font-bold">${summary.total_earned.toFixed(2)}</p>
            <p className="mt-1 text-sm text-foreground/50">Total earned</p>
          </div>
        </div>

        <div className="mt-6">
          <h2 className="text-lg font-semibold">Reward history</h2>
          <div className="mt-3 flex flex-col gap-2">
            {summary.rewards.length === 0 && (
              <p className="text-sm text-foreground/50">No rewards yet &mdash; share your link to get started.</p>
            )}
            {summary.rewards.map((reward) => (
              <div key={reward.id} className="glass flex items-center justify-between rounded-xl p-4 text-sm">
                <span>{reward.referred_user?.name ?? "A referred customer"}&apos;s first purchase</span>
                <span className="font-semibold text-emerald-600">+${reward.amount}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </main>
  );
}
