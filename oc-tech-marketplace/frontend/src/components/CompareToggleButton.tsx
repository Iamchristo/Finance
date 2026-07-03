"use client";

import { useCompareStore } from "@/store/compare";

export function CompareToggleButton({ productId, className }: { productId: number; className?: string }) {
  const { ids, toggle } = useCompareStore();
  const active = ids.includes(productId);

  return (
    <button
      type="button"
      onClick={(e) => {
        e.preventDefault();
        e.stopPropagation();
        toggle(productId);
      }}
      className={
        className ??
        `rounded-full border px-3 py-1 text-xs font-medium transition-colors ${
          active
            ? "border-brand-blue bg-brand-blue/10 text-brand-blue"
            : "border-black/10 text-foreground/60 hover:bg-foreground hover:text-background dark:border-white/10"
        }`
      }
    >
      {active ? "✓ Comparing" : "+ Compare"}
    </button>
  );
}
