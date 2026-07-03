<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenAiClient;
use Illuminate\Http\Request;

class AiSupportController extends Controller
{
    private const SYSTEM_PROMPT = <<<'TXT'
You are the friendly support assistant for OC TECH Marketplace, a digital marketplace for
websites, Laravel projects, UI kits, and other digital products. Customers can pay with wallet
balance or bank transfer, download purchased files from My Account -> Downloads, and leave a
review after a completed purchase. Vendors can apply to sell, publish products with license
tiers, run coupons and flash sales, bundle products together, and request payouts from their
vendor dashboard. You cannot look up a specific customer's order status or account details -
if asked, tell them to check My Account -> Orders or open a support ticket instead of guessing.
Keep answers under 4 sentences.
TXT;

    public function __construct(private readonly OpenAiClient $ai) {}

    public function chat(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['sometimes', 'array', 'max:10'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:1000'],
        ]);

        $messages = [['role' => 'system', 'content' => self::SYSTEM_PROMPT]];

        foreach ($data['history'] ?? [] as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $data['message']];

        $reply = $this->ai->chat($messages, ['temperature' => 0.5]);

        return response()->json([
            'ai_powered' => $reply !== null,
            'reply' => $reply ?? 'Our AI assistant is temporarily unavailable. Please open a support ticket and our team will get back to you.',
        ]);
    }
}
