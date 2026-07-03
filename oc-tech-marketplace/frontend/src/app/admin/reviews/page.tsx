"use client";

import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { useAuthStore } from "@/store/auth";
import type { Review } from "@/lib/types";

export default function AdminReviewsPage() {
  const token = useAuthStore((s) => s.token);
  const [reviews, setReviews] = useState<Review[] | null>(null);

  function refresh() {
    if (token) apiFetch<Review[]>("/admin/reviews/reported", { token }).then(setReviews);
  }

  useEffect(refresh, [token]);

  async function dismiss(review: Review) {
    if (!token) return;
    await apiFetch(`/admin/reviews/${review.id}/dismiss-report`, { method: "POST", token });
    refresh();
  }

  async function hide(review: Review) {
    if (!token) return;
    await apiFetch(`/admin/reviews/${review.id}/hide`, { method: "POST", token });
    refresh();
  }

  if (reviews === null) {
    return <div className="glass h-64 animate-pulse rounded-3xl" />;
  }

  if (reviews.length === 0) {
    return <p className="text-sm text-foreground/50">No reported reviews. Nice and clean.</p>;
  }

  return (
    <div className="flex flex-col gap-4">
      {reviews.map((review) => (
        <div key={review.id} className="glass rounded-2xl p-5">
          <div className="flex items-center justify-between">
            <div>
              <p className="font-semibold">{review.product?.title}</p>
              <p className="text-sm text-foreground/50">
                {review.user?.name} &middot; {"★".repeat(review.rating)}
                {"☆".repeat(5 - review.rating)}
              </p>
            </div>
            <div className="flex gap-2">
              <button
                onClick={() => dismiss(review)}
                className="rounded-lg border border-black/10 px-3 py-1.5 text-xs font-medium hover:bg-foreground hover:text-background dark:border-white/10"
              >
                Dismiss Report
              </button>
              <button
                onClick={() => hide(review)}
                className="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-600"
              >
                Hide Review
              </button>
            </div>
          </div>
          {review.comment && <p className="mt-3 text-sm text-foreground/70">{review.comment}</p>}
          <div className="mt-3 rounded-xl bg-red-50 p-3 text-sm text-red-700 dark:bg-red-950/30 dark:text-red-400">
            <span className="font-semibold">Report reason: </span>
            {review.report_reason}
          </div>
        </div>
      ))}
    </div>
  );
}
