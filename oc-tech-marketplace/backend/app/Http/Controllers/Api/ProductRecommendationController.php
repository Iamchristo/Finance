<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;

class ProductRecommendationController extends Controller
{
    public function index(Product $product)
    {
        $coPurchasedOrderIds = OrderItem::where('product_id', $product->id)->pluck('order_id');

        $frequentlyBoughtWithIds = OrderItem::query()
            ->whereIn('order_id', $coPurchasedOrderIds)
            ->where('product_id', '!=', $product->id)
            ->select('product_id')
            ->groupBy('product_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(4)
            ->pluck('product_id');

        $recommended = Product::query()
            ->with(['vendor', 'category'])
            ->where('status', 'published')
            ->where('id', '!=', $product->id)
            ->whereIn('id', $frequentlyBoughtWithIds)
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $frequentlyBoughtWithIds->all()))
            ->values();

        if ($recommended->count() < 4) {
            $excludeIds = $recommended->pluck('id')->push($product->id);

            $filler = Product::query()
                ->with(['vendor', 'category'])
                ->where('status', 'published')
                ->where('category_id', $product->category_id)
                ->whereNotIn('id', $excludeIds)
                ->orderByDesc('sales_count')
                ->limit(4 - $recommended->count())
                ->get();

            $recommended = $recommended->concat($filler);
        }

        return response()->json($recommended->values());
    }
}
