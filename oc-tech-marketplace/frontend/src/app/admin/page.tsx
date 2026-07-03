"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import { useAuthStore } from "@/store/auth";
import type { PlatformAnalytics } from "@/lib/types";

export default function AdminOverviewPage() {
  const token = useAuthStore((s) => s.token);
  const [analytics, setAnalytics] = useState<PlatformAnalytics | null>(null);

  useEffect(() => {
    if (token) apiFetch<PlatformAnalytics>("/admin/analytics", { token }).then(setAnalytics);
  }, [token]);

  if (!analytics) {
    return <div className="glass h-64 animate-pulse rounded-3xl" />;
  }

  const stats = [
    { label: "Total GMV", value: `$${analytics.total_gmv.toFixed(2)}` },
    { label: "Platform Revenue", value: `$${analytics.platform_revenue.toFixed(2)}` },
    { label: "Completed Orders", value: analytics.total_orders },
    { label: "Users", value: analytics.total_users },
    { label: "Vendors", value: analytics.total_vendors },
    { label: "Published Products", value: analytics.total_products },
  ];

  const pending = [
    { label: "Vendor approvals", value: analytics.pending_vendor_approvals, href: "/admin/vendors?status=pending" },
    { label: "Withdrawals", value: analytics.pending_withdrawals, href: "/admin/withdrawals?status=pending" },
    { label: "Pending orders", value: analytics.pending_orders, href: "/admin/orders?status=pending" },
  ];

  return (
    <div>
      <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        {stats.map((stat) => (
          <div key={stat.label} className="glass rounded-2xl p-4 text-center">
            <p className="text-xl font-bold">{stat.value}</p>
            <p className="mt-1 text-xs text-foreground/50">{stat.label}</p>
          </div>
        ))}
      </div>

      <div className="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        {pending.map((item) => (
          <Link
            key={item.label}
            href={item.href}
            className="glass flex items-center justify-between rounded-2xl p-4 transition-transform hover:-translate-y-0.5"
          >
            <span className="text-sm font-medium">{item.label}</span>
            <span className={item.value > 0 ? "font-bold text-amber-600" : "font-bold text-foreground/40"}>
              {item.value}
            </span>
          </Link>
        ))}
      </div>

      {analytics.top_vendors.length > 0 && (
        <div className="glass mt-6 rounded-2xl p-5">
          <h2 className="font-semibold">Top Vendors</h2>
          <div className="mt-3 flex flex-col gap-2">
            {analytics.top_vendors.map((vendor) => (
              <div key={vendor.id} className="flex items-center justify-between text-sm">
                <span>{vendor.store_name}</span>
                <span className="text-foreground/50">${vendor.total_earnings.toFixed(2)} earned</span>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
