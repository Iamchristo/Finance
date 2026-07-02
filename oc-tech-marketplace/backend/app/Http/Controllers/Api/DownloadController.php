<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DownloadController extends Controller
{
    public function index(Request $request)
    {
        $items = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id)->where('status', 'completed'))
            ->with(['product', 'license'])
            ->get()
            ->unique('product_id')
            ->values();

        return response()->json($items);
    }

    public function requestLink(Request $request, Product $product)
    {
        $item = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id)->where('status', 'completed'))
            ->with('license')
            ->latest()
            ->first();

        abort_unless($item, 403, 'You have not purchased this product.');

        $limit = $item->license?->download_limit;

        if ($limit && $item->downloads_used >= $limit) {
            abort(429, 'Download limit reached for this license.');
        }

        $file = $product->files()->where('is_current', true)->first();

        abort_unless($file, 404, 'No downloadable file is available for this product yet.');

        $item->increment('downloads_used');
        $product->increment('download_count');

        $url = URL::temporarySignedRoute('downloads.file', now()->addMinutes(10), [
            'orderItem' => $item->id,
        ]);

        return response()->json(['url' => $url, 'expires_in_minutes' => 10]);
    }

    public function file(Request $request, OrderItem $orderItem)
    {
        abort_unless($request->hasValidSignature(), 403, 'This download link is invalid or has expired.');

        $file = $orderItem->product->files()->where('is_current', true)->first();

        abort_unless($file, 404);

        return Storage::disk($file->disk)->download($file->path, $orderItem->product->slug.'-'.$file->version.'.zip');
    }
}
