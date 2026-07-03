<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductComparisonController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'string'],
        ]);

        $ids = array_slice(
            array_values(array_filter(array_map('intval', explode(',', $data['ids'])))),
            0,
            4
        );

        $products = Product::query()
            ->with(['vendor', 'category', 'licenses'])
            ->whereIn('id', $ids)
            ->where('status', 'published')
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $ids))
            ->values();

        return response()->json($products);
    }
}
