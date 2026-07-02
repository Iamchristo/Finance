"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { apiFetch, ApiError } from "@/lib/api";
import { useAuthStore } from "@/store/auth";

const schema = z.object({
  email: z.string().email("Enter a valid email address."),
  password: z.string().min(1, "Password is required."),
});

type FormValues = z.infer<typeof schema>;

export default function LoginPage() {
  const router = useRouter();
  const setAuth = useAuthStore((s) => s.setAuth);
  const [serverError, setServerError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<FormValues>({ resolver: zodResolver(schema) });

  async function onSubmit(values: FormValues) {
    setServerError(null);
    try {
      const res = await apiFetch<{ user: { id: number; name: string; email: string; role: string }; token: string }>(
        "/auth/login",
        { method: "POST", body: values },
      );
      setAuth(res.user, res.token);
      router.push("/products");
    } catch (err) {
      setServerError(err instanceof ApiError ? err.message : "Unable to sign in.");
    }
  }

  return (
    <main className="flex flex-1 items-center justify-center px-6 py-16">
      <div className="glass w-full max-w-md rounded-3xl p-8">
        <h1 className="text-2xl font-bold tracking-tight">Sign in to OC TECH</h1>
        <p className="mt-1 text-sm text-foreground/60">Welcome back. Enter your details below.</p>

        <form onSubmit={handleSubmit(onSubmit)} className="mt-8 flex flex-col gap-4">
          <div>
            <label className="text-sm font-medium">Email</label>
            <input
              type="email"
              {...register("email")}
              className="mt-1 w-full rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none focus:border-brand-blue dark:border-white/10"
            />
            {errors.email && <p className="mt-1 text-xs text-red-500">{errors.email.message}</p>}
          </div>

          <div>
            <label className="text-sm font-medium">Password</label>
            <input
              type="password"
              {...register("password")}
              className="mt-1 w-full rounded-xl border border-black/10 bg-transparent px-4 py-2.5 text-sm outline-none focus:border-brand-blue dark:border-white/10"
            />
            {errors.password && <p className="mt-1 text-xs text-red-500">{errors.password.message}</p>}
          </div>

          {serverError && <p className="text-sm text-red-500">{serverError}</p>}

          <button
            type="submit"
            disabled={isSubmitting}
            className="brand-gradient mt-2 rounded-xl px-6 py-3 text-sm font-semibold text-white transition-transform hover:scale-[1.02] disabled:opacity-60"
          >
            {isSubmitting ? "Signing in..." : "Sign In"}
          </button>
        </form>

        <p className="mt-6 text-center text-sm text-foreground/60">
          Don&apos;t have an account?{" "}
          <Link href="/register" className="font-semibold text-brand-blue">
            Create one
          </Link>
        </p>
      </div>
    </main>
  );
}
