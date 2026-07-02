<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    private const STAFF_ROLES = ['support_agent', 'moderator', 'administrator', 'super_administrator'];

    public function index(Request $request)
    {
        $user = $request->user();

        $query = in_array($user->role->value, self::STAFF_ROLES, true)
            ? SupportTicket::query()->with('user:id,name,email')
            : $user->supportTickets();

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'priority' => ['sometimes', 'in:low,normal,high,urgent'],
        ]);

        $ticket = $request->user()->supportTickets()->create([
            'subject' => $data['subject'],
            'order_id' => $data['order_id'] ?? null,
            'priority' => $data['priority'] ?? 'normal',
            'status' => 'open',
        ]);

        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $data['message'],
        ]);

        return response()->json($ticket->load('messages.user'), 201);
    }

    public function show(Request $request, SupportTicket $supportTicket)
    {
        $this->authorizeAccess($request, $supportTicket);

        return response()->json($supportTicket->load('messages.user', 'user:id,name,email'));
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $this->authorizeAccess($request, $supportTicket);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = $supportTicket->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $data['message'],
        ]);

        $isStaff = in_array($request->user()->role->value, self::STAFF_ROLES, true);
        $supportTicket->update(['status' => $isStaff ? 'in_progress' : 'open']);

        return response()->json($message->load('user'), 201);
    }

    public function close(Request $request, SupportTicket $supportTicket)
    {
        $this->authorizeAccess($request, $supportTicket);

        $supportTicket->update(['status' => 'closed']);

        return response()->json($supportTicket);
    }

    private function authorizeAccess(Request $request, SupportTicket $supportTicket): void
    {
        $user = $request->user();
        $isStaff = in_array($user->role->value, self::STAFF_ROLES, true);

        abort_unless($isStaff || $supportTicket->user_id === $user->id, 403);
    }
}
