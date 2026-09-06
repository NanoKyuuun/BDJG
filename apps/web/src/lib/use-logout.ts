"use client";

import { useRouter } from "next/navigation";

const API_URL = process.env.NEXT_PUBLIC_API_URL!;

export function useLogout() {
  const router = useRouter();

  async function logout() {
    try {
      await fetch("/sanctum/csrf-cookie", { credentials: "include" });

      const xsrf = document.cookie
        .split("; ")
        .find((c) => c.startsWith("XSRF-TOKEN="))
        ?.split("=")[1];

      await fetch("/logout", {
        method: "POST",
        headers: {
          Accept: "application/json",
          "X-XSRF-TOKEN": decodeURIComponent(xsrf ?? ""),
        },
        credentials: "include",
      });
    } finally {
      router.push("/login");
    }
  }

  return logout;
}
