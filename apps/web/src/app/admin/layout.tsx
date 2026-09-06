"use client";

import { useAuth } from "@/lib/use-auth";
import DashboardShell from "@/components/dashboard-shell";

const NAV = [
  { href: "/admin/dashboard", label: "Dashboard", icon: "📊" },
  { href: "/admin/sales/inquiries", label: "Inquiries", icon: "📥", group: "SALES" },
  { href: "/admin/sales/quotations", label: "Quotations", icon: "🧾" },
  { href: "/admin/sales/clients", label: "Clients", icon: "👥" },
  { href: "/admin/projects", label: "All Projects", icon: "🎬", group: "PROJECTS" },
  { href: "/admin/projects/board", label: "Production Board", icon: "🗂️" },
  { href: "/admin/projects/calendar", label: "Calendar", icon: "📅" },
  { href: "/admin/projects/revisions", label: "Revisions", icon: "🔁" },
  { href: "/admin/team/workers", label: "Workers", icon: "🧑‍🎨", group: "TEAM" },
  { href: "/admin/finance", label: "Finance", icon: "💰", group: "FINANCE" },
  { href: "/admin/content/portfolio", label: "Portfolio", icon: "🖼️", group: "CONTENT" },
  { href: "/admin/content/services", label: "Services & Packages", icon: "📦" },
  { href: "/admin/content/testimonials", label: "Testimonials", icon: "⭐" },
  { href: "/admin/messages", label: "Messages", icon: "💬", group: "COMMUNICATION" },
  { href: "/admin/templates", label: "Templates", icon: "📨" },
  { href: "/admin/system/users", label: "Users & Roles", icon: "🛡️", group: "SYSTEM" },
  { href: "/admin/system/activity", label: "Activity Log", icon: "🕘" },
  { href: "/admin/system/settings", label: "Settings", icon: "⚙️" },
];

export default function AdminLayout({ children }: { children: React.ReactNode }) {
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
      role="admin"
      nav={NAV}
      title="Dashboard"
      userName={user.name}
      initial={user.name.charAt(0).toUpperCase()}
    >
      {children}
    </DashboardShell>
  );
}
