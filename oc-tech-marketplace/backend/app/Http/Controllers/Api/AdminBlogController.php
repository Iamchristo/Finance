<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBlogController extends Controller
{
    public function index()
    {
        return response()->json(BlogPost::with('author:id,name')->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'cover_image_url' => ['nullable', 'url'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $post = BlogPost::create([
            ...$data,
            'author_id' => $request->user()->id,
            'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(6)),
            'published_at' => $data['status'] === 'published' ? now() : null,
        ]);

        AuditLog::record('blog.created', $post, ['title' => $post->title]);

        return response()->json($post, 201);
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['sometimes', 'string'],
            'cover_image_url' => ['nullable', 'url'],
            'status' => ['sometimes', 'in:draft,published'],
        ]);

        if (($data['status'] ?? null) === 'published' && $blogPost->status !== 'published') {
            $data['published_at'] = now();
        }

        $blogPost->update($data);

        AuditLog::record('blog.updated', $blogPost, ['title' => $blogPost->title]);

        return response()->json($blogPost);
    }

    public function destroy(BlogPost $blogPost)
    {
        AuditLog::record('blog.deleted', $blogPost, ['title' => $blogPost->title]);

        $blogPost->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
