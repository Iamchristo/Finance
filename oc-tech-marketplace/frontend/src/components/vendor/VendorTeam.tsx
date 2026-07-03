"use client";

import { useEffect, useState } from "react";
import { apiFetch, ApiError } from "@/lib/api";
import type { VendorTeamMember } from "@/lib/types";

export function VendorTeam({ token, isOwner }: { token: string; isOwner: boolean }) {
  const [members, setMembers] = useState<VendorTeamMember[] | null>(null);
  const [email, setEmail] = useState("");
  const [role, setRole] = useState<"manager" | "staff">("staff");
  const [error, setError] = useState<string | null>(null);

  function refresh() {
    apiFetch<VendorTeamMember[]>("/vendor/team", { token }).then(setMembers);
  }

  useEffect(refresh, [token]);

  if (!isOwner) return null;

  async function invite(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    try {
      await apiFetch("/vendor/team", { method: "POST", token, body: { email, role } });
      setEmail("");
      refresh();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Unable to add team member.");
    }
  }

  async function remove(id: number) {
    await apiFetch(`/vendor/team/${id}`, { method: "DELETE", token });
    refresh();
  }

  return (
    <div className="glass mt-6 rounded-2xl p-5">
      <h3 className="font-semibold">Team</h3>
      <p className="mt-1 text-xs text-foreground/50">
        Managers can create and publish products; staff get read access to your dashboard.
      </p>

      <form onSubmit={invite} className="mt-3 flex flex-wrap items-end gap-2">
        <div>
          <label className="text-xs text-foreground/50">Email of an existing user</label>
          <input
            required
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="mt-1 block w-56 rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
          />
        </div>
        <div>
          <label className="text-xs text-foreground/50">Role</label>
          <select
            value={role}
            onChange={(e) => setRole(e.target.value as "manager" | "staff")}
            className="mt-1 block rounded-lg border border-black/10 bg-transparent px-2 py-1.5 text-sm outline-none dark:border-white/10"
          >
            <option value="staff">Staff</option>
            <option value="manager">Manager</option>
          </select>
        </div>
        <button className="rounded-lg border border-black/10 px-4 py-1.5 text-sm font-medium hover:bg-foreground hover:text-background dark:border-white/10">
          Invite
        </button>
      </form>
      {error && <p className="mt-2 text-xs text-red-500">{error}</p>}

      <div className="mt-4 flex flex-col gap-2">
        {members?.map((member) => (
          <div key={member.id} className="flex items-center justify-between text-sm">
            <span>
              {member.user?.name} <span className="text-foreground/50">({member.user?.email})</span>
            </span>
            <span className="text-foreground/50">{member.role}</span>
            <button onClick={() => remove(member.id)} className="text-xs font-semibold text-red-500">
              Remove
            </button>
          </div>
        ))}
        {members?.length === 0 && <p className="text-sm text-foreground/50">No team members yet.</p>}
      </div>
    </div>
  );
}
