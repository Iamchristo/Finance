"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useRequireAdmin } from "@/lib/useRequireAdmin";

const links = [
  { href: "/admin", label: "Overview" },
  { href: "/admin/vendors", label: "Vendors" },
  { href: "/admin/withdrawals", label: "Withdrawals" },
  { href: "/admin/orders", label: "Orders" },
  { href: "/admin/reviews", label: "Reviews" },
  { href: "/admin/blog", label: "Blog" },
  { href: "/account/support", label: "Support" },
];

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const { ready } = useRequireAdmin();
  const pathname = usePathname();

  if (!ready) {
    return (
      <main className="flex-1 px-6 py-12">
        <div className="mx-auto max-w-6xl">
          <div className="glass h-64 animate-pulse rounded-3xl" />
        </div>
      </main>
    );
  }

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-6xl">
        <h1 className="text-3xl font-bold tracking-tight">Admin</h1>
        <nav className="mt-6 flex flex-wrap gap-2 border-b border-black/10 pb-4 dark:border-white/10">
          {links.map((link) => (
            <Link
              key={link.href}
              href={link.href}
              className={`rounded-full px-4 py-1.5 text-sm font-medium transition-colors ${
                pathname === link.href
                  ? "brand-gradient text-white"
                  : "border border-black/10 text-foreground/70 hover:text-foreground dark:border-white/10"
              }`}
            >
              {link.label}
            </Link>
          ))}
        </nav>
        <div className="mt-8">{children}</div>
      </div>
    </main>
  );
}
