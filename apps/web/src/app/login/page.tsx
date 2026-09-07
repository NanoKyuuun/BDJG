"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Image from "next/image";
import { getDefaultPortalForRoles } from "@/lib/use-auth";

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [checkingAuth, setCheckingAuth] = useState(true);

  useEffect(() => {
    fetch("/api/v1/me", {
      credentials: "include",
      headers: { Accept: "application/json" },
    })
      .then((res) => {
        if (res.ok) return res.json();
      })
      .then((data) => {
        if (data?.data?.roles) {
          const target = getDefaultPortalForRoles(data.data.roles);
          router.replace(target);
        } else {
          setCheckingAuth(false);
        }
      })
      .catch(() => {
        setCheckingAuth(false);
      });
  }, [router]);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    setError("");

    try {
      await fetch("/sanctum/csrf-cookie", { credentials: "include" });

      const xsrfToken = document.cookie
        .split("; ")
        .find((c) => c.startsWith("XSRF-TOKEN="))
        ?.split("=")[1];

      const res = await fetch("/login", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-XSRF-TOKEN": decodeURIComponent(xsrfToken || ""),
          Accept: "application/json",
        },
        credentials: "include",
        body: JSON.stringify({ email, password }),
      });

      if (res.ok) {
        // Fetch current user details to redirect to the correct portal
        try {
          const meRes = await fetch("/api/v1/me", {
            credentials: "include",
            headers: { Accept: "application/json" },
          });
          if (meRes.ok) {
            const meData = await meRes.json();
            const roles: string[] = meData?.data?.roles || [];
            const target = getDefaultPortalForRoles(roles);
            router.push(target);
            return;
          }
        } catch {
          // fallback redirect if /me fails
        }
        router.push("/admin/dashboard");
      } else if (res.status === 422) {
        const data = await res.json();
        setError(data.message || "Invalid credentials");
      } else if (res.status === 429) {
        setError("Too many login attempts. Please try again in 1 minute.");
      } else {
        setError("Login failed");
      }
    } catch {
      setError("Network error");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-[#090909]">
      <div className="w-full max-w-md p-8">
        <div className="flex justify-center mb-8">
          <Image
            src="/images/brand/logo-white.png"
            alt="BDJG"
            width={120}
            height={40}
            priority
          />
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label
              htmlFor="email"
              className="block text-xs uppercase tracking-wider text-[#A3A3A3] mb-1"
            >
              Email
            </label>
            <input
              id="email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              className="w-full px-3 py-2 bg-[#171717] border border-[#2A2A2A] rounded-lg text-[#F2F1ED] focus:outline-none focus:border-[#5C7CFF]"
            />
          </div>

          <div>
            <label
              htmlFor="password"
              className="block text-xs uppercase tracking-wider text-[#A3A3A3] mb-1"
            >
              Password
            </label>
            <input
              id="password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              className="w-full px-3 py-2 bg-[#171717] border border-[#2A2A2A] rounded-lg text-[#F2F1ED] focus:outline-none focus:border-[#5C7CFF]"
            />
          </div>

          {error && (
            <p className="text-sm text-red-400">{error}</p>
          )}

          <button
            type="submit"
            disabled={loading}
            className="w-full py-2 bg-[#5C7CFF] text-white rounded-lg font-medium disabled:opacity-50 hover:opacity-90 transition"
          >
            {loading ? "Signing in..." : "Sign in"}
          </button>
        </form>

        <p className="text-xs text-[#6B6B6B] text-center mt-6 space-x-3">
          <a href="/register" className="hover:text-[#A3A3A3]">
            Create account
          </a>
          <span>·</span>
          <a href="/forgot-password" className="hover:text-[#A3A3A3]">
            Forgot password?
          </a>
        </p>
      </div>
    </div>
  );
}
