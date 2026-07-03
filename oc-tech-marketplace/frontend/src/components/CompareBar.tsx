"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { useCompareStore } from "@/store/compare";

export function CompareBar() {
  const [mounted, setMounted] = useState(false);
  const { ids, clear } = useCompareStore();
  const router = useRouter();

  useEffect(() => setMounted(true), []);

  if (!mounted || ids.length < 2) return null;

  return (
    <div className="fixed bottom-6 left-1/2 z-40 flex -translate-x-1/2 items-center gap-4 rounded-full bg-foreground px-6 py-3 text-background shadow-2xl">
      <span className="text-sm font-medium">{ids.length} products selected</span>
      <button
        onClick={() => router.push(`/compare?ids=${ids.join(",")}`)}
        className="brand-gradient rounded-full px-4 py-1.5 text-sm font-semibold text-white"
      >
        Compare
      </button>
      <button onClick={clear} className="text-sm text-background/60 hover:text-background">
        Clear
      </button>
    </div>
  );
}
