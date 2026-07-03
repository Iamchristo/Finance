<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;

        abort_unless($vendor, 404, 'No vendor profile found.');

        return response()->json($vendor->withdrawalRequests()->latest()->get());
    }

    public function store(Request $request)
    {
        $vendor = $request->user()->vendor;

        abort_unless($vendor, 404, 'No vendor profile found.');

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        return response()->json(DB::transaction(function () use ($vendor, $data) {
            $wallet = Wallet::query()->lockForUpdate()->firstOrCreate(['user_id' => $vendor->user_id]);

            if ((float) $wallet->balance < (float) $data['amount']) {
                throw ValidationException::withMessages(['amount' => 'Insufficient wallet balance.']);
            }

            $wallet->decrement('balance', $data['amount']);
            $wallet->transactions()->create([
                'type' => 'debit',
                'amount' => $data['amount'],
                'balance_after' => $wallet->balance,
                'description' => 'Withdrawal request pending review',
            ]);

            return $vendor->withdrawalRequests()->create([
                'amount' => $data['amount'],
                'status' => 'pending',
            ]);
        }), 201);
    }

    public function adminIndex(Request $request)
    {
        return response()->json(
            WithdrawalRequest::with('vendor.user:id,name,email')
                ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
                ->latest()
                ->get()
        );
    }

    public function approve(WithdrawalRequest $withdrawalRequest)
    {
        $withdrawalRequest->update(['status' => 'approved', 'processed_at' => now()]);

        AuditLog::record('withdrawal.approved', $withdrawalRequest, ['amount' => $withdrawalRequest->amount]);

        return response()->json($withdrawalRequest);
    }

    public function reject(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        $result = DB::transaction(function () use ($request, $withdrawalRequest) {
            $wallet = Wallet::firstOrCreate(['user_id' => $withdrawalRequest->vendor->user_id]);
            $wallet->increment('balance', $withdrawalRequest->amount);
            $wallet->transactions()->create([
                'type' => 'credit',
                'amount' => $withdrawalRequest->amount,
                'balance_after' => $wallet->balance,
                'description' => 'Withdrawal request rejected — funds returned',
            ]);

            $withdrawalRequest->update([
                'status' => 'rejected',
                'processed_at' => now(),
                'notes' => $request->input('notes'),
            ]);

            return $withdrawalRequest;
        });

        AuditLog::record('withdrawal.rejected', $withdrawalRequest, ['amount' => $withdrawalRequest->amount]);

        return response()->json($result);
    }
}
