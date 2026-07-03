"use client";

import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { useAuthHydrated, useAuthStore } from "@/store/auth";

const ADMIN_ROLES = ["administrator", "super_administrator"];

export function useRequireAdmin() {
  const router = useRouter();
  const { user, token } = useAuthStore();
  const hydrated = useAuthHydrated();
  const [ready, setReady] = useState(false);

  useEffect(() => {
    if (!hydrated) return;

    if (!token) {
      router.replace("/login");
      return;
    }
    if (!user || !ADMIN_ROLES.includes(user.role)) {
      router.replace("/");
      return;
    }
    setReady(true);
  }, [hydrated, token, user, router]);

  return { user, token, ready };
}
