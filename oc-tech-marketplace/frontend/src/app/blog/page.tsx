import Link from "next/link";
import type { Metadata } from "next";
import type { BlogPost, Paginated } from "@/lib/types";

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

export const metadata: Metadata = {
  title: "Blog",
  description: "Guides, product updates, and stories from the OC TECH Marketplace team.",
};

async function fetchPosts(): Promise<BlogPost[]> {
  const res = await fetch(`${API_URL}/blog`, { cache: "no-store" });
  if (!res.ok) return [];
  const data: Paginated<BlogPost> = await res.json();
  return data.data;
}

export default async function BlogIndexPage() {
  const posts = await fetchPosts();

  return (
    <main className="flex-1 px-6 py-12">
      <div className="mx-auto max-w-4xl">
        <h1 className="text-3xl font-bold tracking-tight">Blog</h1>
        <p className="mt-2 text-sm text-foreground/50">
          Guides, product updates, and stories from the OC TECH Marketplace team.
        </p>

        {posts.length === 0 && (
          <div className="glass mt-8 rounded-2xl p-12 text-center text-foreground/50">No posts yet.</div>
        )}

        <div className="mt-8 flex flex-col gap-4">
          {posts.map((post) => (
            <Link
              key={post.id}
              href={`/blog/${post.slug}`}
              className="glass flex flex-col rounded-2xl p-6 transition-transform hover:-translate-y-1"
            >
              <span className="text-xs text-foreground/50">
                {post.published_at ? new Date(post.published_at).toLocaleDateString() : ""}
                {post.author?.name ? ` · ${post.author.name}` : ""}
              </span>
              <h2 className="mt-1 text-xl font-semibold">{post.title}</h2>
              {post.excerpt && <p className="mt-2 text-sm text-foreground/60">{post.excerpt}</p>}
            </Link>
          ))}
        </div>
      </div>
    </main>
  );
}
