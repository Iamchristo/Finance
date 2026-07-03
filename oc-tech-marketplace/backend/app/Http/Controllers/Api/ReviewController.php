<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $user = $request->user();

        if ($product->reviews()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages(['review' => 'You have already reviewed this product.']);
        }

        $orderItem = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id)->where('status', 'completed'))
            ->first();

        if (! $orderItem) {
            throw ValidationException::withMessages(['review' => 'You can only review products you have purchased.']);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_item_id' => $orderItem->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_verified_purchase' => true,
        ]);

        $product->average_rating = round($product->reviews()->avg('rating'), 2);
        $product->save();

        return response()->json($review->load('user'), 201);
    }

    public function reply(Request $request, Review $review)
    {
        $vendor = $request->user()->activeVendor();

        abort_unless($vendor && $review->product->vendor_id === $vendor->id, 403);

        $data = $request->validate([
            'vendor_reply' => ['required', 'string', 'max:2000'],
        ]);

        $review->update(['vendor_reply' => $data['vendor_reply']]);

        return response()->json($review);
    }

    public function report(Request $request, Review $review)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $review->update([
            'report_reason' => $data['reason'],
            'reported_at' => now(),
        ]);

        return response()->json(['message' => 'Thanks — our team will take a look.']);
    }
}
