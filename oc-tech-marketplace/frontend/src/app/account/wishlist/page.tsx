"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { useRequireAuth } from "@/lib/useRequireAuth";
import type { WishlistEntry } from "@/lib/types";

export default function WishlistPage() {
  const { token, ready } = useRequireAuth();
  const [entries, setEntries] = useState<WishlistEntry[] | null>(null);

  useEffect(() => {
    if (ready && token) {
      apiFetch<WishlistEntry[]>("/wishlist", { token }).then(setEntries);
    }
  }, [ready, token]);

  async function remove(productId: number) {
    if (!token) return;
    await apiFetch(`/wishlist/${productId}`, { method: "DELETE", token });
    setEntries((prev) => prev?.filter((e) => e.product_id !== productId) ?? null);
  }

  if (!ready || entries === null) {
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
        <h1 className="text-3xl font-bold tracking-tight">My Wishlist</h1>

        {entries.length === 0 ? (
          <p className="mt-8 text-foreground/50">Your wishlist is empty.</p>
        ) : (
          <div className="mt-8 flex flex-col gap-4">
            {entries.map((entry) => (
              <div key={entry.id} className="glass flex items-center justify-between rounded-2xl p-5">
                <div>
                  <Link href={`/products/${entry.product?.slug}`} className="font-semibold hover:underline">
                    {entry.product?.title}
                  </Link>
                  <p className="text-sm text-foreground/50">${entry.product?.base_price}</p>
                </div>
                <button
                  onClick={() => remove(entry.product_id)}
                  className="text-sm text-red-500 hover:underline"
                >
                  Remove
                </button>
              </div>
            ))}
          </div>
        )}
      </div>
    </main>
  );
}
