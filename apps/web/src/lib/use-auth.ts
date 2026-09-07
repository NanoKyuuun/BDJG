"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";

export interface AuthUser {
  id: string;
  name: string;
  email: string;
  phone?: string | null;
  status: string;
  roles: string[];
  permissions?: string[];
}

export function getDefaultPortalForRoles(roles: string[]): string {
  if (roles.includes("OWNER") || roles.includes("ADMIN")) {
    return "/admin/dashboard";
  }
  if (roles.includes("WORKER")) {
    return "/worker/dashboard";
  }
  if (roles.includes("CLIENT")) {
    return "/client/dashboard";
  }
  return "/login";
}

export function useAuth(requiredRoles?: string[]) {
  const router = useRouter();
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loading, setLoading] = useState(true);
  const [authorized, setAuthorized] = useState(false);

  useEffect(() => {
    let isMounted = true;

    async function checkAuth() {
      try {
        const res = await fetch("/api/v1/me", {
          credentials: "include",
          headers: { Accept: "application/json" },
        });

        if (!res.ok) {
          if (isMounted) {
            setUser(null);
            setAuthorized(false);
            setLoading(false);
            router.replace("/login");
          }
          return;
        }

        const data = await res.json();
        const userData: AuthUser = data?.data;

        if (!userData) {
          if (isMounted) {
            setUser(null);
            setAuthorized(false);
            setLoading(false);
            router.replace("/login");
          }
          return;
        }

        const userRoles = userData.roles || [];
        const hasRequiredRole =
          !requiredRoles ||
          requiredRoles.length === 0 ||
          userRoles.some((role) => requiredRoles.includes(role));

        if (!hasRequiredRole) {
          // User is authenticated, but not authorized for this portal section.
          // Redirect them to their designated home portal.
          const targetPortal = getDefaultPortalForRoles(userRoles);
          if (isMounted) {
            setUser(userData);
            setAuthorized(false);
            setLoading(false);
            router.replace(targetPortal);
          }
          return;
        }

        if (isMounted) {
          setUser(userData);
          setAuthorized(true);
          setLoading(false);
        }
      } catch {
        if (isMounted) {
          setUser(null);
          setAuthorized(false);
          setLoading(false);
          router.replace("/login");
        }
      }
    }

    checkAuth();

    return () => {
      isMounted = false;
    };
  }, [router, JSON.stringify(requiredRoles)]);

  return { user, loading, authorized };
}
