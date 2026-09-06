"use client";

import { useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import "@/styles/dashboard.css";
import { NOTIFICATIONS, ROLE_META, type Role } from "@/lib/demo-data";
import { useLogout } from "@/lib/use-logout";
import NotificationBell from "@/components/notification-bell";

export interface NavItem {
  href: string;
  label: string;
  icon: string;
  group?: string;
}

function Logo() {
  return (
    <Link href="#" className="logo" aria-label="BDJG Studio OS">
      <svg className="lsvg" viewBox="0 0 64 40">
        <defs>
          <linearGradient id="bdjg-lg" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0" stopColor="#F97316" />
            <stop offset=".5" stopColor="#FFC700" />
            <stop offset="1" stopColor="#8AB6FF" />
          </linearGradient>
        </defs>
        <ellipse
          cx="32"
          cy="20"
          rx="27"
          ry="13"
          fill="none"
          stroke="url(#bdjg-lg)"
          strokeWidth="3.5"
          strokeLinecap="round"
          strokeDasharray="118 26"
          transform="rotate(-12 32 20)"
        />
      </svg>
      <div className="ltxt">
        BDJG<b>STUDIO OS</b>
      </div>
    </Link>
  );
}

function Sidebar({
  role,
  nav,
  pathname,
  open,
  onClose,
}: {
  role: Role;
  nav: NavItem[];
  pathname: string;
  open: boolean;
  onClose: () => void;
}) {
  const sections: { group?: string; items: NavItem[] }[] = [];
  nav.forEach((item) => {
    const last = sections[sections.length - 1];
    if (item.group && (!last || last.group !== item.group)) {
      sections.push({ group: item.group, items: [item] });
    } else if (item.group && last) {
      last.items.push(item);
    } else if (last && !last.group) {
      last.items.push(item);
    } else {
      sections.push({ items: [item] });
    }
  });

  return (
    <aside className={`sidebar ${open ? "open" : ""}`} aria-label="Sidebar">
      <Logo />
      <nav style={{ flex: 1 }}>
        {sections.map((sec, si) => (
          <div key={si}>
            {sec.group && <div className="navgrp">{sec.group}</div>}
            {sec.items.map((item) => {
              const active = pathname === item.href;
              return (
                <Link
                  key={item.href}
                  href={item.href}
                  onClick={onClose}
                  className={`navitem ${active ? "on" : ""}`}
                  aria-current={active ? "page" : undefined}
                >
                  <span className="ni">{item.icon}</span>
                  {item.label}
                </Link>
              );
            })}
          </div>
        ))}
      </nav>
      <div className="sidefoot">
        Baseline <b>v1.0</b> — Photo · Film · Post Production.
        <br />
        Scope akses: <b>{ROLE_META[role].title}</b>.
      </div>
    </aside>
  );
}

export default function DashboardShell({
  role,
  nav,
  title,
  userName,
  initial,
  children,
}: {
  role: Role;
  nav: NavItem[];
  title: string;
  userName: string;
  initial: string;
  children: React.ReactNode;
}) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [notifOpen, setNotifOpen] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const logout = useLogout();
  const pathname = usePathname();
  const unread = NOTIFICATIONS[role].filter((n) => n.unread).length;

  return (
    <div className="bdjg-dashboard bdjg-dashboard-body min-h-screen">
      <Sidebar
        role={role}
        nav={nav}
        pathname={pathname}
        open={sidebarOpen}
        onClose={() => setSidebarOpen(false)}
      />
      <div className="main">
        <header className="topbar">
          <button
            className="iconbtn hamb"
            onClick={() => setSidebarOpen((o) => !o)}
            aria-label="Toggle menu"
          >
            ☰
          </button>
          <div className="ttl sora">{title}</div>
          <div className="spacer" />

          <div className="searchwrap">
            <span className="sic">🔍</span>
            <input
              id="globalSearch"
              placeholder="Cari project, client…"
              autoComplete="off"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
            />
          </div>

          <NotificationBell />

          <div style={{ position: "relative" }}>
            <button
              className="iconbtn"
              onClick={() => {
                setMenuOpen((o) => !o);
                setNotifOpen(false);
              }}
              style={{
                background: "linear-gradient(135deg,var(--orange),var(--yellow))",
                color: "#1A1200",
                fontWeight: 800,
              }}
              aria-label="Account menu"
            >
              {initial}
            </button>
            {menuOpen && (
              <div
                className="panel avmenu show"
                style={{ display: "block", width: 190 }}
              >
                <div className="ph">{userName}</div>
                <button
                  onClick={() => setMenuOpen(false)}
                  style={{
                    display: "flex",
                    gap: 9,
                    width: "100%",
                    padding: "11px 16px",
                    fontSize: "12.5px",
                    fontWeight: 600,
                    textAlign: "left",
                  }}
                >
                  👤 Profile
                </button>
                <button
                  onClick={logout}
                  style={{
                    display: "flex",
                    gap: 9,
                    width: "100%",
                    padding: "11px 16px",
                    fontSize: "12.5px",
                    fontWeight: 600,
                    textAlign: "left",
                  }}
                >
                  🚪 Logout
                </button>
              </div>
            )}
          </div>
        </header>

        <main
          className="view"
          onClick={() => {
            if (sidebarOpen) setSidebarOpen(false);
            if (notifOpen) setNotifOpen(false);
            if (menuOpen) setMenuOpen(false);
          }}
        >
          {children}
        </main>
      </div>
    </div>
  );
}
