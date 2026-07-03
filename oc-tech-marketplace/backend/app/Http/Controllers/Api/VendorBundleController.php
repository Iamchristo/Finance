<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VendorBundleController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;

        abort_unless($vendor, 404, 'No vendor profile found.');

        return response()->json(
            $vendor->bundles()->with('products:id,title,base_price')->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $vendor = $request->user()->vendor;

        abort_unless($vendor, 422, 'You need a vendor profile before creating a bundle.');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'bundle_price' => ['required', 'numeric', 'min:0'],
            'product_ids' => ['required', 'array', 'min:2'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $ownedPublishedCount = Product::whereIn('id', $data['product_ids'])
            ->where('vendor_id', $vendor->id)
            ->where('status', 'published')
            ->count();

        if ($ownedPublishedCount !== count($data['product_ids'])) {
            throw ValidationException::withMessages(['product_ids' => 'All bundle products must be your own published products.']);
        }

        $bundle = DB::transaction(function () use ($vendor, $data) {
            $bundle = $vendor->bundles()->create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(6)),
                'description' => $data['description'] ?? null,
                'bundle_price' => $data['bundle_price'],
            ]);

            $bundle->products()->attach($data['product_ids']);

            return $bundle;
        });

        return response()->json($bundle->load('products'), 201);
    }

    public function destroy(Request $request, Bundle $bundle)
    {
        $vendor = $request->user()->vendor;

        abort_unless($vendor && $bundle->vendor_id === $vendor->id, 403);

        $bundle->update(['is_active' => false]);

        return response()->json(['message' => 'Bundle deactivated.']);
    }
}
