import Link from "next/link";
import type { Metadata } from "next";
import type { Bundle } from "@/lib/types";

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

export const metadata: Metadata = {
  title: "Bundles",
  description: "Save by buying multiple products together in a vendor bundle.",
};

async function fetchBundles(): Promise<Bundle[]> {
  const res = await fetch(`${API_URL}/bundles`, { cache: "no-store" });
  if (!res.ok) return [];
  return res.json();
}

export default async function BundlesPage() {
  const bundles = await fetchBundles();

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-6xl">
        <h1 className="text-3xl font-bold tracking-tight">Bundles</h1>
        <p className="mt-2 text-sm text-foreground/50">
          Multiple products from the same vendor, priced together for less.
        </p>

        {bundles.length === 0 && (
          <div className="glass mt-8 rounded-2xl p-12 text-center text-foreground/50">
            No bundles available right now.
          </div>
        )}

        <div className="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {bundles.map((bundle) => (
            <Link
              key={bundle.id}
              href={`/bundles/${bundle.slug}`}
              className="glass flex flex-col rounded-2xl p-5 transition-transform hover:-translate-y-1"
            >
              <div className="brand-gradient mb-4 h-28 w-full rounded-xl opacity-80" />
              <h3 className="font-semibold">{bundle.title}</h3>
              <p className="mt-1 text-sm text-foreground/50">
                {bundle.products?.length ?? 0} products &middot; by {bundle.vendor?.store_name}
              </p>
              <span className="mt-4 text-lg font-bold">${bundle.bundle_price}</span>
            </Link>
          ))}
        </div>
      </div>
    </main>
  );
}
