import Link from "next/link";
import { FeaturedProducts } from "@/components/FeaturedProducts";
import { HeroSearch } from "@/components/HeroSearch";

const categories = [
  "Websites",
  "Laravel Projects",
  "UI Kits",
  "WordPress Themes",
  "Mobile Apps",
  "AI Agents",
  "SaaS Boilerplates",
  "Graphics & Icons",
];

const stats = [
  { label: "Digital Products", value: "42,000+" },
  { label: "Verified Vendors", value: "6,500+" },
  { label: "Happy Customers", value: "310,000+" },
  { label: "Countries Served", value: "150+" },
];

export default function Home() {
  return (
    <main className="flex-1">
      <section className="relative overflow-hidden px-6 pb-24 pt-20 sm:pt-28">
        <div
          aria-hidden
          className="pointer-events-none absolute inset-x-0 -top-40 -z-10 flex justify-center blur-3xl"
        >
          <div className="brand-gradient h-72 w-[36rem] rounded-full opacity-30" />
        </div>

        <div className="mx-auto flex max-w-4xl flex-col items-center text-center">
          <span className="glass rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-foreground/70">
            We Bring Your Ideas To Life
          </span>
          <h1 className="mt-6 text-4xl font-bold tracking-tight sm:text-6xl">
            All Digital Products.
            <br />
            <span className="brand-gradient-text">One Smart Marketplace.</span>
          </h1>
          <p className="mt-6 max-w-2xl text-lg text-foreground/60">
            Buy, sell, and manage websites, templates, UI kits, AI agents, source
            code, and more &mdash; built for creators, developers, designers,
            and businesses worldwide.
          </p>

          <HeroSearch />

          <div className="mt-6 flex flex-wrap items-center justify-center gap-2 text-xs text-foreground/50">
            <span>Popular:</span>
            {["Next.js Templates", "Laravel Scripts", "Figma UI Kits", "AI Prompts"].map(
              (term) => (
                <span
                  key={term}
                  className="rounded-full border border-black/10 px-3 py-1 dark:border-white/10"
                >
                  {term}
                </span>
              ),
            )}
          </div>
        </div>
      </section>

      <section id="categories" className="px-6 py-16">
        <div className="mx-auto max-w-7xl">
          <h2 className="text-2xl font-bold tracking-tight">Browse Categories</h2>
          <div className="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
            {categories.map((category) => (
              <Link
                key={category}
                href={`/products?search=${encodeURIComponent(category)}`}
                className="glass group cursor-pointer rounded-2xl p-6 text-center transition-transform hover:-translate-y-1"
              >
                <p className="font-semibold">{category}</p>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section id="products" className="px-6 py-16">
        <div className="mx-auto max-w-7xl">
          <div className="flex items-center justify-between">
            <h2 className="text-2xl font-bold tracking-tight">Featured Products</h2>
            <Link href="/products" className="text-sm font-semibold text-brand-blue">
              View all &rarr;
            </Link>
          </div>
          <FeaturedProducts />
        </div>
      </section>

      <section className="px-6 py-16">
        <div className="brand-gradient mx-auto grid max-w-7xl grid-cols-2 gap-8 rounded-3xl px-8 py-12 text-white sm:grid-cols-4">
          {stats.map((stat) => (
            <div key={stat.label} className="text-center">
              <p className="text-3xl font-bold">{stat.value}</p>
              <p className="mt-1 text-sm text-white/80">{stat.label}</p>
            </div>
          ))}
        </div>
      </section>

      <section id="vendors" className="px-6 py-16">
        <div className="glass mx-auto flex max-w-4xl flex-col items-center rounded-3xl p-12 text-center">
          <h2 className="text-2xl font-bold tracking-tight sm:text-3xl">
            Start Selling on OC TECH Marketplace
          </h2>
          <p className="mt-4 max-w-xl text-foreground/60">
            Join thousands of verified vendors reaching customers worldwide.
            Upload once, sell everywhere, and manage your store with
            enterprise-grade tools.
          </p>
          <Link
            href="/vendor/apply"
            className="brand-gradient mt-8 rounded-full px-8 py-3 text-sm font-semibold text-white transition-transform hover:scale-105"
          >
            Become a Vendor
          </Link>
        </div>
      </section>
    </main>
  );
}
