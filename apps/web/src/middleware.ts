import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

const API = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000";

// ponytail: Next.js rewrites don't forward Set-Cookie properly for Sanctum SPA.
// Middleware does. This proxies auth + API routes to Laravel with full cookie flow.

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;

  const isApiRoute = pathname.startsWith("/api/");
  const isSanctumRoute = pathname.startsWith("/sanctum/");
  const isFortifyRoute = ["/login", "/logout", "/register", "/forgot-password", "/reset-password"].some(
    (r) => pathname === r
  );

  // Keep Next.js responsible for rendering auth pages, while sending their
  // state-changing Fortify requests to Laravel so its session cookie is set.
  if (!isApiRoute && !isSanctumRoute && !isFortifyRoute) {
    return NextResponse.next();
  }

  // ponytail: Fortify POST/PUT/DELETE → proxy to Laravel. GET → Next.js page.
  if (isFortifyRoute && (request.method === "GET" || request.method === "HEAD")) {
    return NextResponse.next();
  }

  const url = new URL(pathname + request.nextUrl.search, API);

  const headers = new Headers();
  // Forward all headers except host
  request.headers.forEach((value, key) => {
    if (key.toLowerCase() !== "host") {
      headers.set(key, value);
    }
  });

  headers.set("x-forwarded-host", request.headers.get("host") || "localhost:3000");
  headers.set("x-forwarded-proto", request.nextUrl.protocol.replace(":", "") || "http");
  headers.set("x-requested-with", "XMLHttpRequest");
  if (!headers.has("accept") || headers.get("accept") === "*/*") {
    headers.set("accept", "application/json");
  }

  return fetch(url.toString(), {
    method: request.method,
    headers,
    body: request.method !== "GET" && request.method !== "HEAD" ? request.body : undefined,
    redirect: "manual",
  }).then((upstream) => {
    const response = new NextResponse(upstream.body, {
      status: upstream.status,
      statusText: upstream.statusText,
    });

    // Forward response headers. Set-Cookie appears once per cookie; set()
    // overwrites, so only the last cookie would survive. Append each instead.
    upstream.headers.forEach((value, key) => {
      if (key.toLowerCase() !== "set-cookie") {
        response.headers.set(key, value);
      }
    });
    for (const cookie of upstream.headers.getSetCookie()) {
      response.headers.append("set-cookie", cookie);
    }

    // Rewrite Location redirects back to frontend
    const location = response.headers.get("location");
    if (location) {
      try {
        const loc = new URL(location);
        if (loc.hostname === API.replace(/^https?:\/\//, "").split(":")[0]) {
          response.headers.set(
            "location",
            location.replace(API, "")
          );
        }
      } catch {
        // not a URL, leave as-is
      }
    }

    return response;
  });
}

export const config = {
  matcher: ["/api/:path*", "/sanctum/:path*", "/login", "/logout", "/register", "/forgot-password", "/reset-password"],
};
