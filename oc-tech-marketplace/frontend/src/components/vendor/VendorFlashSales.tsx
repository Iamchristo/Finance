"use client";

import { useEffect, useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import type { FlashSale, Product } from "@/lib/types";

export function VendorFlashSales({ token, products }: { token: string; products: Product[] }) {
  const [flashSales, setFlashSales] = useState<FlashSale[] | null>(null);
  const [productId, setProductId] = useState<number | "">("");
  const [discount, setDiscount] = useState("20");
  const [startsAt, setStartsAt] = useState("");
  const [endsAt, setEndsAt] = useState("");
  const [error, setError] = useState<string | null>(null);

  const publishedProducts = products.filter((p) => p.status === "published");

  function refresh() {
    apiFetch<FlashSale[]>("/vendor/flash-sales", { token }).then(setFlashSales);
  }

  useEffect(refresh, [token]);

  async function createFlashSale(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    try {
      await apiFetch("/vendor/flash-sales", {
        method: "POST",
        token,
        body: {
          product_id: productId,
          discount_percent: parseInt(discount, 10),
          starts_at: startsAt,
          ends_at: endsAt,
        },
      });
      setProductId("");
      setDiscount("20");
      setStartsAt("");
      setEndsAt("");
      refresh();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to create flash sale.");
    }
  }

  async function cancel(flashSale: FlashSale) {
    await apiFetch(`/vendor/flash-sales/${flashSale.id}`, { method: "DELETE", token });
    refresh();
  }

  return (
    <div className="glass mt-6 rounded-2xl p-5">
      <h3 className="font-semibold">Flash Sales</h3>

      <form onSubmit={createFlashSale} className="mt-3 flex flex-wrap items-end gap-2">
        <div>
          <label className="text-xs text-foreground/50">Product</label>
          <select
            required
            value={productId}
            onChange={(e) => setProductId(Number(e.target.value))}
            className="mt-1 block w-48 rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
          >
            <option value="">Select product</option>
            {publishedProducts.map((p) => (
              <option key={p.id} value={p.id}>
                {p.title}
              </option>
            ))}
          </select>
        </div>
        <div>
          <label className="text-xs text-foreground/50">Discount %</label>
          <input
            type="number"
            min="1"
            max="90"
            value={discount}
            onChange={(e) => setDiscount(e.target.value)}
            className="mt-1 block w-20 rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
          />
        </div>
        <div>
          <label className="text-xs text-foreground/50">Starts</label>
          <input
            type="datetime-local"
            required
            value={startsAt}
            onChange={(e) => setStartsAt(e.target.value)}
            className="mt-1 block rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
          />
        </div>
        <div>
          <label className="text-xs text-foreground/50">Ends</label>
          <input
            type="datetime-local"
            required
            value={endsAt}
            onChange={(e) => setEndsAt(e.target.value)}
            className="mt-1 block rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
          />
        </div>
        <button className="rounded-lg border border-black/10 px-4 py-1.5 text-sm font-medium hover:bg-foreground hover:text-background dark:border-white/10">
          Launch
        </button>
      </form>
      {error && <p className="mt-2 text-xs text-red-500">{error}</p>}

      <div className="mt-4 flex flex-col gap-2">
        {flashSales?.map((sale) => (
          <div key={sale.id} className="flex items-center justify-between text-sm">
            <span className="font-medium">{sale.product?.title}</span>
            <span className="text-foreground/50">
              -{sale.discount_percent}% &middot; {new Date(sale.starts_at).toLocaleString()} &rarr;{" "}
              {new Date(sale.ends_at).toLocaleString()}
            </span>
            <button onClick={() => cancel(sale)} className="text-xs font-semibold text-red-500">
              Cancel
            </button>
          </div>
        ))}
        {flashSales?.length === 0 && <p className="text-sm text-foreground/50">No flash sales running.</p>}
      </div>
    </div>
  );
}
