"use client";

import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { effectiveProductPrice } from "@/lib/types";
import type { Product } from "@/lib/types";

export function CompareView() {
  const searchParams = useSearchParams();
  const ids = searchParams.get("ids") || "";
  const [products, setProducts] = useState<Product[] | null>(null);

  useEffect(() => {
    if (!ids) {
      setProducts([]);
      return;
    }
    apiFetch<Product[]>(`/products/compare?ids=${ids}`)
      .then(setProducts)
      .catch(() => setProducts([]));
  }, [ids]);

  if (products === null) {
    return (
      <main className="flex-1 px-6 py-12">
        <div className="mx-auto max-w-6xl">
          <div className="glass h-64 animate-pulse rounded-3xl" />
        </div>
      </main>
    );
  }

  if (products.length === 0) {
    return (
      <main className="flex-1 px-6 py-24 text-center">
        <p className="text-lg text-foreground/60">
          No products to compare yet. Add at least two from the product catalog.
        </p>
        <Link href="/products" className="brand-gradient mt-6 inline-block rounded-full px-6 py-3 text-sm font-semibold text-white">
          Browse Products
        </Link>
      </main>
    );
  }

  const rows: { label: string; render: (p: Product) => React.ReactNode }[] = [
    { label: "Vendor", render: (p) => p.vendor?.store_name ?? "—" },
    { label: "Category", render: (p) => p.category?.name ?? "—" },
    {
      label: "Price",
      render: (p) => (
        <span className="font-bold">${effectiveProductPrice(p).toFixed(2)}</span>
      ),
    },
    { label: "Rating", render: (p) => (parseFloat(p.average_rating) > 0 ? `${p.average_rating} ★` : "No ratings yet") },
    { label: "Sales", render: (p) => p.sales_count },
    {
      label: "License tiers",
      render: (p) => (
        <ul className="space-y-1">
          {p.licenses?.map((l) => (
            <li key={l.id}>
              {l.name}: ${l.price}
            </li>
          ))}
        </ul>
      ),
    },
  ];

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-6xl">
        <h1 className="text-3xl font-bold tracking-tight">Compare Products</h1>

        <div className="mt-8 overflow-x-auto">
          <table className="w-full min-w-[640px] border-separate border-spacing-y-2">
            <thead>
              <tr>
                <th className="w-32" />
                {products.map((p) => (
                  <th key={p.id} className="glass rounded-2xl p-4 text-left align-top">
                    <Link href={`/products/${p.slug}`} className="font-semibold hover:underline">
                      {p.title}
                    </Link>
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {rows.map((row) => (
                <tr key={row.label}>
                  <td className="px-2 py-3 text-sm font-medium text-foreground/50">{row.label}</td>
                  {products.map((p) => (
                    <td key={p.id} className="glass rounded-2xl p-4 text-sm">
                      {row.render(p)}
                    </td>
                  ))}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </main>
  );
}
