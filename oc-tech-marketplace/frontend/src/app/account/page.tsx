"use client";

import Link from "next/link";
import { useRequireAuth } from "@/lib/useRequireAuth";

const links = [
  { href: "/account/orders", label: "Orders", description: "View your order history and invoices" },
  { href: "/account/downloads", label: "Downloads", description: "Download products you've purchased" },
  { href: "/account/wishlist", label: "Wishlist", description: "Products you've saved for later" },
  { href: "/account/wallet", label: "Wallet", description: "Balance, top-ups, and transaction history" },
  { href: "/account/support", label: "Support", description: "Open tickets and message our team" },
  { href: "/account/referrals", label: "Refer & Earn", description: "Share your link and earn wallet bonuses" },
  { href: "/developer", label: "Developer Portal", description: "API keys, webhooks, and API docs" },
];

export default function AccountPage() {
  const { user, ready } = useRequireAuth();

  if (!ready) return null;

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-4xl">
        <h1 className="text-3xl font-bold tracking-tight">
          {user ? `Welcome back, ${user.name}` : "My Account"}
        </h1>

        <div className="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
          {links.map((link) => (
            <Link
              key={link.href}
              href={link.href}
              className="glass flex flex-col rounded-2xl p-6 transition-transform hover:-translate-y-1"
            >
              <span className="font-semibold">{link.label}</span>
              <span className="mt-1 text-sm text-foreground/50">{link.description}</span>
            </Link>
          ))}
        </div>
      </div>
    </main>
  );
}
