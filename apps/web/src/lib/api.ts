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

export async function fetchApi<T>(path: string, options: RequestInit = {}): Promise<T> {
  const res = await fetch(path, {
    ...options,
    credentials: "include",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(options.headers || {}),
    },
  });

  if (!res.ok) {
    const errorData = await res.json().catch(() => ({ message: res.statusText }));
    throw new Error(errorData.message || `API Error: ${res.status}`);
  }

  return res.json();
}

export async function getDashboardMetrics(): Promise<DashboardMetrics> {
  return fetchApi<DashboardMetrics>("/api/v1/admin/dashboard/metrics");
}
