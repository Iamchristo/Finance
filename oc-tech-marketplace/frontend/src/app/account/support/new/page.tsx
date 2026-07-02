"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import { useRequireAuth } from "@/lib/useRequireAuth";
import type { SupportTicket } from "@/lib/types";

export default function NewSupportTicketPage() {
  const { token, ready } = useRequireAuth();
  const router = useRouter();
  const [subject, setSubject] = useState("");
  const [message, setMessage] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  if (!ready) return null;

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    if (!token) return;
    setError(null);
    setIsSubmitting(true);
    try {
      const ticket = await apiFetch<SupportTicket>("/support/tickets", {
        method: "POST",
        token,
        body: { subject, message },
      });
      router.push(`/account/support/${ticket.id}`);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to create ticket.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main className="flex flex-1 items-center justify-center px-6 py-16">
      <div className="glass w-full max-w-lg rounded-3xl p-8">
        <h1 className="text-2xl font-bold tracking-tight">New Support Ticket</h1>

        <form onSubmit={submit} className="mt-8 flex flex-col gap-4">
          <div>
            <label className="text-sm font-medium">Subject</label>
            <input
              required
              value={subject}
              onChange={(e) => setSubject(e.target.value)}
              className="mt-1 w-full rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none dark:border-white/10"
            />
          </div>

          <div>
            <label className="text-sm font-medium">Message</label>
            <textarea
              required
              value={message}
              onChange={(e) => setMessage(e.target.value)}
              rows={5}
              className="mt-1 w-full rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none dark:border-white/10"
            />
          </div>

          {error && <p className="text-sm text-red-500">{error}</p>}

          <button
            type="submit"
            disabled={isSubmitting}
            className="brand-gradient mt-2 rounded-xl px-6 py-3 text-sm font-semibold text-white disabled:opacity-60"
          >
            {isSubmitting ? "Submitting..." : "Submit Ticket"}
          </button>
        </form>
      </div>
    </main>
  );
}
