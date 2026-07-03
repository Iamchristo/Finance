"use client";

import { useEffect, useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import type { Bundle, Product } from "@/lib/types";

export function VendorBundles({ token, products }: { token: string; products: Product[] }) {
  const [bundles, setBundles] = useState<Bundle[] | null>(null);
  const [title, setTitle] = useState("");
  const [price, setPrice] = useState("");
  const [selected, setSelected] = useState<number[]>([]);
  const [error, setError] = useState<string | null>(null);

  const publishedProducts = products.filter((p) => p.status === "published");

  function refresh() {
    apiFetch<Bundle[]>("/vendor/bundles", { token }).then(setBundles);
  }

  useEffect(refresh, [token]);

  function toggleProduct(id: number) {
    setSelected((prev) => (prev.includes(id) ? prev.filter((p) => p !== id) : [...prev, id]));
  }

  async function createBundle(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    try {
      await apiFetch("/vendor/bundles", {
        method: "POST",
        token,
        body: { title, bundle_price: parseFloat(price || "0"), product_ids: selected },
      });
      setTitle("");
      setPrice("");
      setSelected([]);
      refresh();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to create bundle.");
    }
  }

  async function deactivate(bundle: Bundle) {
    await apiFetch(`/vendor/bundles/${bundle.id}`, { method: "DELETE", token });
    refresh();
  }

  return (
    <div className="glass mt-6 rounded-2xl p-5">
      <h3 className="font-semibold">Bundles</h3>

      <form onSubmit={createBundle} className="mt-3 flex flex-col gap-3">
        <div className="flex flex-wrap items-end gap-2">
          <div>
            <label className="text-xs text-foreground/50">Bundle title</label>
            <input
              required
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              className="mt-1 block w-56 rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
            />
          </div>
          <div>
            <label className="text-xs text-foreground/50">Bundle price</label>
            <input
              type="number"
              min="0"
              step="0.01"
              required
              value={price}
              onChange={(e) => setPrice(e.target.value)}
              className="mt-1 block w-28 rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
            />
          </div>
          <button className="rounded-lg border border-black/10 px-4 py-1.5 text-sm font-medium hover:bg-foreground hover:text-background dark:border-white/10">
            Create
          </button>
        </div>

        <div>
          <label className="text-xs text-foreground/50">Products (pick at least 2 published products)</label>
          <div className="mt-1 flex flex-wrap gap-2">
            {publishedProducts.map((p) => (
              <label
                key={p.id}
                className={`cursor-pointer rounded-lg border px-3 py-1.5 text-xs font-medium ${
                  selected.includes(p.id)
                    ? "border-brand-blue bg-brand-blue/10 text-brand-blue"
                    : "border-black/10 dark:border-white/10"
                }`}
              >
                <input
                  type="checkbox"
                  checked={selected.includes(p.id)}
                  onChange={() => toggleProduct(p.id)}
                  className="hidden"
                />
                {p.title}
              </label>
            ))}
            {publishedProducts.length === 0 && (
              <p className="text-xs text-foreground/50">Publish at least two products to create a bundle.</p>
            )}
          </div>
        </div>
      </form>
      {error && <p className="mt-2 text-xs text-red-500">{error}</p>}

      <div className="mt-4 flex flex-col gap-2">
        {bundles?.map((bundle) => (
          <div key={bundle.id} className="flex items-center justify-between text-sm">
            <span className="font-medium">{bundle.title}</span>
            <span className="text-foreground/50">
              ${bundle.bundle_price} &middot; {bundle.products?.length ?? 0} products &middot;{" "}
              {bundle.is_active ? "active" : "inactive"}
            </span>
            {bundle.is_active && (
              <button onClick={() => deactivate(bundle)} className="text-xs font-semibold text-red-500">
                Deactivate
              </button>
            )}
          </div>
        ))}
        {bundles?.length === 0 && <p className="text-sm text-foreground/50">No bundles yet.</p>}
      </div>
    </div>
  );
}
