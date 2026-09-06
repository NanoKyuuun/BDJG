"use client";

import { useEffect, useState } from "react";
import { useAuth } from "@/lib/use-auth";
import { DashboardMetrics, getDashboardMetrics } from "@/lib/api";

function formatRupiah(amount: number): string {
  return "Rp " + amount.toLocaleString("id-ID");
}

function RevenueChart({ type }: { type: "bar" | "line" }) {
  const data = [
    { l: "Jan", v: 42 },
    { l: "Feb", v: 38 },
    { l: "Mar", v: 55 },
    { l: "Apr", v: 47 },
    { l: "Mei", v: 62 },
    { l: "Jun", v: 58 },
    { l: "Jul", v: 71 },
    { l: "Agu", v: 66 },
    { l: "Sep", v: 86 },
  ];
  const max = 95;
  const yTicks = [90, 80, 70, 60, 50, 40, 30, 20, 10, 0];

  return (
    <div style={{ position: "relative", height: 260, display: "flex", flexDirection: "column" }}>
      {/* Grid and Bars Area */}
      <div style={{ position: "relative", flex: 1, display: "flex" }}>
        {/* Y Axis Labels */}
        <div
          style={{
            width: 38,
            display: "flex",
            flexDirection: "column",
            justifyContent: "space-between",
            paddingBottom: 22,
            fontSize: 10.5,
            color: "#9BA0AE",
            textAlign: "right",
            paddingRight: 8,
            fontWeight: 600,
          }}
        >
          {yTicks.map((t) => (
            <span key={t}>{t}jt</span>
          ))}
        </div>

        {/* Chart Canvas Area */}
        <div style={{ flex: 1, position: "relative", display: "flex", flexDirection: "column" }}>
          {/* Horizontal Grid lines */}
          <div
            style={{
              position: "absolute",
              inset: 0,
              bottom: 22,
              display: "flex",
              flexDirection: "column",
              justifyContent: "space-between",
              pointerEvents: "none",
            }}
          >
            {yTicks.map((t) => (
              <div
                key={t}
                style={{
                  width: "100%",
                  height: 1,
                  background: "rgba(255, 255, 255, 0.06)",
                }}
              />
            ))}
          </div>

          {/* Bar Chart Mode */}
          {type === "bar" ? (
            <div
              style={{
                position: "absolute",
                inset: 0,
                bottom: 22,
                display: "flex",
                alignItems: "flex-end",
                justifyContent: "space-around",
                paddingLeft: 4,
                paddingRight: 4,
              }}
            >
              {data.map((d) => {
                const heightPct = (d.v / max) * 100;
                return (
                  <div
                    key={d.l}
                    style={{
                      display: "flex",
                      flexDirection: "column",
                      alignItems: "center",
                      height: "100%",
                      justifyContent: "flex-end",
                      width: 44,
                    }}
                  >
                    <div
                      title={`${d.l}: ${d.v} jt`}
                      style={{
                        width: 32,
                        height: `${heightPct}%`,
                        background: "#000000",
                        border: "2px solid #F97316",
                        borderRadius: 9,
                        boxShadow: "0 4px 14px rgba(249, 115, 22, 0.2)",
                        transition: "all 0.3s ease",
                      }}
                    />
                  </div>
                );
              })}
            </div>
          ) : (
            /* Line Chart Mode */
            <div
              style={{
                position: "absolute",
                inset: 0,
                bottom: 22,
              }}
            >
              <svg
                viewBox="0 0 700 200"
                preserveAspectRatio="none"
                style={{ width: "100%", height: "100%", overflow: "visible" }}
              >
                <defs>
                  <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stopColor="#F97316" stopOpacity="0.3" />
                    <stop offset="100%" stopColor="#F97316" stopOpacity="0.0" />
                  </linearGradient>
                </defs>
                <path
                  d="M 20 120 Q 95 130, 105 125 T 190 90 T 275 110 T 360 70 T 445 80 T 530 50 T 615 65 T 680 20 L 680 200 L 20 200 Z"
                  fill="url(#areaGrad)"
                />
                <path
                  d="M 20 120 Q 95 130, 105 125 T 190 90 T 275 110 T 360 70 T 445 80 T 530 50 T 615 65 T 680 20"
                  fill="none"
                  stroke="#F97316"
                  strokeWidth="2.5"
                />
                {[
                  [20, 120],
                  [105, 125],
                  [190, 90],
                  [275, 110],
                  [360, 70],
                  [445, 80],
                  [530, 50],
                  [615, 65],
                  [680, 20],
                ].map(([cx, cy], i) => (
                  <circle
                    key={i}
                    cx={cx}
                    cy={cy}
                    r="4.5"
                    fill="#FFC700"
                    stroke="#101013"
                    strokeWidth="2"
                  />
                ))}
              </svg>
            </div>
          )}

          {/* X Axis Labels */}
          <div
            style={{
              position: "absolute",
              bottom: 0,
              left: 0,
              right: 0,
              height: 20,
              display: "flex",
              justifyContent: "space-around",
              fontSize: 11,
              fontWeight: 600,
              color: "#9BA0AE",
            }}
          >
            {data.map((d) => (
              <span key={d.l} style={{ width: 44, textAlign: "center" }}>
                {d.l}
              </span>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

function ServiceDonutChart() {
  const slices = [
    { label: "Wedding", pct: 34, color: "#1B4FD8" },
    { label: "Graduation", pct: 26, color: "#F97316" },
    { label: "Commercial", pct: 35, color: "#FFC700" },
    { label: "Photo", pct: 12, color: "#000000" },
    { label: "Post", pct: 8, color: "#0D9488" },
  ];

  return (
    <div style={{ display: "flex", flexDirection: "column", alignItems: "center", height: 220, justifyContent: "space-between" }}>
      <div style={{ position: "relative", width: 140, height: 140 }}>
        <svg viewBox="0 0 36 36" style={{ width: "100%", height: "100%", transform: "rotate(-90deg)" }}>
          <circle cx="18" cy="18" r="14" fill="none" stroke="#1B4FD8" strokeWidth="6" strokeDasharray="34 66" strokeDashoffset="0" />
          <circle cx="18" cy="18" r="14" fill="none" stroke="#F97316" strokeWidth="6" strokeDasharray="26 74" strokeDashoffset="-34" />
          <circle cx="18" cy="18" r="14" fill="none" stroke="#FFC700" strokeWidth="6" strokeDasharray="20 80" strokeDashoffset="-60" />
          <circle cx="18" cy="18" r="14" fill="none" stroke="#26262A" strokeWidth="6" strokeDasharray="12 88" strokeDashoffset="-80" />
          <circle cx="18" cy="18" r="14" fill="none" stroke="#0D9488" strokeWidth="6" strokeDasharray="8 92" strokeDashoffset="-92" />
        </svg>
      </div>

      <div style={{ display: "flex", gap: 10, flexWrap: "wrap", justifyContent: "center", fontSize: 11, fontWeight: 600, color: "var(--muted)" }}>
        {slices.map((s) => (
          <span key={s.label} style={{ display: "inline-flex", alignItems: "center", gap: 5 }}>
            <span
              style={{
                width: 8,
                height: 8,
                borderRadius: "50%",
                background: s.color,
                border: s.color === "#000000" ? "1px solid rgba(255,255,255,0.3)" : "none",
              }}
            />
            {s.label}
          </span>
        ))}
      </div>
    </div>
  );
}

export default function AdminDashboardPage() {
  const { user, loading: authLoading } = useAuth();
  const [chartType, setChartType] = useState<"bar" | "line">("bar");
  const [metrics, setMetrics] = useState<DashboardMetrics | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getDashboardMetrics()
      .then((data) => setMetrics(data))
      .catch((err) => console.error("Failed to fetch dashboard metrics:", err))
      .finally(() => setLoading(false));
  }, []);

  if (authLoading || loading) {
    return <div className="empty">Loading dashboard…</div>;
  }

  const s = metrics?.summary || {
    new_inquiries: 0,
    quotations_waiting: 0,
    quotations_won: 0,
    active_projects: 0,
    invoices_due: 0,
    today_shoots: 0,
  };

  const f = metrics?.finance || {
    cash_received: 0,
    outstanding_amount: 0,
    currency: "IDR",
  };

  const cards = [
    ["New Inquiries", s.new_inquiries, "📥", "gk", "Perlu respons cepat", "up", false],
    ["Quotation Waiting", s.quotations_waiting, "🧾", "gv", "Perlu follow-up", "wr", false],
    ["Active Projects", s.active_projects, "🎬", "go", "Tahap produksi aktif", "wr", false],
    ["Quotations Won", s.quotations_won, "✨", "gt", "Deals closing bulan ini", "up", false],
    ["Invoices Due", s.invoices_due, "💳", "gg", "Menunggu pelunasan", "dn", false],
    ["Today Shoots", s.today_shoots, "🎥", "go", "Jadwal produksi hari ini", "up", false],
    ["Outstanding Billing", f.outstanding_amount, "⏳", "gy", "Tagihan belum lunas", "wr", true],
    ["Total Cash Received", f.cash_received, "📈", "gk", "Diverifikasi Duitku", "up", true],
  ];

  const recentProjects = metrics?.recent_projects || [];
  const upcomingSchedules = metrics?.upcoming_schedules || [];

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>
            Halo, {user?.name || "BDJG Team"} <span className="hi">👋</span>
          </h1>
          <p>BDJG Creative Studio — Operational Overview & Live Metrics</p>
        </div>
        <div style={{ display: "flex", gap: 9 }}>
          <button className="btn btn-o">+ Inquiry Lead</button>
          <button className="btn btn-p">+ Buat Quotation</button>
        </div>
      </div>

      <div className="grid g-stats">
        {cards.map((c) => (
          <div key={c[0] as string} className="card stat">
            <div>
              <div className="lb">{c[0] as string}</div>
              <div className="vl">
                {c[6] ? formatRupiah(c[1] as number) : (c[1] as number)}
              </div>
              <div className={`dl ${c[5]}`}>{c[4] as string}</div>
            </div>
            <div className={`ico ${c[3]}`}>{c[2] as string}</div>
          </div>
        ))}
      </div>

      <div className="grid g-21 mt">
        <div className="card">
          <div className="chead">
            <h3>📈 Revenue Trend 2026</h3>
            <div style={{ display: "flex", gap: 6 }}>
              <button
                className={`fchip ${chartType === "bar" ? "on" : ""}`}
                onClick={() => setChartType("bar")}
              >
                Bar
              </button>
              <button
                className={`fchip ${chartType === "line" ? "on" : ""}`}
                onClick={() => setChartType("line")}
              >
                Line
              </button>
            </div>
          </div>
          <div className="cbody">
            <RevenueChart type={chartType} />
          </div>
        </div>

        <div className="card">
          <div className="chead">
            <h3>🎬 Active Projects Pipeline</h3>
          </div>
          <div className="cbody">
            {recentProjects.length > 0 ? (
              <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
                {recentProjects.map((p) => (
                  <div
                    key={p.id}
                    style={{
                      display: "flex",
                      justifyContent: "space-between",
                      alignItems: "center",
                      padding: "10px 12px",
                      background: "rgba(255,255,255,0.03)",
                      borderRadius: 8,
                      border: "1px solid rgba(255,255,255,0.06)",
                    }}
                  >
                    <div>
                      <div style={{ fontWeight: 700, fontSize: 13, color: "#FFFFFF" }}>
                        {p.project_number} — {p.name}
                      </div>
                      <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 2 }}>
                        Client: {p.client?.display_name || "Internal"} • Value: {formatRupiah(p.contract_value)}
                      </div>
                    </div>
                    <span className="chip c-blue" style={{ fontSize: 11, fontWeight: 700 }}>
                      {p.status}
                    </span>
                  </div>
                ))}
              </div>
            ) : (
              <div className="empty">Belum ada proyek aktif</div>
            )}
          </div>
        </div>
      </div>

      <div className="grid g-31 mt">
        <div className="card">
          <div className="chead">
            <h3>💠 Revenue by Service</h3>
          </div>
          <div className="cbody">
            <ServiceDonutChart />
          </div>
        </div>

        <div className="card">
          <div className="chead">
            <h3>📅 Upcoming Production Schedules</h3>
          </div>
          <div className="cbody">
            {upcomingSchedules.length > 0 ? (
              upcomingSchedules.map((e) => (
                <div key={e.id} className="ev ev-SHOOT" style={{ marginBottom: 8 }}>
                  <div style={{ fontWeight: 700 }}>{e.title}</div>
                  <small>
                    {new Date(e.start_time).toLocaleString("id-ID", {
                      weekday: "short",
                      day: "numeric",
                      month: "short",
                      hour: "2-digit",
                      minute: "2-digit",
                    })}
                    {e.location ? ` • ${e.location}` : ""}
                  </small>
                </div>
              ))
            ) : (
              <div className="empty">Tidak ada jadwal syuting terdekat</div>
            )}
          </div>
        </div>

        <div className="card">
          <div className="chead">
            <h3>💳 Verified Payments & Invoices</h3>
          </div>
          <div className="cbody">
            <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <div style={{ padding: "12px", background: "rgba(13, 148, 136, 0.1)", borderRadius: 8, border: "1px solid rgba(13, 148, 136, 0.2)" }}>
                <div style={{ fontSize: 11, fontWeight: 600, color: "#14B8A6" }}>TOTAL REVENUE RECEIVED</div>
                <div style={{ fontSize: 20, fontWeight: 800, color: "#FFFFFF", marginTop: 4 }}>
                  {formatRupiah(f.cash_received)}
                </div>
                <div style={{ fontSize: 10.5, color: "#9BA0AE", marginTop: 2 }}>Verified by Duitku Callback</div>
              </div>

              <div style={{ padding: "12px", background: "rgba(249, 115, 22, 0.1)", borderRadius: 8, border: "1px solid rgba(249, 115, 22, 0.2)" }}>
                <div style={{ fontSize: 11, fontWeight: 600, color: "#F97316" }}>OUTSTANDING RECEIVABLES</div>
                <div style={{ fontSize: 20, fontWeight: 800, color: "#FFFFFF", marginTop: 4 }}>
                  {formatRupiah(f.outstanding_amount)}
                </div>
                <div style={{ fontSize: 10.5, color: "#9BA0AE", marginTop: 2 }}>{s.invoices_due} invoice awaiting payment</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
