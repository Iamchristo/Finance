<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WebhookDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function __construct(private readonly WebhookDispatcher $webhooks) {}

    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['vendor', 'category', 'flashSales' => fn ($q) => $q->active()])
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
        $product->load(['vendor', 'category', 'licenses', 'flashSales' => fn ($q) => $q->active()]);
        $product->setRelation('reviews', $product->reviews()->visible()->with('user')->get());

        return response()->json($product);
    }

    public function mine(Request $request)
    {
        $vendor = $request->user()->activeVendor();

        abort_unless($vendor, 404, 'No vendor profile found.');

        return response()->json(
            $vendor->products()->with(['category', 'licenses', 'files'])->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $vendor = $request->user()->activeVendor();

        abort_unless($vendor, 422, 'You need a vendor profile before uploading products.');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'thumbnail_url' => ['nullable', 'url'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'licenses' => ['required', 'array', 'min:1'],
            'licenses.*.type' => ['required', 'string'],
            'licenses.*.name' => ['required', 'string'],
            'licenses.*.price' => ['required', 'numeric', 'min:0'],
            'licenses.*.terms' => ['nullable', 'string'],
            'licenses.*.download_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = DB::transaction(function () use ($data, $vendor) {
            $product = $vendor->products()->create([
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(6)),
                'summary' => $data['summary'] ?? null,
                'description' => $data['description'] ?? null,
                'thumbnail_url' => $data['thumbnail_url'] ?? null,
                'base_price' => $data['base_price'],
                'status' => 'draft',
            ]);

            foreach ($data['licenses'] as $license) {
                $product->licenses()->create($license);
            }

            return $product;
        });

        return response()->json($product->load('licenses'), 201);
    }

    public function uploadFile(Request $request, Product $product)
    {
        $this->authorizeVendorOwnsProduct($request, $product);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:512000'],
            'version' => ['required', 'string', 'max:50'],
            'changelog' => ['nullable', 'string'],
        ]);

        $path = $request->file('file')->store("products/{$product->id}", 'local');

        $file = DB::transaction(function () use ($product, $data, $path, $request) {
            $product->files()->update(['is_current' => false]);

            return $product->files()->create([
                'version' => $data['version'],
                'disk' => 'local',
                'path' => $path,
                'size_bytes' => $request->file('file')->getSize(),
                'checksum' => hash_file('sha256', $request->file('file')->getRealPath()),
                'changelog' => $data['changelog'] ?? null,
                'is_current' => true,
            ]);
        });

        return response()->json($file, 201);
    }

    public function publish(Request $request, Product $product)
    {
        $this->authorizeVendorOwnsProduct($request, $product);

        if ($product->vendor->verification_status !== 'verified') {
            throw ValidationException::withMessages(['vendor' => 'Your vendor profile must be verified before publishing products.']);
        }

        if ($product->licenses()->count() === 0) {
            throw ValidationException::withMessages(['licenses' => 'Add at least one license before publishing.']);
        }

        if ($product->files()->where('is_current', true)->doesntExist()) {
            throw ValidationException::withMessages(['file' => 'Upload a product file before publishing.']);
        }

        $product->update(['status' => 'published', 'published_at' => now()]);

        $this->webhooks->dispatch('product.published', $product->vendor->user, [
            'product_id' => $product->id,
            'title' => $product->title,
            'slug' => $product->slug,
        ]);

        return response()->json($product);
    }

    private function authorizeVendorOwnsProduct(Request $request, Product $product): void
    {
        $vendor = $request->user()->activeVendor();

        abort_unless($vendor && $product->vendor_id === $vendor->id, 403);
    }
}
