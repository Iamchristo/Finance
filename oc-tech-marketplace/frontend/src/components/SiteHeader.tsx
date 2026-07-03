"use client";

import Link from "next/link";
import { useAuthHydrated, useAuthStore } from "@/store/auth";
import { useCartStore } from "@/store/cart";

const ADMIN_ROLES = ["administrator", "super_administrator"];

export function SiteHeader() {
  const mounted = useAuthHydrated();
  const { user, logout } = useAuthStore();
  const cartCount = useCartStore((s) => s.items.length);

  return (
    <header className="sticky top-0 z-50 border-b border-black/5 bg-background/80 backdrop-blur-md dark:border-white/10">
      <div className="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4">
        <Link href="/" className="text-xl font-bold tracking-tight">
          OC <span className="brand-gradient-text">TECH</span> Marketplace
        </Link>
        <nav className="hidden items-center gap-8 text-sm font-medium text-foreground/70 md:flex">
          <Link href="/products" className="hover:text-foreground">
            Products
          </Link>
          <Link href="/bundles" className="hover:text-foreground">
            Bundles
          </Link>
          <Link href="/blog" className="hover:text-foreground">
            Blog
          </Link>
          <Link href="/vendor/apply" className="hover:text-foreground">
            Become a Vendor
          </Link>
        </nav>
        <div className="flex items-center gap-3">
          <Link href="/cart" className="relative text-sm font-medium text-foreground/70 hover:text-foreground">
            Cart
            {mounted && cartCount > 0 && (
              <span className="brand-gradient absolute -right-3 -top-2 flex h-4 w-4 items-center justify-center rounded-full text-[10px] font-bold text-white">
                {cartCount}
              </span>
            )}
          </Link>
          {mounted && user ? (
            <>
              {ADMIN_ROLES.includes(user.role) && (
                <Link href="/admin" className="hidden text-sm font-medium text-foreground/70 hover:text-foreground sm:block">
                  Admin
                </Link>
              )}
              <Link
                href={user.role === "vendor" ? "/vendor/dashboard" : "/account"}
                className="hidden text-sm font-medium text-foreground/70 hover:text-foreground sm:block"
              >
                {user.name}
              </Link>
              <button
                onClick={logout}
                className="brand-gradient rounded-full px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-brand-blue/20 transition-transform hover:scale-105"
              >
                Sign Out
              </button>
            </>
          ) : (
            <>
              <Link
                href="/login"
                className="hidden text-sm font-medium text-foreground/70 hover:text-foreground sm:block"
              >
                Sign In
              </Link>
              <Link
                href="/register"
                className="brand-gradient rounded-full px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-brand-blue/20 transition-transform hover:scale-105"
              >
                Get Started
              </Link>
            </>
          )}
        </div>
      </div>
    </header>
  );
}
