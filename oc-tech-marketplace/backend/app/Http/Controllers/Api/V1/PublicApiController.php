<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function products(Request $request)
    {
        $products = Product::query()
            ->with(['vendor:id,store_name', 'category:id,name,slug'])
            ->where('status', 'published')
            ->when($request->query('category'), fn ($q, $slug) => $q->whereHas(
                'category',
                fn ($qq) => $qq->where('slug', $slug)
            ))
            ->latest('published_at')
            ->paginate(20);

        return response()->json($products);
    }

    public function product(Product $product)
    {
        abort_unless($product->status === 'published', 404);

        return response()->json($product->load(['vendor:id,store_name', 'category:id,name,slug', 'licenses']));
    }

    public function categories()
    {
        return response()->json(Category::all());
    }

    public function createOrder(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.license_id' => ['required', 'integer'],
            'payment_method' => ['required', 'in:wallet,bank_transfer'],
        ]);

        $order = $this->orders->checkout($request->user(), $data['items'], null, $data['payment_method']);

        return response()->json($order, 201);
    }
}
