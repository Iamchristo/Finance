"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import type { Paginated, Product } from "@/lib/types";

export function FeaturedProducts() {
  const [products, setProducts] = useState<Product[] | null>(null);

  useEffect(() => {
    apiFetch<Paginated<Product>>("/products")
      .then((res) => setProducts(res.data.slice(0, 4)))
      .catch(() => setProducts([]));
  }, []);

  if (products === null) {
    return (
      <div className="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {[...Array(4)].map((_, i) => (
          <div key={i} className="glass h-64 animate-pulse rounded-2xl" />
        ))}
      </div>
    );
  }

  if (products.length === 0) {
    return (
      <div className="glass mt-8 rounded-2xl p-10 text-center text-sm text-foreground/50">
        No products published yet. Be the first vendor to list one.
      </div>
    );
  }

  return (
    <div className="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
      {products.map((product) => (
        <Link
          key={product.id}
          href={`/products/${product.slug}`}
          className="glass flex flex-col rounded-2xl p-5 shadow-sm transition-transform hover:-translate-y-1"
        >
          <div className="brand-gradient mb-4 h-32 w-full rounded-xl opacity-80" />
          <span className="text-xs font-medium uppercase tracking-wide text-brand-orange">
            {product.category?.name}
          </span>
          <h3 className="mt-1 font-semibold">{product.title}</h3>
          <p className="mt-1 text-sm text-foreground/50">by {product.vendor?.store_name}</p>
          <div className="mt-4 flex items-center justify-between">
            <span className="text-lg font-bold">${product.base_price}</span>
            <span className="rounded-full border border-black/10 px-4 py-1.5 text-sm font-medium dark:border-white/10">
              View
            </span>
          </div>
        </Link>
      ))}
    </div>
  );
}
