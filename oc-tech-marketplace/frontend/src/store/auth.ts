import { create } from "zustand";
import { persist } from "zustand/middleware";
import { useEffect, useState } from "react";

export type AuthUser = {
  id: number;
  name: string;
  email: string;
  role: string;
};

type AuthState = {
  user: AuthUser | null;
  token: string | null;
  setAuth: (user: AuthUser, token: string) => void;
  updateUser: (user: AuthUser) => void;
  logout: () => void;
};

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      user: null,
      token: null,
      setAuth: (user, token) => set({ user, token }),
      updateUser: (user) => set({ user }),
      logout: () => set({ user: null, token: null }),
    }),
    { name: "octech-auth" },
  ),
);

/**
 * zustand's persist middleware reads localStorage asynchronously, so the
 * store briefly reports token=null on every hard page load before the real
 * value loads. Callers that redirect on a missing token must wait for this
 * to flip true first, or they'll bounce logged-in users to /login.
 */
export function useAuthHydrated(): boolean {
  const [hydrated, setHydrated] = useState(false);

  useEffect(() => {
    setHydrated(useAuthStore.persist.hasHydrated());
    return useAuthStore.persist.onFinishHydration(() => setHydrated(true));
  }, []);

  return hydrated;
}
