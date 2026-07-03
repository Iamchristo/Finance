<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index()
    {
        return response()->json(
            Review::reported()->with(['user:id,name', 'product:id,title'])->latest('reported_at')->get()
        );
    }

    public function dismiss(Review $review)
    {
        $review->update(['report_reason' => null, 'reported_at' => null]);

        AuditLog::record('review.report_dismissed', $review);

        return response()->json($review);
    }

    public function hide(Request $request, Review $review)
    {
        $review->update(['status' => 'hidden']);

        AuditLog::record('review.hidden', $review, ['reason' => $review->report_reason]);

        $product = $review->product;
        $product->average_rating = round($product->reviews()->visible()->avg('rating') ?? 0, 2);
        $product->save();

        return response()->json($review);
    }
}
