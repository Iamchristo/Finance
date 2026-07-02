"use client";

import { useParams, useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { ProductReviews } from "@/components/ProductReviews";
import { WishlistButton } from "@/components/WishlistButton";
import type { License, Product } from "@/lib/types";
import { useCartStore } from "@/store/cart";

export default function ProductDetailPage() {
  const { slug } = useParams<{ slug: string }>();
  const router = useRouter();
  const [product, setProduct] = useState<Product | null | undefined>(undefined);
  const [selectedLicense, setSelectedLicense] = useState<License | null>(null);
  const [added, setAdded] = useState(false);
  const addItem = useCartStore((s) => s.addItem);

  useEffect(() => {
    apiFetch<Product>(`/products/${slug}`)
      .then((res) => {
        setProduct(res);
        setSelectedLicense(res.licenses?.[0] ?? null);
      })
      .catch(() => setProduct(null));
  }, [slug]);

  if (product === undefined) {
    return (
      <main className="flex-1 px-6 py-12">
        <div className="mx-auto max-w-5xl">
          <div className="glass h-96 animate-pulse rounded-3xl" />
        </div>
      </main>
    );
  }

  if (product === null) {
    return (
      <main className="flex-1 px-6 py-24 text-center">
        <p className="text-lg text-foreground/60">Product not found.</p>
      </main>
    );
  }

  function handleAddToCart() {
    if (!product || !selectedLicense) return;
    addItem({
      productId: product.id,
      productTitle: product.title,
      productSlug: product.slug,
      licenseId: selectedLicense.id,
      licenseName: selectedLicense.name,
      price: parseFloat(selectedLicense.price),
    });
    setAdded(true);
  }

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto grid max-w-6xl grid-cols-1 gap-10 lg:grid-cols-3">
        <div className="lg:col-span-2">
          <div className="relative">
            <div className="brand-gradient h-72 w-full rounded-3xl opacity-80" />
            <div className="absolute right-4 top-4">
              <WishlistButton productId={product.id} />
            </div>
          </div>
          <span className="mt-6 inline-block text-xs font-medium uppercase tracking-wide text-brand-orange">
            {product.category?.name}
          </span>
          <h1 className="mt-2 text-3xl font-bold tracking-tight">{product.title}</h1>
          <p className="mt-2 text-sm text-foreground/50">
            by {product.vendor?.store_name} &middot; {product.sales_count} sales
            {parseFloat(product.average_rating) > 0 && (
              <> &middot; {product.average_rating} ★</>
            )}
          </p>
          <p className="mt-6 text-foreground/70">{product.description || product.summary}</p>

          <ProductReviews
            productId={product.id}
            reviews={product.reviews ?? []}
            onReviewAdded={(review) =>
              setProduct((prev) => (prev ? { ...prev, reviews: [...(prev.reviews ?? []), review] } : prev))
            }
          />
        </div>

        <div className="glass h-fit rounded-3xl p-6">
          <h2 className="text-lg font-semibold">Choose a license</h2>
          <div className="mt-4 flex flex-col gap-3">
            {product.licenses?.map((license) => (
              <button
                key={license.id}
                onClick={() => {
                  setSelectedLicense(license);
                  setAdded(false);
                }}
                className={`rounded-xl border p-4 text-left transition-colors ${
                  selectedLicense?.id === license.id
                    ? "border-brand-blue bg-brand-blue/5"
                    : "border-black/10 dark:border-white/10"
                }`}
              >
                <div className="flex items-center justify-between">
                  <span className="font-medium">{license.name}</span>
                  <span className="font-bold">${license.price}</span>
                </div>
                {license.download_limit && (
                  <p className="mt-1 text-xs text-foreground/50">
                    {license.download_limit} downloads included
                  </p>
                )}
              </button>
            ))}
          </div>

          <button
            onClick={handleAddToCart}
            disabled={!selectedLicense}
            className="brand-gradient mt-6 w-full rounded-xl px-6 py-3 text-sm font-semibold text-white transition-transform hover:scale-[1.02] disabled:opacity-60"
          >
            {added ? "Added to Cart" : "Add to Cart"}
          </button>

          {added && (
            <button
              onClick={() => router.push("/cart")}
              className="mt-3 w-full rounded-xl border border-black/10 px-6 py-3 text-sm font-semibold hover:bg-foreground hover:text-background dark:border-white/10"
            >
              Go to Cart
            </button>
          )}
        </div>
      </div>
    </main>
  );
}
