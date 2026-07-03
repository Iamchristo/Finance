import type { Metadata } from "next";
import { notFound } from "next/navigation";
import type { BlogPost } from "@/lib/types";

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

async function fetchPost(slug: string): Promise<BlogPost | null> {
  const res = await fetch(`${API_URL}/blog/${slug}`, { cache: "no-store" });
  if (!res.ok) return null;
  return res.json();
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  const post = await fetchPost(slug);

  if (!post) {
    return { title: "Post not found" };
  }

  return {
    title: post.title,
    description: post.excerpt ?? undefined,
    openGraph: {
      title: post.title,
      description: post.excerpt ?? undefined,
      type: "article",
      ...(post.cover_image_url ? { images: [post.cover_image_url] } : {}),
    },
  };
}

export default async function BlogPostPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const post = await fetchPost(slug);

  if (!post) {
    notFound();
  }

  const jsonLd = {
    "@context": "https://schema.org",
    "@type": "Article",
    headline: post.title,
    description: post.excerpt ?? undefined,
    datePublished: post.published_at ?? undefined,
    author: post.author ? { "@type": "Person", name: post.author.name } : undefined,
  };

  return (
    <main className="flex-1 px-6 py-12">
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }} />
      <article className="mx-auto max-w-3xl">
        <span className="text-xs text-foreground/50">
          {post.published_at ? new Date(post.published_at).toLocaleDateString() : ""}
          {post.author?.name ? ` · ${post.author.name}` : ""}
        </span>
        <h1 className="mt-2 text-3xl font-bold tracking-tight">{post.title}</h1>
        <div className="mt-6 whitespace-pre-wrap text-foreground/70">{post.body}</div>
      </article>
    </main>
  );
}
