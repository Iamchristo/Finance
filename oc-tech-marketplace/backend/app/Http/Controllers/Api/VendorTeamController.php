<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VendorTeamController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;

        abort_unless($vendor, 404, 'No vendor profile found.');

        return response()->json($vendor->teamMembers()->with('user:id,name,email')->get());
    }

    public function store(Request $request)
    {
        $vendor = $request->user()->vendor;

        abort_unless($vendor, 403, 'Only the vendor account owner can invite team members.');

        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', 'in:manager,staff'],
        ]);

        $member = User::where('email', $data['email'])->first();

        if ($member->id === $request->user()->id || $member->vendor()->exists()) {
            throw ValidationException::withMessages(['email' => 'That user can\'t be added to this team.']);
        }

        $teamMember = $vendor->teamMembers()->updateOrCreate(
            ['user_id' => $member->id],
            ['role' => $data['role']]
        );

        return response()->json($teamMember->load('user:id,name,email'), 201);
    }

    public function destroy(Request $request, int $teamMemberId)
    {
        $vendor = $request->user()->vendor;

        abort_unless($vendor, 403, 'Only the vendor account owner can remove team members.');

        $vendor->teamMembers()->where('id', $teamMemberId)->firstOrFail()->delete();

        return response()->json(['message' => 'Team member removed.']);
    }
}
