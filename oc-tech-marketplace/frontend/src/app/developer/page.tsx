"use client";

import { useEffect, useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import { useRequireAuth } from "@/lib/useRequireAuth";
import type { ApiKeyInfo, WebhookDelivery, WebhookEndpoint, WebhookEvent } from "@/lib/types";

const AVAILABLE_EVENTS: WebhookEvent[] = ["order.completed", "product.published", "withdrawal.approved"];

export default function DeveloperPortalPage() {
  const { token, ready } = useRequireAuth();

  const [apiKeys, setApiKeys] = useState<ApiKeyInfo[] | null>(null);
  const [newKeyName, setNewKeyName] = useState("");
  const [plainTextKey, setPlainTextKey] = useState<string | null>(null);
  const [keyError, setKeyError] = useState<string | null>(null);

  const [webhooks, setWebhooks] = useState<WebhookEndpoint[] | null>(null);
  const [webhookUrl, setWebhookUrl] = useState("");
  const [webhookEvents, setWebhookEvents] = useState<WebhookEvent[]>([]);
  const [webhookError, setWebhookError] = useState<string | null>(null);
  const [deliveries, setDeliveries] = useState<Record<number, WebhookDelivery[]>>({});

  function refreshApiKeys() {
    if (token) apiFetch<ApiKeyInfo[]>("/developer/api-keys", { token }).then(setApiKeys);
  }

  function refreshWebhooks() {
    if (token) apiFetch<WebhookEndpoint[]>("/developer/webhooks", { token }).then(setWebhooks);
  }

  useEffect(refreshApiKeys, [token]);
  useEffect(refreshWebhooks, [token]);

  async function createApiKey(e: React.FormEvent) {
    e.preventDefault();
    if (!token) return;
    setKeyError(null);
    try {
      const res = await apiFetch<{ plain_text_key: string }>("/developer/api-keys", {
        method: "POST",
        token,
        body: { name: newKeyName },
      });
      setPlainTextKey(res.plain_text_key);
      setNewKeyName("");
      refreshApiKeys();
    } catch (err) {
      setKeyError(err instanceof ApiError ? err.message : "Unable to create API key.");
    }
  }

  async function revokeApiKey(id: number) {
    if (!token) return;
    await apiFetch(`/developer/api-keys/${id}`, { method: "DELETE", token });
    refreshApiKeys();
  }

  function toggleEvent(event: WebhookEvent) {
    setWebhookEvents((prev) => (prev.includes(event) ? prev.filter((e) => e !== event) : [...prev, event]));
  }

  async function createWebhook(e: React.FormEvent) {
    e.preventDefault();
    if (!token) return;
    setWebhookError(null);
    try {
      await apiFetch("/developer/webhooks", {
        method: "POST",
        token,
        body: { url: webhookUrl, events: webhookEvents },
      });
      setWebhookUrl("");
      setWebhookEvents([]);
      refreshWebhooks();
    } catch (err) {
      setWebhookError(err instanceof ApiError ? err.message : "Unable to create webhook.");
    }
  }

  async function removeWebhook(id: number) {
    if (!token) return;
    await apiFetch(`/developer/webhooks/${id}`, { method: "DELETE", token });
    refreshWebhooks();
  }

  async function loadDeliveries(id: number) {
    if (!token) return;
    const res = await apiFetch<WebhookDelivery[]>(`/developer/webhooks/${id}/deliveries`, { token });
    setDeliveries((prev) => ({ ...prev, [id]: res }));
  }

  if (!ready) return null;

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-4xl">
        <h1 className="text-3xl font-bold tracking-tight">Developer Portal</h1>
        <p className="mt-2 text-sm text-foreground/50">
          Build against the OC TECH Marketplace public API. Every request needs an{" "}
          <code className="rounded bg-black/5 px-1 dark:bg-white/10">X-API-Key</code> header, and is
          rate-limited to 60 requests/minute per key.
        </p>

        <div className="glass mt-6 rounded-2xl p-5 text-sm">
          <h2 className="font-semibold">Quick reference</h2>
          <ul className="mt-2 space-y-1 text-foreground/60">
            <li>
              <code>GET /api/v1/products</code> — list published products
            </li>
            <li>
              <code>GET /api/v1/products/{"{id}"}</code> — a single product with licenses
            </li>
            <li>
              <code>GET /api/v1/categories</code> — all categories
            </li>
            <li>
              <code>POST /api/v1/orders</code> — create an order on your behalf (
              <code>items</code>, <code>payment_method</code>)
            </li>
          </ul>
        </div>

        <div className="glass mt-6 rounded-2xl p-5">
          <h2 className="font-semibold">API Keys</h2>

          {plainTextKey && (
            <div className="mt-3 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400">
              <p className="font-semibold">Copy this key now — it won&apos;t be shown again:</p>
              <code className="mt-1 block break-all">{plainTextKey}</code>
            </div>
          )}

          <form onSubmit={createApiKey} className="mt-3 flex items-end gap-2">
            <div className="flex-1">
              <label className="text-xs text-foreground/50">Key name</label>
              <input
                required
                value={newKeyName}
                onChange={(e) => setNewKeyName(e.target.value)}
                placeholder="e.g. Inventory sync script"
                className="mt-1 block w-full rounded-lg border border-black/10 bg-transparent px-3 py-1.5 text-sm outline-none dark:border-white/10"
              />
            </div>
            <button className="rounded-lg border border-black/10 px-4 py-1.5 text-sm font-medium hover:bg-foreground hover:text-background dark:border-white/10">
              Generate
            </button>
          </form>
          {keyError && <p className="mt-2 text-xs text-red-500">{keyError}</p>}

          <div className="mt-4 flex flex-col gap-2">
            {apiKeys?.map((key) => (
              <div key={key.id} className="flex items-center justify-between text-sm">
                <span>
                  <span className="font-medium">{key.name}</span>{" "}
                  <span className="font-mono text-xs text-foreground/50">{key.prefix}.***</span>
                </span>
                <span className="text-xs text-foreground/50">
                  {key.revoked_at ? "revoked" : key.last_used_at ? `last used ${new Date(key.last_used_at).toLocaleDateString()}` : "never used"}
                </span>
                {!key.revoked_at && (
                  <button onClick={() => revokeApiKey(key.id)} className="text-xs font-semibold text-red-500">
                    Revoke
                  </button>
                )}
              </div>
            ))}
            {apiKeys?.length === 0 && <p className="text-sm text-foreground/50">No API keys yet.</p>}
          </div>
        </div>

        <div className="glass mt-6 rounded-2xl p-5">
          <h2 className="font-semibold">Webhooks</h2>

          <form onSubmit={createWebhook} className="mt-3 flex flex-col gap-3">
            <div className="flex items-end gap-2">
              <div className="flex-1">
                <label className="text-xs text-foreground/50">Endpoint URL</label>
                <input
                  required
                  type="url"
                  value={webhookUrl}
                  onChange={(e) => setWebhookUrl(e.target.value)}
                  placeholder="https://your-app.example.com/webhooks/octech"
                  className="mt-1 block w-full rounded-lg border border-black/10 bg-transparent px-3 py-1.5 text-sm outline-none dark:border-white/10"
                />
              </div>
              <button className="rounded-lg border border-black/10 px-4 py-1.5 text-sm font-medium hover:bg-foreground hover:text-background dark:border-white/10">
                Add
              </button>
            </div>
            <div className="flex flex-wrap gap-2">
              {AVAILABLE_EVENTS.map((event) => (
                <label
                  key={event}
                  className={`cursor-pointer rounded-lg border px-3 py-1.5 text-xs font-medium ${
                    webhookEvents.includes(event)
                      ? "border-brand-blue bg-brand-blue/10 text-brand-blue"
                      : "border-black/10 dark:border-white/10"
                  }`}
                >
                  <input
                    type="checkbox"
                    checked={webhookEvents.includes(event)}
                    onChange={() => toggleEvent(event)}
                    className="hidden"
                  />
                  {event}
                </label>
              ))}
            </div>
          </form>
          {webhookError && <p className="mt-2 text-xs text-red-500">{webhookError}</p>}

          <div className="mt-4 flex flex-col gap-3">
            {webhooks?.map((webhook) => (
              <div key={webhook.id} className="rounded-xl border border-black/10 p-3 text-sm dark:border-white/10">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="font-mono">{webhook.url}</p>
                    <p className="text-xs text-foreground/50">{webhook.events.join(", ")}</p>
                  </div>
                  <div className="flex gap-2">
                    <button onClick={() => loadDeliveries(webhook.id)} className="text-xs font-semibold text-brand-blue">
                      View deliveries
                    </button>
                    <button onClick={() => removeWebhook(webhook.id)} className="text-xs font-semibold text-red-500">
                      Remove
                    </button>
                  </div>
                </div>
                <p className="mt-2 text-xs text-foreground/50">
                  Signing secret: <code>{webhook.secret}</code> (verify the{" "}
                  <code>X-Webhook-Signature</code> header as HMAC-SHA256 of the request body)
                </p>
                {deliveries[webhook.id] && (
                  <div className="mt-2 space-y-1 border-t border-black/10 pt-2 dark:border-white/10">
                    {deliveries[webhook.id].length === 0 && (
                      <p className="text-xs text-foreground/50">No deliveries yet.</p>
                    )}
                    {deliveries[webhook.id].map((d) => (
                      <div key={d.id} className="flex justify-between text-xs">
                        <span>{d.event}</span>
                        <span className={d.response_status && d.response_status < 300 ? "text-emerald-600" : "text-red-500"}>
                          {d.response_status ?? d.error ?? "pending"}
                        </span>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            ))}
            {webhooks?.length === 0 && <p className="text-sm text-foreground/50">No webhooks registered.</p>}
          </div>
        </div>
      </div>
    </main>
  );
}
