"use client";

import { useEffect, useState } from "react";
import { apiFetch } from "@/lib/api";
import type { VendorAnalytics } from "@/lib/types";

export function VendorAnalyticsPanel({ token }: { token: string }) {
  const [analytics, setAnalytics] = useState<VendorAnalytics | null>(null);

  useEffect(() => {
    apiFetch<VendorAnalytics>("/vendor/analytics", { token }).then(setAnalytics);
  }, [token]);

  if (!analytics) {
    return <div className="glass mt-6 h-32 animate-pulse rounded-2xl" />;
  }

  const stats = [
    { label: "Total Revenue", value: `$${analytics.total_revenue.toFixed(2)}` },
    { label: "Total Sales", value: analytics.total_sales },
    { label: "Wallet Balance", value: `$${analytics.wallet_balance}` },
    { label: "Products", value: analytics.product_count },
  ];

  return (
    <div className="mt-6">
      <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
        {stats.map((stat) => (
          <div key={stat.label} className="glass rounded-2xl p-4 text-center">
            <p className="text-2xl font-bold">{stat.value}</p>
            <p className="mt-1 text-xs text-foreground/50">{stat.label}</p>
          </div>
        ))}
      </div>

      {analytics.top_products.length > 0 && (
        <div className="glass mt-4 rounded-2xl p-5">
          <h3 className="font-semibold">Top Products</h3>
          <div className="mt-3 flex flex-col gap-2">
            {analytics.top_products.map((product) => (
              <div key={product.id} className="flex items-center justify-between text-sm">
                <span>{product.title}</span>
                <span className="text-foreground/50">{product.sales_count} sales</span>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
