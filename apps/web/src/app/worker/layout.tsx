"use client";

import { useAuth } from "@/lib/use-auth";
import DashboardShell from "@/components/dashboard-shell";

const NAV = [
  { href: "/worker/dashboard", label: "Dashboard", icon: "📊" },
  { href: "/worker/projects", label: "My Projects", icon: "🎬" },
  { href: "/worker/tasks", label: "My Tasks", icon: "✅" },
  { href: "/worker/schedule", label: "Schedule", icon: "📅" },
  { href: "/worker/revisions", label: "Revisions", icon: "🔁" },
  { href: "/worker/files", label: "Files", icon: "📁" },
  { href: "/worker/availability", label: "Availability", icon: "🗓️" },
  { href: "/worker/expenses", label: "Expense Claims", icon: "🧾" },
];

export default function WorkerLayout({ children }: { children: React.ReactNode }) {
  const { user, loading } = useAuth();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center" style={{ background: "var(--bg)" }}>
        <span className="text-sm" style={{ color: "var(--muted)" }}>Loading...</span>
      </div>
    );
  }

  if (!user) return null;

  return (
    <DashboardShell
      role="worker"
      nav={NAV}
      title="Worker Workspace"
      userName={user.name}
      initial={user.name.charAt(0).toUpperCase()}
    >
      {children}
    </DashboardShell>
  );
}
