"use client";

import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { useAuthStore } from "@/store/auth";
import type { WishlistEntry } from "@/lib/types";

export function WishlistButton({ productId }: { productId: number }) {
  const router = useRouter();
  const { token } = useAuthStore();
  const [inWishlist, setInWishlist] = useState(false);
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (!token) return;
    apiFetch<WishlistEntry[]>("/wishlist", { token })
      .then((entries) => setInWishlist(entries.some((e) => e.product_id === productId)))
      .catch(() => {});
  }, [token, productId]);

  async function toggle(e: React.MouseEvent) {
    e.preventDefault();
    e.stopPropagation();

    if (!token) {
      router.push("/login");
      return;
    }

    setBusy(true);
    try {
      if (inWishlist) {
        await apiFetch(`/wishlist/${productId}`, { method: "DELETE", token });
        setInWishlist(false);
      } else {
        await apiFetch(`/wishlist/${productId}`, { method: "POST", token });
        setInWishlist(true);
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <button
      onClick={toggle}
      disabled={busy}
      aria-label={inWishlist ? "Remove from wishlist" : "Add to wishlist"}
      className={`flex h-9 w-9 items-center justify-center rounded-full border transition-colors ${
        inWishlist
          ? "border-brand-orange bg-brand-orange/10 text-brand-orange"
          : "border-black/10 text-foreground/50 hover:text-brand-orange dark:border-white/10"
      }`}
    >
      {inWishlist ? "♥" : "♡"}
    </button>
  );
}
