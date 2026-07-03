<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookEndpointController extends Controller
{
    private const AVAILABLE_EVENTS = ['order.completed', 'product.published', 'withdrawal.approved'];

    public function index(Request $request)
    {
        return response()->json($request->user()->webhookEndpoints()->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'url' => ['required', 'url'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'in:'.implode(',', self::AVAILABLE_EVENTS)],
        ]);

        $endpoint = $request->user()->webhookEndpoints()->create([
            'url' => $data['url'],
            'secret' => Str::random(40),
            'events' => $data['events'],
        ]);

        return response()->json($endpoint, 201);
    }

    public function destroy(Request $request, WebhookEndpoint $webhookEndpoint)
    {
        abort_unless($webhookEndpoint->user_id === $request->user()->id, 403);

        $webhookEndpoint->delete();

        return response()->json(['message' => 'Webhook endpoint removed.']);
    }

    public function deliveries(Request $request, WebhookEndpoint $webhookEndpoint)
    {
        abort_unless($webhookEndpoint->user_id === $request->user()->id, 403);

        return response()->json($webhookEndpoint->deliveries()->latest()->limit(50)->get());
    }
}
