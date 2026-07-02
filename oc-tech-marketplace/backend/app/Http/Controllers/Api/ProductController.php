<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['vendor', 'category'])
            ->where('status', 'published')
            ->when($request->query('category'), fn ($query, $slug) => $query->whereHas(
                'category',
                fn ($q) => $q->where('slug', $slug)
            ))
            ->when($request->query('search'), fn ($query, $term) => $query->where('title', 'like', "%{$term}%"))
            ->latest('published_at')
            ->paginate(20);

        return response()->json($products);
    }

    public function show(Product $product)
    {
        $product->load(['vendor', 'category', 'licenses', 'reviews.user']);

        return response()->json($product);
    }
}
