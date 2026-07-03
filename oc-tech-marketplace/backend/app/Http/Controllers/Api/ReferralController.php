<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        $rewards = ReferralReward::where('referrer_id', $user->id)
            ->with('referredUser:id,name')
            ->latest()
            ->get();

        return response()->json([
            'referral_code' => $user->referral_code,
            'referred_count' => User::where('referred_by_user_id', $user->id)->count(),
            'total_earned' => round((float) $rewards->sum('amount'), 2),
            'rewards' => $rewards,
        ]);
    }
}
