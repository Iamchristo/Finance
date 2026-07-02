"use client";

import Link from "next/link";
import { useSearchParams } from "next/navigation";

export function CheckoutSuccess() {
  const searchParams = useSearchParams();
  const order = searchParams.get("order");
  const status = searchParams.get("status");
  const isPaid = status === "completed";

  return (
    <main className="flex flex-1 items-center justify-center px-6 py-24">
      <div className="glass max-w-md rounded-3xl p-10 text-center">
        <div className="brand-gradient mx-auto flex h-16 w-16 items-center justify-center rounded-full text-2xl text-white">
          {isPaid ? "✓" : "⏳"}
        </div>
        <h1 className="mt-6 text-2xl font-bold tracking-tight">
          {isPaid ? "Order Complete!" : "Order Received"}
        </h1>
        <p className="mt-2 text-sm text-foreground/60">
          {isPaid
            ? "Your purchase was successful. Your downloads are ready."
            : "We're waiting for your bank transfer to be confirmed before your downloads unlock."}
        </p>
        {order && <p className="mt-4 text-sm font-mono text-foreground/50">{order}</p>}

        <div className="mt-8 flex flex-col gap-3">
          <Link
            href="/account/downloads"
            className="brand-gradient rounded-xl px-6 py-3 text-sm font-semibold text-white"
          >
            Go to My Downloads
          </Link>
          <Link
            href="/products"
            className="rounded-xl border border-black/10 px-6 py-3 text-sm font-semibold hover:bg-foreground hover:text-background dark:border-white/10"
          >
            Continue Shopping
          </Link>
        </div>
      </div>
    </main>
  );
}
