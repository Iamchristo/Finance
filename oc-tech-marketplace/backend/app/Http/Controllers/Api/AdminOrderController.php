<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Order::query()
                ->with('user:id,name,email', 'items.product')
                ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
                ->when($request->boolean('flagged'), fn ($q) => $q->where('is_flagged', true))
                ->latest()
                ->paginate(20)
        );
    }
}
