<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    public function apply(Request $request)
    {
        $user = $request->user();

        if ($user->vendor) {
            return response()->json(['message' => 'You already have a vendor profile.', 'vendor' => $user->vendor], 422);
        }

        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url'],
        ]);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'store_name' => $data['store_name'],
            'slug' => Str::slug($data['store_name']).'-'.Str::lower(Str::random(5)),
            'description' => $data['description'] ?? null,
            'website_url' => $data['website_url'] ?? null,
            'verification_status' => 'pending',
        ]);

        $user->update(['role' => UserRole::Vendor->value]);

        return response()->json($vendor, 201);
    }

    public function me(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (! $vendor) {
            return response()->json(['message' => 'No vendor profile found.'], 404);
        }

        return response()->json($vendor);
    }

    public function approve(Request $request, Vendor $vendor)
    {
        $vendor->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        return response()->json($vendor);
    }

    public function index(Request $request)
    {
        return response()->json(Vendor::query()->with('user:id,name,email')->paginate(20));
    }
}
