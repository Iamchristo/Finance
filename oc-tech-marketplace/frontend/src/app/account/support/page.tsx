"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { useRequireAuth } from "@/lib/useRequireAuth";
import type { SupportTicket } from "@/lib/types";

export default function SupportTicketsPage() {
  const { token, ready } = useRequireAuth();
  const [tickets, setTickets] = useState<SupportTicket[] | null>(null);

  useEffect(() => {
    if (ready && token) {
      apiFetch<SupportTicket[]>("/support/tickets", { token }).then(setTickets);
    }
  }, [ready, token]);

  if (!ready || tickets === null) {
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
        <div className="flex items-center justify-between">
          <h1 className="text-3xl font-bold tracking-tight">Support Tickets</h1>
          <Link
            href="/account/support/new"
            className="brand-gradient rounded-full px-5 py-2 text-sm font-semibold text-white"
          >
            New Ticket
          </Link>
        </div>

        {tickets.length === 0 ? (
          <p className="mt-8 text-foreground/50">You have no support tickets.</p>
        ) : (
          <div className="mt-8 flex flex-col gap-4">
            {tickets.map((ticket) => (
              <Link
                key={ticket.id}
                href={`/account/support/${ticket.id}`}
                className="glass flex items-center justify-between rounded-2xl p-5 transition-transform hover:-translate-y-0.5"
              >
                <div>
                  <p className="font-semibold">{ticket.subject}</p>
                  <p className="mt-1 text-sm text-foreground/50">
                    {new Date(ticket.created_at).toLocaleDateString()}
                  </p>
                </div>
                <span
                  className={
                    ticket.status === "closed"
                      ? "text-xs font-semibold text-foreground/40"
                      : ticket.status === "in_progress"
                        ? "text-xs font-semibold text-brand-blue"
                        : "text-xs font-semibold text-amber-600"
                  }
                >
                  {ticket.status}
                </span>
              </Link>
            ))}
          </div>
        )}
      </div>
    </main>
  );
}
