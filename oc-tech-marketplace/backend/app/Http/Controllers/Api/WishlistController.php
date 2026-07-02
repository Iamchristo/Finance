<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->wishlist()->with('product.vendor', 'product.category')->latest()->get()
        );
    }

    public function store(Request $request, Product $product)
    {
        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return response()->json($wishlist, 201);
    }

    public function destroy(Request $request, Product $product)
    {
        $request->user()->wishlist()->where('product_id', $product->id)->delete();

        return response()->json(['message' => 'Removed from wishlist.']);
    }
}
