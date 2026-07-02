"use client";

import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import type { Paginated, Product } from "@/lib/types";

export function ProductsBrowser() {
  const searchParams = useSearchParams();
  const search = searchParams.get("search") || "";
  const [products, setProducts] = useState<Product[] | null>(null);
  const [term, setTerm] = useState(search);

  useEffect(() => {
    setProducts(null);
    const query = search ? `?search=${encodeURIComponent(search)}` : "";
    apiFetch<Paginated<Product>>(`/products${query}`)
      .then((res) => setProducts(res.data))
      .catch(() => setProducts([]));
  }, [search]);

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-7xl">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <h1 className="text-3xl font-bold tracking-tight">Browse Products</h1>
          <form
            onSubmit={(e) => {
              e.preventDefault();
              const url = new URL(window.location.href);
              if (term.trim()) {
                url.searchParams.set("search", term.trim());
              } else {
                url.searchParams.delete("search");
              }
              window.history.pushState({}, "", url);
              window.dispatchEvent(new PopStateEvent("popstate"));
            }}
            className="glass flex w-full max-w-sm items-center gap-2 rounded-xl p-1.5 sm:w-auto"
          >
            <input
              value={term}
              onChange={(e) => setTerm(e.target.value)}
              placeholder="Search products..."
              className="flex-1 bg-transparent px-3 py-1.5 text-sm outline-none"
            />
            <button className="brand-gradient rounded-lg px-4 py-1.5 text-sm font-semibold text-white">
              Search
            </button>
          </form>
        </div>

        {products === null && (
          <div className="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {[...Array(6)].map((_, i) => (
              <div key={i} className="glass h-64 animate-pulse rounded-2xl" />
            ))}
          </div>
        )}

        {products?.length === 0 && (
          <div className="glass mt-8 rounded-2xl p-12 text-center text-foreground/50">
            No products found{search ? ` for "${search}"` : ""}.
          </div>
        )}

        {products && products.length > 0 && (
          <div className="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {products.map((product) => (
              <Link
                key={product.id}
                href={`/products/${product.slug}`}
                className="glass flex flex-col rounded-2xl p-5 shadow-sm transition-transform hover:-translate-y-1"
              >
                <div className="brand-gradient mb-4 h-36 w-full rounded-xl opacity-80" />
                <span className="text-xs font-medium uppercase tracking-wide text-brand-orange">
                  {product.category?.name}
                </span>
                <h3 className="mt-1 font-semibold">{product.title}</h3>
                <p className="mt-1 line-clamp-2 text-sm text-foreground/50">{product.summary}</p>
                <div className="mt-4 flex items-center justify-between">
                  <span className="text-lg font-bold">${product.base_price}</span>
                  <span className="text-xs text-foreground/50">by {product.vendor?.store_name}</span>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>
    </main>
  );
}
