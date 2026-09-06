"use client";

import { useAuth } from "@/lib/use-auth";
import DashboardShell from "@/components/dashboard-shell";

const NAV = [
  { href: "/client/dashboard", label: "Dashboard", icon: "📊" },
  { href: "/client/projects", label: "My Projects", icon: "🎬" },
  { href: "/client/quotations", label: "Quotations", icon: "🧾" },
  { href: "/client/invoices", label: "Invoices & Payments", icon: "💳" },
  { href: "/client/schedule", label: "Schedule", icon: "📅" },
  { href: "/client/preview", label: "Preview & Revision", icon: "🎞️" },
  { href: "/client/photos", label: "Photo Selection", icon: "🖼️" },
  { href: "/client/files", label: "Files", icon: "📁" },
  { href: "/client/messages", label: "Messages", icon: "💬" },
];

export default function ClientLayout({ children }: { children: React.ReactNode }) {
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
      role="client"
      nav={NAV}
      title="Client Portal"
      userName={user.name}
      initial={user.name.charAt(0).toUpperCase()}
    >
      {children}
    </DashboardShell>
  );
}
