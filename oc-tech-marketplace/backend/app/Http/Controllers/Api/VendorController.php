<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Vendor;
use App\Models\Wallet;
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
        $vendor = $request->user()->activeVendor();

        if (! $vendor) {
            return response()->json(['message' => 'No vendor profile found.'], 404);
        }

        return response()->json([
            ...$vendor->toArray(),
            'wallet_balance' => Wallet::firstOrCreate(['user_id' => $vendor->user_id])->balance,
            'is_owner' => $vendor->user_id === $request->user()->id,
        ]);
    }

    public function update(Request $request)
    {
        $vendor = $request->user()->vendor;

        abort_unless($vendor, 404, 'No vendor profile found.');

        $data = $request->validate([
            'store_name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url'],
            'logo_url' => ['nullable', 'url'],
        ]);

        $vendor->update($data);

        return response()->json($vendor);
    }

    public function approve(Request $request, Vendor $vendor)
    {
        $vendor->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        AuditLog::record('vendor.approved', $vendor, ['store_name' => $vendor->store_name]);

        return response()->json($vendor);
    }

    public function reject(Request $request, Vendor $vendor)
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:1000']]);

        $vendor->update(['verification_status' => 'rejected']);

        AuditLog::record('vendor.rejected', $vendor, ['store_name' => $vendor->store_name, ...$data]);

        return response()->json($vendor);
    }

    public function index(Request $request)
    {
        return response()->json(
            Vendor::query()
                ->with('user:id,name,email')
                ->when($request->query('status'), fn ($q, $status) => $q->where('verification_status', $status))
                ->latest()
                ->paginate(20)
        );
    }
}
