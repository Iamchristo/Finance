"use client";

import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { useAuthHydrated, useAuthStore } from "@/store/auth";

export function useRequireAuth() {
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
    setReady(true);
  }, [hydrated, token, router]);

  return { user, token, ready };
}
