"use client";

import { useParams } from "next/navigation";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { useRequireAuth } from "@/lib/useRequireAuth";
import type { SupportTicket } from "@/lib/types";

export default function SupportTicketDetailPage() {
  const { id } = useParams<{ id: string }>();
  const { token, user, ready } = useRequireAuth();
  const [ticket, setTicket] = useState<SupportTicket | null | undefined>(undefined);
  const [reply, setReply] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function load(authToken: string) {
    const res = await apiFetch<SupportTicket>(`/support/tickets/${id}`, { token: authToken });
    setTicket(res);
  }

  useEffect(() => {
    if (ready && token) {
      load(token).catch(() => setTicket(null));
    }
  }, [ready, token, id]);

  async function submitReply(e: React.FormEvent) {
    e.preventDefault();
    if (!token || !reply.trim()) return;
    setIsSubmitting(true);
    try {
      await apiFetch(`/support/tickets/${id}/reply`, { method: "POST", token, body: { message: reply } });
      setReply("");
      await load(token);
    } finally {
      setIsSubmitting(false);
    }
  }

  async function closeTicket() {
    if (!token) return;
    await apiFetch(`/support/tickets/${id}/close`, { method: "POST", token });
    await load(token);
  }

  if (!ready || ticket === undefined) {
    return (
      <main className="flex-1 px-6 py-12">
        <div className="mx-auto max-w-3xl">
          <div className="glass h-96 animate-pulse rounded-3xl" />
        </div>
      </main>
    );
  }

  if (ticket === null) {
    return (
      <main className="flex-1 px-6 py-24 text-center">
        <p className="text-lg text-foreground/60">Ticket not found.</p>
      </main>
    );
  }

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-3xl">
        <div className="flex items-center justify-between">
          <h1 className="text-2xl font-bold tracking-tight">{ticket.subject}</h1>
          {ticket.status !== "closed" && (
            <button onClick={closeTicket} className="text-sm text-foreground/50 hover:underline">
              Close ticket
            </button>
          )}
        </div>
        <span className="text-xs font-semibold uppercase tracking-wide text-brand-orange">{ticket.status}</span>

        <div className="mt-6 flex flex-col gap-4">
          {ticket.messages?.map((message) => (
            <div
              key={message.id}
              className={`glass max-w-[80%] rounded-2xl p-4 ${
                message.user?.id === user?.id ? "ml-auto" : ""
              }`}
            >
              <p className="text-xs font-semibold text-foreground/50">{message.user?.name}</p>
              <p className="mt-1 text-sm">{message.message}</p>
            </div>
          ))}
        </div>

        {ticket.status !== "closed" && (
          <form onSubmit={submitReply} className="mt-6 flex gap-2">
            <input
              value={reply}
              onChange={(e) => setReply(e.target.value)}
              placeholder="Type a reply..."
              className="flex-1 rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none dark:border-white/10"
            />
            <button
              type="submit"
              disabled={isSubmitting}
              className="brand-gradient rounded-xl px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-60"
            >
              Send
            </button>
          </form>
        )}
      </div>
    </main>
  );
}
