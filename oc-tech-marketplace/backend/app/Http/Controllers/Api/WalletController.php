<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request)
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $request->user()->id]);

        return response()->json($wallet->load(['transactions' => fn ($q) => $q->latest()->limit(50)]));
    }

    /**
     * Dev/manual top-up representing a reconciled bank transfer deposit.
     * In production this would be replaced by a verified payment gateway callback.
     */
    public function topup(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:100000'],
        ]);

        $wallet = Wallet::firstOrCreate(['user_id' => $request->user()->id]);
        $wallet->increment('balance', $data['amount']);
        $wallet->transactions()->create([
            'type' => 'credit',
            'amount' => $data['amount'],
            'balance_after' => $wallet->balance,
            'description' => 'Bank transfer top-up',
        ]);

        return response()->json($wallet);
    }
}
