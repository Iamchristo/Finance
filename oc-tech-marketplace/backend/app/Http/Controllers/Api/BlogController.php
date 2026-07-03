<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        return response()->json(
            BlogPost::published()->with('author:id,name')->latest('published_at')->paginate(10)
        );
    }

    public function show(BlogPost $blogPost)
    {
        abort_unless($blogPost->status === 'published' && $blogPost->published_at?->isPast(), 404);

        $blogPost->load('author:id,name');

        return response()->json($blogPost);
    }
}
