"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { ProductReviews } from "@/components/ProductReviews";
import { WishlistButton } from "@/components/WishlistButton";
import { CompareToggleButton } from "@/components/CompareToggleButton";
import { RecommendedProducts } from "@/components/RecommendedProducts";
import type { License, Product } from "@/lib/types";
import { useCartStore } from "@/store/cart";

export function ProductDetailView({ initialProduct }: { initialProduct: Product }) {
  const router = useRouter();
  const [product, setProduct] = useState<Product>(initialProduct);
  const [selectedLicense, setSelectedLicense] = useState<License | null>(product.licenses?.[0] ?? null);
  const [added, setAdded] = useState(false);
  const addItem = useCartStore((s) => s.addItem);

  const onSale = !!product.active_flash_sale;
  const flashDiscount = product.active_flash_sale?.discount_percent ?? 0;

  function handleAddToCart() {
    if (!selectedLicense) return;
    const licensePrice = parseFloat(selectedLicense.price);
    const price = onSale ? Math.round(licensePrice * (1 - flashDiscount / 100) * 100) / 100 : licensePrice;
    addItem({
      productId: product.id,
      productTitle: product.title,
      productSlug: product.slug,
      licenseId: selectedLicense.id,
      licenseName: selectedLicense.name,
      price,
    });
    setAdded(true);
  }

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto grid max-w-6xl grid-cols-1 gap-10 lg:grid-cols-3">
        <div className="lg:col-span-2">
          <div className="relative">
            <div className="brand-gradient h-72 w-full rounded-3xl opacity-80" />
            <div className="absolute right-4 top-4 flex gap-2">
              <WishlistButton productId={product.id} />
            </div>
            {onSale && (
              <span className="absolute left-4 top-4 rounded-full bg-red-500 px-3 py-1 text-sm font-bold text-white">
                Flash Sale &mdash; {flashDiscount}% off
              </span>
            )}
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
          <div className="mt-3">
            <CompareToggleButton productId={product.id} />
          </div>
          <p className="mt-6 text-foreground/70">{product.description || product.summary}</p>

          <ProductReviews
            productId={product.id}
            reviews={product.reviews ?? []}
            onReviewAdded={(review) =>
              setProduct((prev) => ({ ...prev, reviews: [...(prev.reviews ?? []), review] }))
            }
          />

          <RecommendedProducts slug={product.slug} />
        </div>

        <div className="glass h-fit rounded-3xl p-6">
          <h2 className="text-lg font-semibold">Choose a license</h2>
          <div className="mt-4 flex flex-col gap-3">
            {product.licenses?.map((license) => {
              const price = onSale
                ? Math.round(parseFloat(license.price) * (1 - flashDiscount / 100) * 100) / 100
                : parseFloat(license.price);
              return (
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
                    <span className="flex items-center gap-2">
                      {onSale && <span className="text-xs text-foreground/40 line-through">${license.price}</span>}
                      <span className="font-bold">${price.toFixed(2)}</span>
                    </span>
                  </div>
                  {license.download_limit && (
                    <p className="mt-1 text-xs text-foreground/50">
                      {license.download_limit} downloads included
                    </p>
                  )}
                </button>
              );
            })}
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
