<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key');

        if (! $key) {
            return response()->json(['message' => 'Missing X-API-Key header.'], 401);
        }

        $apiKey = ApiKey::findByPlainTextKey($key);

        if (! $apiKey) {
            return response()->json(['message' => 'Invalid or revoked API key.'], 401);
        }

        $apiKey->update(['last_used_at' => now()]);

        $request->setUserResolver(fn () => $apiKey->user);
        $request->attributes->set('apiKey', $apiKey);

        return $next($request);
    }
}
