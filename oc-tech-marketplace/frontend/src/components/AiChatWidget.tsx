"use client";

import { useState } from "react";
import { apiFetch } from "@/lib/api";
import { useAuthHydrated, useAuthStore } from "@/store/auth";
import type { AiChatTurn } from "@/lib/types";

export function AiChatWidget() {
  const hydrated = useAuthHydrated();
  const token = useAuthStore((s) => s.token);
  const [open, setOpen] = useState(false);
  const [turns, setTurns] = useState<AiChatTurn[]>([]);
  const [message, setMessage] = useState("");
  const [sending, setSending] = useState(false);
  const [degraded, setDegraded] = useState(false);

  if (!hydrated || !token) return null;

  async function send(e: React.FormEvent) {
    e.preventDefault();
    const text = message.trim();
    if (!text || sending) return;

    const history = turns.slice(-10);
    setTurns((prev) => [...prev, { role: "user", content: text }]);
    setMessage("");
    setSending(true);

    try {
      const res = await apiFetch<{ reply: string; ai_powered: boolean }>("/support/ai-chat", {
        method: "POST",
        token,
        body: { message: text, history },
      });
      setTurns((prev) => [...prev, { role: "assistant", content: res.reply }]);
      setDegraded(!res.ai_powered);
    } catch {
      setTurns((prev) => [
        ...prev,
        { role: "assistant", content: "Something went wrong reaching the assistant. Please try again." },
      ]);
    } finally {
      setSending(false);
    }
  }

  return (
    <div className="fixed bottom-6 right-6 z-50">
      {open && (
        <div className="glass mb-3 flex h-96 w-80 flex-col overflow-hidden rounded-2xl shadow-2xl">
          <div className="flex items-center justify-between border-b border-black/10 px-4 py-3 dark:border-white/10">
            <span className="text-sm font-semibold">OC TECH Assistant</span>
            <button onClick={() => setOpen(false)} className="text-foreground/50 hover:text-foreground">
              ✕
            </button>
          </div>

          <div className="flex-1 space-y-3 overflow-y-auto p-4">
            {turns.length === 0 && (
              <p className="text-sm text-foreground/50">
                Ask me anything about buying, selling, or your account on OC TECH Marketplace.
              </p>
            )}
            {turns.map((turn, i) => (
              <div
                key={i}
                className={`max-w-[85%] rounded-xl px-3 py-2 text-sm ${
                  turn.role === "user"
                    ? "brand-gradient ml-auto text-white"
                    : "bg-black/5 dark:bg-white/10"
                }`}
              >
                {turn.content}
              </div>
            ))}
            {degraded && (
              <p className="text-xs text-amber-600">
                Our AI assistant is currently unavailable &mdash; open a support ticket for a human reply.
              </p>
            )}
          </div>

          <form onSubmit={send} className="flex items-center gap-2 border-t border-black/10 p-3 dark:border-white/10">
            <input
              value={message}
              onChange={(e) => setMessage(e.target.value)}
              placeholder="Type a message..."
              className="flex-1 rounded-lg border border-black/10 bg-transparent px-3 py-2 text-sm outline-none dark:border-white/10"
            />
            <button
              disabled={sending}
              className="brand-gradient rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-60"
            >
              Send
            </button>
          </form>
        </div>
      )}

      <button
        onClick={() => setOpen((o) => !o)}
        className="brand-gradient flex h-14 w-14 items-center justify-center rounded-full text-2xl text-white shadow-xl transition-transform hover:scale-105"
        aria-label="Open support chat"
      >
        {open ? "×" : "💬"}
      </button>
    </div>
  );
}
