"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";

export function HeroSearch() {
  const router = useRouter();
  const [term, setTerm] = useState("");

  function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    const params = term.trim() ? `?search=${encodeURIComponent(term.trim())}` : "";
    router.push(`/products${params}`);
  }

  return (
    <form onSubmit={onSubmit} className="glass mt-10 flex w-full max-w-xl items-center gap-2 rounded-2xl p-2 shadow-xl">
      <input
        type="search"
        value={term}
        onChange={(e) => setTerm(e.target.value)}
        placeholder="Search for products, categories, or vendors..."
        className="flex-1 bg-transparent px-4 py-3 text-sm outline-none placeholder:text-foreground/40"
      />
      <button
        type="submit"
        className="brand-gradient rounded-xl px-6 py-3 text-sm font-semibold text-white transition-transform hover:scale-105"
      >
        Search
      </button>
    </form>
  );
}
