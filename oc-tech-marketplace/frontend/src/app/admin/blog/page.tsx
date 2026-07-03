"use client";

import { useEffect, useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import { useAuthStore } from "@/store/auth";
import type { BlogPost } from "@/lib/types";

export default function AdminBlogPage() {
  const token = useAuthStore((s) => s.token);
  const [posts, setPosts] = useState<BlogPost[] | null>(null);
  const [showForm, setShowForm] = useState(false);
  const [title, setTitle] = useState("");
  const [excerpt, setExcerpt] = useState("");
  const [body, setBody] = useState("");
  const [status, setStatus] = useState<"draft" | "published">("draft");
  const [error, setError] = useState<string | null>(null);

  function refresh() {
    if (token) apiFetch<BlogPost[]>("/admin/blog", { token }).then(setPosts);
  }

  useEffect(refresh, [token]);

  async function createPost(e: React.FormEvent) {
    e.preventDefault();
    if (!token) return;
    setError(null);
    try {
      await apiFetch("/admin/blog", { method: "POST", token, body: { title, excerpt, body, status } });
      setTitle("");
      setExcerpt("");
      setBody("");
      setStatus("draft");
      setShowForm(false);
      refresh();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to create post.");
    }
  }

  async function togglePublish(post: BlogPost) {
    if (!token) return;
    await apiFetch(`/admin/blog/${post.id}`, {
      method: "PATCH",
      token,
      body: { status: post.status === "published" ? "draft" : "published" },
    });
    refresh();
  }

  async function remove(post: BlogPost) {
    if (!token) return;
    await apiFetch(`/admin/blog/${post.id}`, { method: "DELETE", token });
    refresh();
  }

  if (posts === null) {
    return <div className="glass h-64 animate-pulse rounded-3xl" />;
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <h2 className="text-xl font-semibold">Blog Posts</h2>
        <button
          onClick={() => setShowForm((s) => !s)}
          className="brand-gradient rounded-full px-4 py-1.5 text-sm font-semibold text-white"
        >
          {showForm ? "Cancel" : "New Post"}
        </button>
      </div>

      {showForm && (
        <form onSubmit={createPost} className="glass mt-4 flex flex-col gap-3 rounded-2xl p-5">
          <input
            required
            placeholder="Title"
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            className="rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none dark:border-white/10"
          />
          <input
            placeholder="Excerpt (optional)"
            value={excerpt}
            onChange={(e) => setExcerpt(e.target.value)}
            className="rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none dark:border-white/10"
          />
          <textarea
            required
            placeholder="Body"
            rows={6}
            value={body}
            onChange={(e) => setBody(e.target.value)}
            className="rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none dark:border-white/10"
          />
          <select
            value={status}
            onChange={(e) => setStatus(e.target.value as "draft" | "published")}
            className="w-40 rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none dark:border-white/10"
          >
            <option value="draft">Save as draft</option>
            <option value="published">Publish now</option>
          </select>
          {error && <p className="text-sm text-red-500">{error}</p>}
          <button className="brand-gradient w-fit rounded-xl px-6 py-2.5 text-sm font-semibold text-white">
            Save
          </button>
        </form>
      )}

      <div className="mt-6 flex flex-col gap-3">
        {posts.map((post) => (
          <div key={post.id} className="glass flex items-center justify-between rounded-2xl p-5">
            <div>
              <p className="font-semibold">{post.title}</p>
              <p className="text-sm text-foreground/50">
                {post.status} &middot; by {post.author?.name ?? "—"}
              </p>
            </div>
            <div className="flex gap-2">
              <button
                onClick={() => togglePublish(post)}
                className="rounded-lg border border-black/10 px-3 py-1.5 text-xs font-medium hover:bg-foreground hover:text-background dark:border-white/10"
              >
                {post.status === "published" ? "Unpublish" : "Publish"}
              </button>
              <button onClick={() => remove(post)} className="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-600">
                Delete
              </button>
            </div>
          </div>
        ))}
        {posts.length === 0 && <p className="text-sm text-foreground/50">No blog posts yet.</p>}
      </div>
    </div>
  );
}
