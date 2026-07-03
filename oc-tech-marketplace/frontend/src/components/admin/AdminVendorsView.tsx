"use client";

import { useSearchParams } from "next/navigation";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { useAuthStore } from "@/store/auth";
import type { Paginated, Vendor } from "@/lib/types";

export function AdminVendorsView() {
  const token = useAuthStore((s) => s.token);
  const searchParams = useSearchParams();
  const status = searchParams.get("status") ?? "";
  const [vendors, setVendors] = useState<Vendor[] | null>(null);

  function refresh() {
    if (!token) return;
    const query = status ? `?status=${status}` : "";
    apiFetch<Paginated<Vendor>>(`/admin/vendors${query}`, { token }).then((res) => setVendors(res.data));
  }

  useEffect(refresh, [token, status]);

  async function approve(vendor: Vendor) {
    if (!token) return;
    await apiFetch(`/admin/vendors/${vendor.id}/approve`, { method: "POST", token });
    refresh();
  }

  async function reject(vendor: Vendor) {
    if (!token) return;
    await apiFetch(`/admin/vendors/${vendor.id}/reject`, { method: "POST", token });
    refresh();
  }

  if (vendors === null) {
    return <div className="glass h-64 animate-pulse rounded-3xl" />;
  }

  return (
    <div className="flex flex-col gap-4">
      {vendors.length === 0 && <p className="text-sm text-foreground/50">No vendors found.</p>}
      {vendors.map((vendor) => (
        <div key={vendor.id} className="glass flex items-center justify-between rounded-2xl p-5">
          <div>
            <p className="font-semibold">{vendor.store_name}</p>
            <p className="text-sm text-foreground/50">
              {vendor.user?.name} &middot; {vendor.user?.email}
            </p>
          </div>
          <div className="flex items-center gap-3">
            <span
              className={
                vendor.verification_status === "verified"
                  ? "text-xs font-semibold text-emerald-600"
                  : vendor.verification_status === "rejected"
                    ? "text-xs font-semibold text-red-500"
                    : "text-xs font-semibold text-amber-600"
              }
            >
              {vendor.verification_status}
            </span>
            {vendor.verification_status === "pending" && (
              <>
                <button
                  onClick={() => approve(vendor)}
                  className="brand-gradient rounded-lg px-3 py-1.5 text-xs font-semibold text-white"
                >
                  Approve
                </button>
                <button
                  onClick={() => reject(vendor)}
                  className="rounded-lg border border-black/10 px-3 py-1.5 text-xs font-medium hover:bg-foreground hover:text-background dark:border-white/10"
                >
                  Reject
                </button>
              </>
            )}
          </div>
        </div>
      ))}
    </div>
  );
}
