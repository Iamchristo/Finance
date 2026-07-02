"use client";

import { useEffect, useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import type { Coupon } from "@/lib/types";

export function VendorCoupons({ token }: { token: string }) {
  const [coupons, setCoupons] = useState<Coupon[] | null>(null);
  const [code, setCode] = useState("");
  const [value, setValue] = useState("10");
  const [type, setType] = useState<"percentage" | "fixed">("percentage");
  const [error, setError] = useState<string | null>(null);

  function refresh() {
    apiFetch<Coupon[]>("/vendor/coupons", { token }).then(setCoupons);
  }

  useEffect(refresh, [token]);

  async function createCoupon(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    try {
      await apiFetch("/vendor/coupons", {
        method: "POST",
        token,
        body: { code, type, value: parseFloat(value) },
      });
      setCode("");
      refresh();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to create coupon.");
    }
  }

  async function toggleActive(coupon: Coupon) {
    await apiFetch(`/vendor/coupons/${coupon.id}`, {
      method: "PATCH",
      token,
      body: { is_active: !coupon.is_active },
    });
    refresh();
  }

  return (
    <div className="glass mt-6 rounded-2xl p-5">
      <h3 className="font-semibold">Coupons</h3>

      <form onSubmit={createCoupon} className="mt-3 flex flex-wrap items-end gap-2">
        <div>
          <label className="text-xs text-foreground/50">Code</label>
          <input
            required
            value={code}
            onChange={(e) => setCode(e.target.value.toUpperCase())}
            className="mt-1 block w-32 rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
          />
        </div>
        <div>
          <label className="text-xs text-foreground/50">Type</label>
          <select
            value={type}
            onChange={(e) => setType(e.target.value as "percentage" | "fixed")}
            className="mt-1 block rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
          >
            <option value="percentage">% off</option>
            <option value="fixed">$ off</option>
          </select>
        </div>
        <div>
          <label className="text-xs text-foreground/50">Value</label>
          <input
            type="number"
            min="0"
            step="0.01"
            value={value}
            onChange={(e) => setValue(e.target.value)}
            className="mt-1 block w-24 rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
          />
        </div>
        <button className="rounded-lg border border-black/10 px-4 py-1.5 text-sm font-medium hover:bg-foreground hover:text-background dark:border-white/10">
          Add
        </button>
      </form>
      {error && <p className="mt-2 text-xs text-red-500">{error}</p>}

      <div className="mt-4 flex flex-col gap-2">
        {coupons?.map((coupon) => (
          <div key={coupon.id} className="flex items-center justify-between text-sm">
            <span className="font-mono">{coupon.code}</span>
            <span className="text-foreground/50">
              {coupon.type === "percentage" ? `${coupon.value}%` : `$${coupon.value}`} off &middot; used{" "}
              {coupon.used_count}x
            </span>
            <button onClick={() => toggleActive(coupon)} className="text-xs font-semibold text-brand-blue">
              {coupon.is_active ? "Deactivate" : "Activate"}
            </button>
          </div>
        ))}
        {coupons?.length === 0 && <p className="text-sm text-foreground/50">No coupons yet.</p>}
      </div>
    </div>
  );
}
