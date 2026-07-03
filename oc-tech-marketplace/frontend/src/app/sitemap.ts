import type { MetadataRoute } from "next";
import type { BlogPost, Bundle, Category, Paginated, Product } from "@/lib/types";

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";
const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000";

async function safeFetch<T>(path: string, fallback: T): Promise<T> {
  try {
    const res = await fetch(`${API_URL}${path}`, { cache: "no-store" });
    if (!res.ok) return fallback;
    return await res.json();
  } catch {
    return fallback;
  }
}

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const [products, categories, posts, bundles] = await Promise.all([
    safeFetch<Paginated<Product>>("/products", { data: [], current_page: 1, last_page: 1, total: 0 }),
    safeFetch<Category[]>("/categories", []),
    safeFetch<Paginated<BlogPost>>("/blog", { data: [], current_page: 1, last_page: 1, total: 0 }),
    safeFetch<Bundle[]>("/bundles", []),
  ]);

  const staticRoutes: MetadataRoute.Sitemap = [
    { url: SITE_URL, changeFrequency: "daily", priority: 1 },
    { url: `${SITE_URL}/products`, changeFrequency: "hourly", priority: 0.9 },
    { url: `${SITE_URL}/bundles`, changeFrequency: "daily", priority: 0.6 },
    { url: `${SITE_URL}/blog`, changeFrequency: "daily", priority: 0.6 },
    { url: `${SITE_URL}/search`, changeFrequency: "monthly", priority: 0.3 },
  ];

  const productRoutes: MetadataRoute.Sitemap = products.data.map((product) => ({
    url: `${SITE_URL}/products/${product.slug}`,
    lastModified: product.published_at ?? undefined,
    changeFrequency: "weekly",
    priority: 0.7,
  }));

  const categoryRoutes: MetadataRoute.Sitemap = categories.map((category) => ({
    url: `${SITE_URL}/products?category=${category.slug}`,
    changeFrequency: "weekly",
    priority: 0.5,
  }));

  const blogRoutes: MetadataRoute.Sitemap = posts.data.map((post) => ({
    url: `${SITE_URL}/blog/${post.slug}`,
    lastModified: post.published_at ?? undefined,
    changeFrequency: "monthly",
    priority: 0.5,
  }));

  const bundleRoutes: MetadataRoute.Sitemap = bundles.map((bundle) => ({
    url: `${SITE_URL}/bundles/${bundle.slug}`,
    changeFrequency: "weekly",
    priority: 0.5,
  }));

  return [...staticRoutes, ...productRoutes, ...categoryRoutes, ...blogRoutes, ...bundleRoutes];
}
