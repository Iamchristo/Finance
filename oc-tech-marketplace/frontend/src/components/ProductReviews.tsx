"use client";

import { useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import { useAuthStore } from "@/store/auth";
import type { Review } from "@/lib/types";

export function ProductReviews({
  productId,
  reviews,
  onReviewAdded,
}: {
  productId: number;
  reviews: Review[];
  onReviewAdded: (review: Review) => void;
}) {
  const { user, token } = useAuthStore();
  const [rating, setRating] = useState(5);
  const [comment, setComment] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [reportingId, setReportingId] = useState<number | null>(null);
  const [reportReason, setReportReason] = useState("");
  const [reportedIds, setReportedIds] = useState<number[]>([]);

  const alreadyReviewed = user ? reviews.some((r) => r.user_id === user.id) : false;

  async function submitReport(reviewId: number) {
    if (!token || !reportReason.trim()) return;
    await apiFetch(`/reviews/${reviewId}/report`, { method: "POST", token, body: { reason: reportReason } });
    setReportedIds((prev) => [...prev, reviewId]);
    setReportingId(null);
    setReportReason("");
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    if (!token) return;
    setError(null);
    setIsSubmitting(true);
    try {
      const review = await apiFetch<Review>(`/products/${productId}/reviews`, {
        method: "POST",
        token,
        body: { rating, comment: comment || undefined },
      });
      onReviewAdded(review);
      setComment("");
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to submit your review.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <div className="mt-12">
      <h2 className="text-xl font-bold tracking-tight">Reviews</h2>

      <div className="mt-4 flex flex-col gap-4">
        {reviews.length === 0 && <p className="text-sm text-foreground/50">No reviews yet.</p>}
        {reviews.map((review) => (
          <div key={review.id} className="glass rounded-2xl p-5">
            <div className="flex items-center justify-between">
              <span className="font-semibold">{review.user?.name}</span>
              <span className="text-brand-orange">{"★".repeat(review.rating)}{"☆".repeat(5 - review.rating)}</span>
            </div>
            {review.is_verified_purchase && (
              <span className="text-xs font-medium text-emerald-600">Verified Purchase</span>
            )}
            {review.comment && <p className="mt-2 text-sm text-foreground/70">{review.comment}</p>}
            {review.vendor_reply && (
              <div className="mt-3 rounded-xl bg-black/5 p-3 text-sm dark:bg-white/5">
                <span className="font-semibold">Vendor reply: </span>
                {review.vendor_reply}
              </div>
            )}

            {token && (
              <div className="mt-3">
                {reportedIds.includes(review.id) ? (
                  <span className="text-xs text-foreground/40">Reported — thanks for letting us know.</span>
                ) : reportingId === review.id ? (
                  <div className="flex flex-wrap items-center gap-2">
                    <input
                      value={reportReason}
                      onChange={(e) => setReportReason(e.target.value)}
                      placeholder="Why are you reporting this review?"
                      className="min-w-[220px] flex-1 rounded-lg border border-black/10 bg-transparent px-2 py-1 text-xs outline-none dark:border-white/10"
                    />
                    <button
                      onClick={() => submitReport(review.id)}
                      className="text-xs font-semibold text-red-500"
                    >
                      Submit
                    </button>
                    <button onClick={() => setReportingId(null)} className="text-xs text-foreground/50">
                      Cancel
                    </button>
                  </div>
                ) : (
                  <button
                    onClick={() => setReportingId(review.id)}
                    className="text-xs text-foreground/40 hover:text-red-500"
                  >
                    Report
                  </button>
                )}
              </div>
            )}
          </div>
        ))}
      </div>

      {token && !alreadyReviewed && (
        <form onSubmit={submit} className="glass mt-6 rounded-2xl p-5">
          <h3 className="font-semibold">Write a review</h3>
          <div className="mt-3 flex gap-1">
            {[1, 2, 3, 4, 5].map((n) => (
              <button
                type="button"
                key={n}
                onClick={() => setRating(n)}
                className={n <= rating ? "text-2xl text-brand-orange" : "text-2xl text-foreground/30"}
              >
                ★
              </button>
            ))}
          </div>
          <textarea
            value={comment}
            onChange={(e) => setComment(e.target.value)}
            placeholder="Share your experience with this product..."
            rows={3}
            className="mt-3 w-full rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none dark:border-white/10"
          />
          {error && <p className="mt-2 text-sm text-red-500">{error}</p>}
          <button
            type="submit"
            disabled={isSubmitting}
            className="brand-gradient mt-3 rounded-xl px-5 py-2 text-sm font-semibold text-white disabled:opacity-60"
          >
            {isSubmitting ? "Submitting..." : "Submit Review"}
          </button>
        </form>
      )}
    </div>
  );
}
