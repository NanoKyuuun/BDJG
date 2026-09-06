"use client";

import { useEffect, useState } from "react";
import { useAuth } from "@/lib/use-auth";
import { fetchApi } from "@/lib/api";

interface ClientProject {
  id: number;
  public_id: string;
  project_number: string;
  name: string;
  service_name?: string;
  package_name?: string;
  status: string;
  shoot_date?: string;
  deadline?: string;
  brief?: string;
  schedules?: Array<{
    id: number;
    title: string;
    start_time: string;
    location?: string;
  }>;
}

interface ClientInvoice {
  id: number;
  public_id: string;
  invoice_number: string;
  invoice_type: string;
  amount: number;
  paid_amount: number;
  status: string;
  due_at?: string;
}

interface ClientQuotation {
  id: number;
  public_id: string;
  quotation_number: string;
  status: string;
  current_version?: {
    project_name: string;
    grand_total: number;
    dp_amount: number;
  };
}

function formatRupiah(amount: number): string {
  return "Rp " + (amount || 0).toLocaleString("id-ID");
}

export default function ClientDashboardPage() {
  const { user, loading: authLoading } = useAuth();
  const [projects, setProjects] = useState<ClientProject[]>([]);
  const [invoices, setInvoices] = useState<ClientInvoice[]>([]);
  const [quotations, setQuotations] = useState<ClientQuotation[]>([]);
  const [loading, setLoading] = useState(true);

  const loadData = () => {
    setLoading(true);
    Promise.all([
      fetchApi<{ data: ClientProject[] }>("/api/v1/client/projects").catch(() => ({ data: [] })),
      fetchApi<{ data: ClientInvoice[] }>("/api/v1/client/invoices").catch(() => ({ data: [] })),
      fetchApi<{ data: ClientQuotation[] }>("/api/v1/client/quotations").catch(() => ({ data: [] })),
    ])
      .then(([projRes, invRes, quotRes]) => {
        setProjects(projRes.data || []);
        setInvoices(invRes.data || []);
        setQuotations(quotRes.data || []);
      })
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadData();
  }, []);

  const handlePayInvoice = async (invoiceId: number) => {
    try {
      const res = await fetchApi<{ data: { payment_url?: string; va_number?: string } }>(
        `/api/v1/client/invoices/${invoiceId}/pay`,
        { method: "POST" }
      );

      if (res.data?.payment_url) {
        window.open(res.data.payment_url, "_blank");
      } else if (res.data?.va_number) {
        alert(`Silakan transfer ke Virtual Account: ${res.data.va_number}`);
      } else {
        alert("Transaksi pembayaran berhasil dibuat!");
      }
      loadData();
    } catch (err: any) {
      alert(err.message || "Gagal menginisiasi pembayaran");
    }
  };

  const handleAcceptQuotation = async (quotationId: number) => {
    if (!confirm("Setujui penawaran harga ini?")) return;
    try {
      await fetchApi(`/api/v1/client/quotations/${quotationId}/accept`, { method: "POST" });
      alert("Penawaran berhasil disetujui! Studio akan segera menerbitkan invoice DP.");
      loadData();
    } catch (err: any) {
      alert(err.message || "Gagal menyetujui penawaran");
    }
  };

  if (authLoading || loading) {
    return <div className="empty">Loading dashboard…</div>;
  }

  const payableInvoices = invoices.filter((i) => ["ISSUED", "OVERDUE"].includes(i.status));
  const waitingQuotations = quotations.filter((q) => q.status === "SENT");

  const stats = [
    ["Active Projects", projects.length, "🎬", "go"],
    ["Penawaran Masuk", waitingQuotations.length, "🧾", "gt"],
    ["Tagihan Menunggu", payableInvoices.length, "💳", "gy"],
    ["Total Tagihan Lunas", invoices.filter((i) => i.status === "PAID").length, "✨", "gk"],
  ];

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>
            Halo, {user?.name} <span className="hi">🎉</span>
          </h1>
          <p>Portal Klien BDJG Studio — Pantau progres produksi, penawaran, dan pembayaran Anda</p>
        </div>
      </div>

      <div className="grid g-stats">
        {stats.map((c) => (
          <div key={c[0] as string} className="card stat">
            <div>
              <div className="lb">{c[0] as string}</div>
              <div className="vl">{c[1] as number}</div>
            </div>
            <div className={`ico ${c[3] as string}`}>{c[2] as string}</div>
          </div>
        ))}
      </div>

      {waitingQuotations.length > 0 && (
        <div className="card mb" style={{ marginBottom: 16, border: "1px solid #1B4FD8" }}>
          <div className="chead">
            <h3 style={{ color: "#93C5FD" }}>📄 Penawaran Harga Baru Menunggu Persetujuan</h3>
          </div>
          <div className="cbody">
            {waitingQuotations.map((q) => (
              <div
                key={q.id}
                style={{
                  display: "flex",
                  justifyContent: "space-between",
                  alignItems: "center",
                  padding: 12,
                  background: "rgba(27, 79, 216, 0.1)",
                  borderRadius: 8,
                }}
              >
                <div>
                  <div style={{ fontWeight: 700, fontSize: 14, color: "#FFF" }}>
                    {q.quotation_number} — {q.current_version?.project_name}
                  </div>
                  <div style={{ fontSize: 12, color: "#CBD5E1", marginTop: 2 }}>
                    Grand Total: {formatRupiah(q.current_version?.grand_total || 0)} • DP 50%:{" "}
                    {formatRupiah(q.current_version?.dp_amount || 0)}
                  </div>
                </div>
                <div style={{ display: "flex", gap: 8 }}>
                  <button className="btn btn-p sm" onClick={() => handleAcceptQuotation(q.id)}>
                    ✓ Setujui Penawaran
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      <div className="grid g-21 mt">
        <div>
          {projects.length > 0 ? (
            projects.map((p) => {
              const payable = payableInvoices[0];
              return (
                <div key={p.id} className="card" style={{ padding: 18, marginBottom: 16 }}>
                  <div style={{ display: "flex", gap: 14, alignItems: "center" }}>
                    <div
                      className="thumb lg"
                      style={{ background: "linear-gradient(135deg,#1B4FD8,#4F83FF)" }}
                    />
                    <div style={{ flex: 1 }}>
                      <b style={{ fontFamily: "Sora, sans-serif", fontSize: 15, color: "#FFF" }}>
                        {p.name}
                      </b>
                      <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 2 }}>
                        {p.project_number} • {p.service_name || "Layanan Video/Foto"} • {p.package_name || "Custom Package"}
                      </div>
                    </div>
                    <span className="chip c-blue" style={{ fontSize: 11, fontWeight: 700 }}>
                      {p.status}
                    </span>
                  </div>

                  {p.brief && (
                    <p style={{ fontSize: 12, color: "#CBD5E1", marginTop: 12, background: "rgba(255,255,255,0.02)", padding: 10, borderRadius: 6 }}>
                      📝 <b>Brief Produksi:</b> {p.brief}
                    </p>
                  )}

                  <div className="krow" style={{ marginTop: 14 }}>
                    {payable && (
                      <button className="btn btn-o sm" onClick={() => handlePayInvoice(payable.id)}>
                        💳 Bayar Invoice {payable.invoice_number} ({formatRupiah(payable.amount - payable.paid_amount)})
                      </button>
                    )}
                  </div>
                </div>
              );
            })
          ) : (
            <div className="card">
              <div className="cbody">
                <div className="empty">Belum ada proyek aktif. Silakan hubungi tim studio untuk memulai proyek baru.</div>
              </div>
            </div>
          )}
        </div>

        <div className="card">
          <div className="chead">
            <h3>💳 Riwayat Invoice Tagihan</h3>
          </div>
          <div className="cbody" style={{ padding: 0 }}>
            {invoices.length > 0 ? (
              <div style={{ padding: 12, display: "flex", flexDirection: "column", gap: 10 }}>
                {invoices.map((inv) => (
                  <div
                    key={inv.id}
                    style={{
                      display: "flex",
                      justifyContent: "space-between",
                      alignItems: "center",
                      padding: 10,
                      background: "rgba(255,255,255,0.03)",
                      borderRadius: 6,
                      border: "1px solid rgba(255,255,255,0.06)",
                    }}
                  >
                    <div>
                      <div style={{ fontWeight: 700, fontSize: 12, color: "#F97316" }}>
                        {inv.invoice_number}
                      </div>
                      <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 2 }}>
                        {inv.invoice_type} • {formatRupiah(inv.amount)}
                      </div>
                    </div>
                    <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
                      <span
                        className={`chip ${inv.status === "PAID" ? "c-green" : "c-blue"}`}
                        style={{ fontSize: 10, fontWeight: 700 }}
                      >
                        {inv.status}
                      </span>
                      {inv.status !== "PAID" && (
                        <button className="btn btn-x sm" onClick={() => handlePayInvoice(inv.id)}>
                          Bayar
                        </button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="empty">Belum ada tagihan invoice.</div>
            )}
          </div>
        </div>
      </div>
    </>
  );
}
