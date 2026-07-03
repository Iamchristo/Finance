<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\Request;

class VendorFlashSaleController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->activeVendor();

        abort_unless($vendor, 404, 'No vendor profile found.');

        return response()->json(
            $vendor->flashSales()->with('product:id,title,slug,base_price')->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $vendor = $request->user()->activeVendor();

        abort_unless($vendor, 422, 'You need a vendor profile before running a flash sale.');

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'discount_percent' => ['required', 'integer', 'min:1', 'max:90'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $product = Product::where('id', $data['product_id'])->where('vendor_id', $vendor->id)->firstOrFail();

        $flashSale = $vendor->flashSales()->create([
            'product_id' => $product->id,
            'discount_percent' => $data['discount_percent'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
        ]);

        return response()->json($flashSale->load('product'), 201);
    }

    public function destroy(Request $request, FlashSale $flashSale)
    {
        $vendor = $request->user()->activeVendor();

        abort_unless($vendor && $flashSale->vendor_id === $vendor->id, 403);

        $flashSale->delete();

        return response()->json(['message' => 'Flash sale cancelled.']);
    }
}
