"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import { useRequireAuth } from "@/lib/useRequireAuth";
import { VendorAnalyticsPanel } from "@/components/vendor/VendorAnalyticsPanel";
import { VendorCoupons } from "@/components/vendor/VendorCoupons";
import { VendorSettingsForm } from "@/components/vendor/VendorSettingsForm";
import { VendorWithdrawals } from "@/components/vendor/VendorWithdrawals";
import { VendorFlashSales } from "@/components/vendor/VendorFlashSales";
import { VendorBundles } from "@/components/vendor/VendorBundles";
import { VendorTeam } from "@/components/vendor/VendorTeam";
import type { Category, Product, Vendor as VendorProfile } from "@/lib/types";

type LicenseDraft = { type: string; name: string; price: string; download_limit: string };

const emptyLicense: LicenseDraft = { type: "personal", name: "Personal License", price: "", download_limit: "" };

export default function VendorDashboardPage() {
  const { token, ready } = useRequireAuth();
  const [vendor, setVendor] = useState<VendorProfile | null | undefined>(undefined);
  const [products, setProducts] = useState<Product[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [showForm, setShowForm] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const [title, setTitle] = useState("");
  const [categoryId, setCategoryId] = useState<number | "">("");
  const [summary, setSummary] = useState("");
  const [basePrice, setBasePrice] = useState("");
  const [licenses, setLicenses] = useState<LicenseDraft[]>([{ ...emptyLicense }]);
  const [refreshKey, setRefreshKey] = useState(0);

  async function refresh(authToken: string) {
    try {
      const [vendorRes, productsRes] = await Promise.all([
        apiFetch<VendorProfile>("/vendor/me", { token: authToken }),
        apiFetch<Product[]>("/vendor/products", { token: authToken }),
      ]);
      setVendor(vendorRes);
      setProducts(productsRes);
      setRefreshKey((k) => k + 1);
    } catch {
      setVendor(null);
    }
  }

  useEffect(() => {
    if (ready && token) {
      refresh(token);
      apiFetch<Category[]>("/categories").then(setCategories);
    }
  }, [ready, token]);

  function updateLicense(index: number, field: keyof LicenseDraft, value: string) {
    setLicenses((prev) => prev.map((l, i) => (i === index ? { ...l, [field]: value } : l)));
  }

  async function createProduct(e: React.FormEvent) {
    e.preventDefault();
    if (!token) return;
    setError(null);
    setIsSubmitting(true);
    try {
      await apiFetch("/vendor/products", {
        method: "POST",
        token,
        body: {
          title,
          category_id: categoryId,
          summary,
          base_price: parseFloat(basePrice || "0"),
          licenses: licenses.map((l) => ({
            type: l.type,
            name: l.name,
            price: parseFloat(l.price || "0"),
            download_limit: l.download_limit ? parseInt(l.download_limit, 10) : null,
          })),
        },
      });
      setTitle("");
      setCategoryId("");
      setSummary("");
      setBasePrice("");
      setLicenses([{ ...emptyLicense }]);
      setShowForm(false);
      refresh(token);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to create product.");
    } finally {
      setIsSubmitting(false);
    }
  }

  async function uploadFile(productId: number, file: File) {
    if (!token) return;
    const form = new FormData();
    form.append("file", file);
    form.append("version", "1.0.0");
    try {
      await apiFetch(`/vendor/products/${productId}/files`, {
        method: "POST",
        token,
        body: form,
        isFormData: true,
      });
      refresh(token);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "File upload failed.");
    }
  }

  async function publish(productId: number) {
    if (!token) return;
    setError(null);
    try {
      await apiFetch(`/vendor/products/${productId}/publish`, { method: "POST", token });
      refresh(token);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to publish product.");
    }
  }

  if (!ready || vendor === undefined) {
    return (
      <main className="flex-1 px-6 py-12">
        <div className="mx-auto max-w-5xl">
          <div className="glass h-64 animate-pulse rounded-3xl" />
        </div>
      </main>
    );
  }

  if (vendor === null) {
    return (
      <main className="flex-1 px-6 py-24 text-center">
        <p className="text-lg text-foreground/60">You don&apos;t have a vendor profile yet.</p>
        <Link href="/vendor/apply" className="brand-gradient mt-6 inline-block rounded-full px-6 py-3 text-sm font-semibold text-white">
          Apply to Become a Vendor
        </Link>
      </main>
    );
  }

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-5xl">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">{vendor.store_name}</h1>
            <p className="mt-1 text-sm text-foreground/50">
              Status:{" "}
              <span
                className={
                  vendor.verification_status === "verified" ? "font-semibold text-emerald-600" : "font-semibold text-amber-600"
                }
              >
                {vendor.verification_status}
              </span>{" "}
              &middot; Wallet balance ${vendor.wallet_balance}
            </p>
          </div>
          <button
            onClick={() => setShowForm((s) => !s)}
            className="brand-gradient rounded-full px-5 py-2 text-sm font-semibold text-white"
          >
            {showForm ? "Cancel" : "New Product"}
          </button>
        </div>

        {vendor.verification_status !== "verified" && (
          <div className="glass mt-6 rounded-2xl p-4 text-sm text-amber-600">
            Your vendor profile is pending verification. You can prepare products now, but you
            won&apos;t be able to publish them until an administrator approves your account.
          </div>
        )}

        {token && (
          <>
            <VendorAnalyticsPanel key={refreshKey} token={token} />
            <VendorSettingsForm token={token} vendor={vendor} onUpdated={setVendor} />
            <VendorCoupons token={token} />
            <VendorFlashSales token={token} products={products} />
            <VendorBundles token={token} products={products} />
            <VendorTeam token={token} isOwner={vendor.is_owner ?? true} />
            <VendorWithdrawals token={token} onChange={() => refresh(token)} />
          </>
        )}

        {showForm && (
          <form onSubmit={createProduct} className="glass mt-6 flex flex-col gap-4 rounded-3xl p-6">
            <div>
              <label className="text-sm font-medium">Title</label>
              <input
                required
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                className="mt-1 w-full rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none dark:border-white/10"
              />
            </div>

            <div>
              <label className="text-sm font-medium">Category</label>
              <select
                required
                value={categoryId}
                onChange={(e) => setCategoryId(Number(e.target.value))}
                className="mt-1 w-full rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none dark:border-white/10"
              >
                <option value="">Select a category</option>
                {categories.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.name}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="text-sm font-medium">Summary</label>
              <textarea
                value={summary}
                onChange={(e) => setSummary(e.target.value)}
                rows={3}
                className="mt-1 w-full rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none dark:border-white/10"
              />
            </div>

            <div>
              <label className="text-sm font-medium">Base price (USD)</label>
              <input
                required
                type="number"
                min="0"
                step="0.01"
                value={basePrice}
                onChange={(e) => setBasePrice(e.target.value)}
                className="mt-1 w-full rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none dark:border-white/10"
              />
            </div>

            <div>
              <div className="flex items-center justify-between">
                <label className="text-sm font-medium">License tiers</label>
                <button
                  type="button"
                  onClick={() => setLicenses((prev) => [...prev, { ...emptyLicense, name: "", type: "commercial" }])}
                  className="text-xs font-semibold text-brand-blue"
                >
                  + Add tier
                </button>
              </div>
              <div className="mt-2 flex flex-col gap-3">
                {licenses.map((license, i) => (
                  <div key={i} className="grid grid-cols-2 gap-2 rounded-xl border border-black/10 p-3 dark:border-white/10">
                    <input
                      placeholder="Name"
                      required
                      value={license.name}
                      onChange={(e) => updateLicense(i, "name", e.target.value)}
                      className="rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
                    />
                    <input
                      placeholder="Price"
                      type="number"
                      min="0"
                      step="0.01"
                      required
                      value={license.price}
                      onChange={(e) => updateLicense(i, "price", e.target.value)}
                      className="rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
                    />
                    <input
                      placeholder="Type (personal/commercial)"
                      value={license.type}
                      onChange={(e) => updateLicense(i, "type", e.target.value)}
                      className="rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
                    />
                    <input
                      placeholder="Download limit (optional)"
                      type="number"
                      min="1"
                      value={license.download_limit}
                      onChange={(e) => updateLicense(i, "download_limit", e.target.value)}
                      className="rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
                    />
                  </div>
                ))}
              </div>
            </div>

            {error && <p className="text-sm text-red-500">{error}</p>}

            <button
              type="submit"
              disabled={isSubmitting}
              className="brand-gradient rounded-xl px-6 py-3 text-sm font-semibold text-white disabled:opacity-60"
            >
              {isSubmitting ? "Creating..." : "Create Product"}
            </button>
          </form>
        )}

        <div className="mt-10 flex flex-col gap-4">
          {products.map((product) => (
            <div key={product.id} className="glass rounded-2xl p-5">
              <div className="flex items-center justify-between">
                <div>
                  <p className="font-semibold">{product.title}</p>
                  <p className="text-sm text-foreground/50">
                    Status: {product.status} &middot; {product.licenses?.length || 0} license(s) &middot;{" "}
                    {product.files?.length || 0} file(s)
                  </p>
                </div>
                <div className="flex items-center gap-2">
                  <label className="cursor-pointer rounded-lg border border-black/10 px-3 py-1.5 text-xs font-medium hover:bg-foreground hover:text-background dark:border-white/10">
                    Upload File
                    <input
                      type="file"
                      className="hidden"
                      onChange={(e) => {
                        const file = e.target.files?.[0];
                        if (file) uploadFile(product.id, file);
                      }}
                    />
                  </label>
                  {product.status !== "published" && (
                    <button
                      onClick={() => publish(product.id)}
                      className="brand-gradient rounded-lg px-3 py-1.5 text-xs font-semibold text-white"
                    >
                      Publish
                    </button>
                  )}
                </div>
              </div>
            </div>
          ))}
          {products.length === 0 && !showForm && (
            <p className="text-sm text-foreground/50">You haven&apos;t created any products yet.</p>
          )}
        </div>
      </div>
    </main>
  );
}
