import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { BundlePurchasePanel } from "@/components/BundlePurchasePanel";
import type { Bundle } from "@/lib/types";

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

async function fetchBundle(slug: string): Promise<Bundle | null> {
  const res = await fetch(`${API_URL}/bundles/${slug}`, { cache: "no-store" });
  if (!res.ok) return null;
  return res.json();
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  const bundle = await fetchBundle(slug);

  if (!bundle) {
    return { title: "Bundle not found" };
  }

  return {
    title: bundle.title,
    description: bundle.description ?? undefined,
  };
}

export default async function BundleDetailPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const bundle = await fetchBundle(slug);

  if (!bundle) {
    notFound();
  }

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto grid max-w-5xl grid-cols-1 gap-10 lg:grid-cols-3">
        <div className="lg:col-span-2">
          <div className="brand-gradient h-56 w-full rounded-3xl opacity-80" />
          <span className="mt-6 inline-block text-xs font-medium uppercase tracking-wide text-brand-orange">
            Bundle by {bundle.vendor?.store_name}
          </span>
          <h1 className="mt-2 text-3xl font-bold tracking-tight">{bundle.title}</h1>
          {bundle.description && <p className="mt-4 text-foreground/70">{bundle.description}</p>}

          <h2 className="mt-8 text-lg font-semibold">What&apos;s included</h2>
          <div className="mt-4 flex flex-col gap-3">
            {bundle.products?.map((product) => (
              <div key={product.id} className="glass flex items-center justify-between rounded-2xl p-4">
                <div>
                  <p className="font-medium">{product.title}</p>
                  <p className="text-sm text-foreground/50">{product.category?.name}</p>
                </div>
                <span className="text-sm text-foreground/50 line-through">${product.base_price}</span>
              </div>
            ))}
          </div>
        </div>

        <BundlePurchasePanel bundle={bundle} />
      </div>
    </main>
  );
}
