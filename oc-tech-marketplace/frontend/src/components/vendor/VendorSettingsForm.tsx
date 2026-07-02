"use client";

import { useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import type { Vendor } from "@/lib/types";

export function VendorSettingsForm({
  token,
  vendor,
  onUpdated,
}: {
  token: string;
  vendor: Vendor;
  onUpdated: (vendor: Vendor) => void;
}) {
  const [storeName, setStoreName] = useState(vendor.store_name);
  const [description, setDescription] = useState(vendor.description ?? "");
  const [websiteUrl, setWebsiteUrl] = useState(vendor.website_url ?? "");
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function save(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    setSuccess(false);
    setIsSubmitting(true);
    try {
      const updated = await apiFetch<Vendor>("/vendor/me", {
        method: "PATCH",
        token,
        body: { store_name: storeName, description, website_url: websiteUrl || null },
      });
      onUpdated(updated);
      setSuccess(true);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to save settings.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <form onSubmit={save} className="glass mt-6 flex flex-col gap-3 rounded-2xl p-5">
      <h3 className="font-semibold">Store Settings</h3>
      <div>
        <label className="text-xs text-foreground/50">Store name</label>
        <input
          value={storeName}
          onChange={(e) => setStoreName(e.target.value)}
          className="mt-1 w-full rounded-lg border border-black/10 bg-transparent px-3 py-2 text-sm outline-none dark:border-white/10"
        />
      </div>
      <div>
        <label className="text-xs text-foreground/50">Description</label>
        <textarea
          value={description}
          onChange={(e) => setDescription(e.target.value)}
          rows={2}
          className="mt-1 w-full rounded-lg border border-black/10 bg-transparent px-3 py-2 text-sm outline-none dark:border-white/10"
        />
      </div>
      <div>
        <label className="text-xs text-foreground/50">Website URL</label>
        <input
          value={websiteUrl}
          onChange={(e) => setWebsiteUrl(e.target.value)}
          placeholder="https://..."
          className="mt-1 w-full rounded-lg border border-black/10 bg-transparent px-3 py-2 text-sm outline-none dark:border-white/10"
        />
      </div>
      {error && <p className="text-xs text-red-500">{error}</p>}
      {success && <p className="text-xs text-emerald-600">Saved.</p>}
      <button
        disabled={isSubmitting}
        className="brand-gradient self-start rounded-lg px-4 py-1.5 text-sm font-semibold text-white disabled:opacity-60"
      >
        {isSubmitting ? "Saving..." : "Save Settings"}
      </button>
    </form>
  );
}
