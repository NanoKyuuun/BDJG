"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Image from "next/image";

export default function RegisterPage() {
  const router = useRouter();
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

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

      const res = await fetch("/register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-XSRF-TOKEN": decodeURIComponent(xsrfToken || ""),
          Accept: "application/json",
        },
        credentials: "include",
        body: JSON.stringify({
          name,
          email,
          password,
          password_confirmation: passwordConfirmation,
        }),
      });

      if (res.ok) {
        router.push("/client/dashboard");
      } else if (res.status === 422) {
        const data = await res.json();
        setError(data.message || "Registration failed");
      } else {
        setError("Registration failed");
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
              htmlFor="name"
              className="block text-xs uppercase tracking-wider text-[#A3A3A3] mb-1"
            >
              Name
            </label>
            <input
              id="name"
              type="text"
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
              className="w-full px-3 py-2 bg-[#171717] border border-[#2A2A2A] rounded-lg text-[#F2F1ED] focus:outline-none focus:border-[#5C7CFF]"
            />
          </div>

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

          <div>
            <label
              htmlFor="password_confirmation"
              className="block text-xs uppercase tracking-wider text-[#A3A3A3] mb-1"
            >
              Confirm Password
            </label>
            <input
              id="password_confirmation"
              type="password"
              value={passwordConfirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
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
            {loading ? "Creating account..." : "Create account"}
          </button>
        </form>

        <p className="text-xs text-[#6B6B6B] text-center mt-6">
          Already have an account?{" "}
          <a href="/login" className="hover:text-[#A3A3A3]">
            Sign in
          </a>
        </p>
      </div>
    </div>
  );
}
