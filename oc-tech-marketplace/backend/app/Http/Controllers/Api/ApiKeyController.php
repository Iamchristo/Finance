<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->apiKeys()->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        ['model' => $apiKey, 'plainTextKey' => $plainTextKey] = ApiKey::generate($request->user(), $data['name']);

        return response()->json([
            'api_key' => $apiKey,
            'plain_text_key' => $plainTextKey,
        ], 201);
    }

    public function destroy(Request $request, ApiKey $apiKey)
    {
        abort_unless($apiKey->user_id === $request->user()->id, 403);

        $apiKey->update(['revoked_at' => now()]);

        return response()->json(['message' => 'API key revoked.']);
    }
}
