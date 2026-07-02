"use client";

import { useEffect, useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import { useRequireAuth } from "@/lib/useRequireAuth";
import type { OrderItem } from "@/lib/types";

export default function DownloadsPage() {
  const { token, ready } = useRequireAuth();
  const [items, setItems] = useState<OrderItem[] | null>(null);
  const [busyId, setBusyId] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (ready && token) {
      apiFetch<OrderItem[]>("/downloads", { token }).then(setItems);
    }
  }, [ready, token]);

  async function download(productId: number) {
    if (!token) return;
    setError(null);
    setBusyId(productId);
    try {
      const res = await apiFetch<{ url: string }>(`/downloads/${productId}/request`, {
        method: "POST",
        token,
      });
      window.location.href = res.url;
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to generate a download link.");
    } finally {
      setBusyId(null);
    }
  }

  if (!ready || items === null) {
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
        <h1 className="text-3xl font-bold tracking-tight">My Downloads</h1>

        {error && <p className="mt-4 text-sm text-red-500">{error}</p>}

        {items.length === 0 ? (
          <p className="mt-8 text-foreground/50">You haven&apos;t purchased anything yet.</p>
        ) : (
          <div className="mt-8 flex flex-col gap-4">
            {items.map((item) => (
              <div key={item.id} className="glass flex items-center justify-between rounded-2xl p-5">
                <div>
                  <p className="font-semibold">{item.product?.title}</p>
                  <p className="text-sm text-foreground/50">
                    {item.license?.name}
                    {item.license?.download_limit
                      ? ` · ${item.downloads_used}/${item.license.download_limit} downloads used`
                      : ""}
                  </p>
                </div>
                <button
                  onClick={() => download(item.product_id)}
                  disabled={busyId === item.product_id}
                  className="brand-gradient rounded-lg px-5 py-2 text-sm font-semibold text-white disabled:opacity-60"
                >
                  {busyId === item.product_id ? "Preparing..." : "Download"}
                </button>
              </div>
            ))}
          </div>
        )}
      </div>
    </main>
  );
}
