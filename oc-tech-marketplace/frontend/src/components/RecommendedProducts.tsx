"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { effectiveProductPrice } from "@/lib/types";
import type { Product } from "@/lib/types";

export function RecommendedProducts({ slug }: { slug: string }) {
  const [products, setProducts] = useState<Product[] | null>(null);

  useEffect(() => {
    setProducts(null);
    apiFetch<Product[]>(`/products/${slug}/recommendations`)
      .then(setProducts)
      .catch(() => setProducts([]));
  }, [slug]);

  if (products !== null && products.length === 0) return null;

  return (
    <div className="mt-12">
      <h2 className="text-xl font-semibold">You might also like</h2>
      <div className="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
        {products === null &&
          [...Array(4)].map((_, i) => <div key={i} className="glass h-40 animate-pulse rounded-2xl" />)}
        {products?.map((product) => (
          <Link
            key={product.id}
            href={`/products/${product.slug}`}
            className="glass flex flex-col rounded-2xl p-4 transition-transform hover:-translate-y-1"
          >
            <div className="brand-gradient mb-3 h-20 w-full rounded-lg opacity-80" />
            <span className="line-clamp-2 text-sm font-medium">{product.title}</span>
            <span className="mt-1 text-sm font-bold">${effectiveProductPrice(product).toFixed(2)}</span>
          </Link>
        ))}
      </div>
    </div>
  );
}
