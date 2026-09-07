export interface ApiResponse<T> {
  data: T;
  meta?: {
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
  };
}

export interface DashboardMetrics {
  summary: {
    new_inquiries: number;
    quotations_waiting: number;
    quotations_won: number;
    active_projects: number;
    invoices_due: number;
    today_shoots: number;
  };
  finance: {
    cash_received: number;
    outstanding_amount: number;
    currency: string;
  };
  recent_projects: Array<{
    id: number;
    project_number: string;
    name: string;
    client?: {
      display_name: string;
      company_name?: string;
    };
    status: string;
    shoot_date?: string;
    deadline?: string;
    contract_value: number;
  }>;
  upcoming_schedules: Array<{
    id: number;
    title: string;
    schedule_type: string;
    start_time: string;
    location?: string;
    project_number?: string;
    project_name?: string;
  }>;
}

function getXsrfToken(): string | null {
  if (typeof document === "undefined") return null;
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : null;
}

export async function fetchApi<T>(path: string, options: RequestInit = {}): Promise<T> {
  const method = (options.method || "GET").toUpperCase();
  const isMutation = ["POST", "PUT", "PATCH", "DELETE"].includes(method);

  const headers: Record<string, string> = {
    Accept: "application/json",
    "Content-Type": "application/json",
    ...(options.headers as Record<string, string> || {}),
  };

  if (isMutation && typeof window !== "undefined") {
    let token = getXsrfToken();
    if (!token) {
      // Initialize CSRF cookie if not already set
      try {
        await fetch("/sanctum/csrf-cookie", { credentials: "include" });
        token = getXsrfToken();
      } catch {
        // Continue and let server validate
      }
    }
    if (token) {
      headers["X-XSRF-TOKEN"] = token;
    }
  }

  let res = await fetch(path, {
    ...options,
    credentials: "include",
    headers,
  });

  // Handle CSRF expiration (419) with one automatic retry
  if (res.status === 419 && isMutation && typeof window !== "undefined") {
    try {
      await fetch("/sanctum/csrf-cookie", { credentials: "include" });
      const refreshedToken = getXsrfToken();
      if (refreshedToken) {
        headers["X-XSRF-TOKEN"] = refreshedToken;
      }
      res = await fetch(path, {
        ...options,
        credentials: "include",
        headers,
      });
    } catch {
      // fallback to original error handling
    }
  }

  if (!res.ok) {
    const errorData = await res.json().catch(() => ({ message: res.statusText }));
    throw new Error(errorData.message || `API Error: ${res.status}`);
  }

  return res.json();
}

export async function getDashboardMetrics(): Promise<DashboardMetrics> {
  return fetchApi<DashboardMetrics>("/api/v1/admin/dashboard/metrics");
}
