import Link from "next/link";

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

const featuredProducts = [
  {
    name: "NovaCommerce SaaS Starter",
    category: "SaaS Boilerplates",
    price: "$79",
    vendor: "Northline Studio",
  },
  {
    name: "Aurora Admin Dashboard UI Kit",
    category: "UI Kits",
    price: "$39",
    vendor: "Pixel Forge",
  },
  {
    name: "Laravel Booking Engine",
    category: "Laravel Projects",
    price: "$129",
    vendor: "CodeHarbor",
  },
  {
    name: "Orbit AI Chat Widget",
    category: "AI Agents",
    price: "$59",
    vendor: "Loopwave",
  },
];

const stats = [
  { label: "Digital Products", value: "42,000+" },
  { label: "Verified Vendors", value: "6,500+" },
  { label: "Happy Customers", value: "310,000+" },
  { label: "Countries Served", value: "150+" },
];

export default function Home() {
  return (
    <div className="flex flex-1 flex-col">
      <header className="sticky top-0 z-50 border-b border-black/5 bg-background/80 backdrop-blur-md dark:border-white/10">
        <div className="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4">
          <span className="text-xl font-bold tracking-tight">
            OC <span className="brand-gradient-text">TECH</span> Marketplace
          </span>
          <nav className="hidden items-center gap-8 text-sm font-medium text-foreground/70 md:flex">
            <Link href="#categories" className="hover:text-foreground">
              Categories
            </Link>
            <Link href="#products" className="hover:text-foreground">
              Products
            </Link>
            <Link href="#vendors" className="hover:text-foreground">
              Become a Vendor
            </Link>
            <Link href="#support" className="hover:text-foreground">
              Support
            </Link>
          </nav>
          <div className="flex items-center gap-3">
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
          </div>
        </div>
      </header>

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

            <form className="glass mt-10 flex w-full max-w-xl items-center gap-2 rounded-2xl p-2 shadow-xl">
              <input
                type="search"
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
                <div
                  key={category}
                  className="glass group cursor-pointer rounded-2xl p-6 text-center transition-transform hover:-translate-y-1"
                >
                  <p className="font-semibold">{category}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        <section id="products" className="px-6 py-16">
          <div className="mx-auto max-w-7xl">
            <div className="flex items-center justify-between">
              <h2 className="text-2xl font-bold tracking-tight">Featured Products</h2>
              <Link href="#" className="text-sm font-semibold text-brand-blue">
                View all &rarr;
              </Link>
            </div>
            <div className="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
              {featuredProducts.map((product) => (
                <div
                  key={product.name}
                  className="glass flex flex-col rounded-2xl p-5 shadow-sm transition-transform hover:-translate-y-1"
                >
                  <div className="brand-gradient mb-4 h-32 w-full rounded-xl opacity-80" />
                  <span className="text-xs font-medium uppercase tracking-wide text-brand-orange">
                    {product.category}
                  </span>
                  <h3 className="mt-1 font-semibold">{product.name}</h3>
                  <p className="mt-1 text-sm text-foreground/50">by {product.vendor}</p>
                  <div className="mt-4 flex items-center justify-between">
                    <span className="text-lg font-bold">{product.price}</span>
                    <button className="rounded-full border border-black/10 px-4 py-1.5 text-sm font-medium hover:bg-foreground hover:text-background dark:border-white/10">
                      View
                    </button>
                  </div>
                </div>
              ))}
            </div>
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
              href="/register?role=vendor"
              className="brand-gradient mt-8 rounded-full px-8 py-3 text-sm font-semibold text-white transition-transform hover:scale-105"
            >
              Become a Vendor
            </Link>
          </div>
        </section>
      </main>

      <footer
        id="support"
        className="border-t border-black/5 px-6 py-12 text-sm text-foreground/60 dark:border-white/10"
      >
        <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 sm:flex-row">
          <span>&copy; {new Date().getFullYear()} OC TECH Marketplace. All rights reserved.</span>
          <div className="flex gap-6">
            <Link href="#" className="hover:text-foreground">
              Support
            </Link>
            <Link href="#" className="hover:text-foreground">
              Documentation
            </Link>
            <Link href="#" className="hover:text-foreground">
              Vendor Terms
            </Link>
          </div>
        </div>
      </footer>
    </div>
  );
}
