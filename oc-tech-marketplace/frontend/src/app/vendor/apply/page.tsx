"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import { useRequireAuth } from "@/lib/useRequireAuth";
import { useAuthStore } from "@/store/auth";

export default function VendorApplyPage() {
  const { token, user, ready } = useRequireAuth();
  const updateUser = useAuthStore((s) => s.updateUser);
  const router = useRouter();
  const [storeName, setStoreName] = useState("");
  const [description, setDescription] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  if (!ready) return null;

  if (user?.role === "vendor") {
    return (
      <main className="flex-1 px-6 py-24 text-center">
        <p className="text-lg text-foreground/60">You already have a vendor profile.</p>
        <button
          onClick={() => router.push("/vendor/dashboard")}
          className="brand-gradient mt-6 rounded-full px-6 py-3 text-sm font-semibold text-white"
        >
          Go to Vendor Dashboard
        </button>
      </main>
    );
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    if (!token || !user) return;
    setError(null);
    setIsSubmitting(true);
    try {
      await apiFetch("/vendor/apply", {
        method: "POST",
        token,
        body: { store_name: storeName, description },
      });
      updateUser({ ...user, role: "vendor" });
      router.push("/vendor/dashboard");
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to submit your application.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main className="flex flex-1 items-center justify-center px-6 py-16">
      <div className="glass w-full max-w-lg rounded-3xl p-8">
        <h1 className="text-2xl font-bold tracking-tight">Become a Vendor</h1>
        <p className="mt-1 text-sm text-foreground/60">
          Tell us about your store. An administrator will verify your account before you can
          publish products.
        </p>

        <form onSubmit={submit} className="mt-8 flex flex-col gap-4">
          <div>
            <label className="text-sm font-medium">Store name</label>
            <input
              required
              value={storeName}
              onChange={(e) => setStoreName(e.target.value)}
              className="mt-1 w-full rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none focus:border-brand-blue dark:border-white/10"
            />
          </div>

          <div>
            <label className="text-sm font-medium">Description</label>
            <textarea
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              rows={4}
              className="mt-1 w-full rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none focus:border-brand-blue dark:border-white/10"
            />
          </div>

          {error && <p className="text-sm text-red-500">{error}</p>}

          <button
            type="submit"
            disabled={isSubmitting}
            className="brand-gradient mt-2 rounded-xl px-6 py-3 text-sm font-semibold text-white transition-transform hover:scale-[1.02] disabled:opacity-60"
          >
            {isSubmitting ? "Submitting..." : "Submit Application"}
          </button>
        </form>
      </div>
    </main>
  );
}
