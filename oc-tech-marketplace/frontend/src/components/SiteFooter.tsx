import Link from "next/link";

export function SiteFooter() {
  return (
    <footer className="border-t border-black/5 px-6 py-12 text-sm text-foreground/60 dark:border-white/10">
      <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 sm:flex-row">
        <span>&copy; {new Date().getFullYear()} OC TECH Marketplace. All rights reserved.</span>
        <div className="flex gap-6">
          <Link href="/products" className="hover:text-foreground">
            Browse
          </Link>
          <Link href="/vendor/apply" className="hover:text-foreground">
            Sell on OC TECH
          </Link>
          <Link href="/account/downloads" className="hover:text-foreground">
            My Downloads
          </Link>
        </div>
      </div>
    </footer>
  );
}
