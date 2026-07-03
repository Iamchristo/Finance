"use client";

import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { WishlistButton } from "@/components/WishlistButton";
import { effectiveProductPrice } from "@/lib/types";
import type { AiSearchResponse } from "@/lib/types";

export function AiSearchView() {
  const searchParams = useSearchParams();
  const initialQuery = searchParams.get("q") || "";
  const [term, setTerm] = useState(initialQuery);
  const [response, setResponse] = useState<AiSearchResponse | null>(null);
  const [loading, setLoading] = useState(false);

  function runSearch(query: string) {
    if (!query.trim()) return;
    setLoading(true);
    apiFetch<AiSearchResponse>("/search/ai", { method: "POST", body: { query } })
      .then(setResponse)
      .catch(() => setResponse({ ai_powered: false, summary: "Something went wrong.", results: [] }))
      .finally(() => setLoading(false));
  }

  useEffect(() => {
    if (initialQuery) runSearch(initialQuery);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [initialQuery]);

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-6xl">
        <h1 className="text-3xl font-bold tracking-tight">AI-Powered Search</h1>
        <p className="mt-2 text-sm text-foreground/50">
          Describe what you&apos;re looking for in plain language &mdash; e.g. &ldquo;a SaaS
          starter kit under $100&rdquo;.
        </p>

        <form
          onSubmit={(e) => {
            e.preventDefault();
            runSearch(term);
          }}
          className="glass mt-6 flex items-center gap-2 rounded-2xl p-2"
        >
          <input
            value={term}
            onChange={(e) => setTerm(e.target.value)}
            placeholder="What are you looking for?"
            className="flex-1 bg-transparent px-4 py-3 text-sm outline-none placeholder:text-foreground/40"
          />
          <button className="brand-gradient rounded-xl px-6 py-3 text-sm font-semibold text-white">
            {loading ? "Searching..." : "Search"}
          </button>
        </form>

        {response && (
          <>
            <div className="mt-6 flex items-center gap-2 text-sm text-foreground/60">
              <span>{response.summary}</span>
              {!response.ai_powered && (
                <span className="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-950/40 dark:text-amber-400">
                  AI unavailable &mdash; showing keyword results
                </span>
              )}
              {response.ai_powered && (
                <span className="brand-gradient rounded-full px-2 py-0.5 text-xs font-semibold text-white">
                  AI-powered
                </span>
              )}
            </div>

            {response.results.length === 0 && !loading && (
              <div className="glass mt-6 rounded-2xl p-12 text-center text-foreground/50">
                No products matched that search.
              </div>
            )}

            <div className="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {response.results.map((product) => {
                const price = effectiveProductPrice(product);
                const onSale = !!product.active_flash_sale;
                return (
                  <Link
                    key={product.id}
                    href={`/products/${product.slug}`}
                    className="glass relative flex flex-col rounded-2xl p-5 shadow-sm transition-transform hover:-translate-y-1"
                  >
                    <div className="absolute right-4 top-4 z-10">
                      <WishlistButton productId={product.id} />
                    </div>
                    <div className="brand-gradient mb-4 h-36 w-full rounded-xl opacity-80" />
                    <span className="text-xs font-medium uppercase tracking-wide text-brand-orange">
                      {product.category?.name}
                    </span>
                    <h3 className="mt-1 font-semibold">{product.title}</h3>
                    <p className="mt-1 line-clamp-2 text-sm text-foreground/50">{product.summary}</p>
                    <div className="mt-4 flex items-center justify-between">
                      <span className="flex items-center gap-2">
                        {onSale && (
                          <span className="text-xs text-foreground/40 line-through">${product.base_price}</span>
                        )}
                        <span className="text-lg font-bold">${price.toFixed(2)}</span>
                      </span>
                      <span className="text-xs text-foreground/50">by {product.vendor?.store_name}</span>
                    </div>
                  </Link>
                );
              })}
            </div>
          </>
        )}
      </div>
    </main>
  );
}
