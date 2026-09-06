"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface InvoiceItem {
  id: number;
  public_id: string;
  invoice_number: string;
  invoice_type: string;
  amount: number;
  paid_amount: number;
  status: string;
  due_at?: string;
  paid_at?: string;
  client?: {
    display_name: string;
  };
  created_at: string;
}

interface PaymentItem {
  id: number;
  merchant_order_id: string;
  provider_reference?: string;
  amount: number;
  payment_method?: string;
  status: string;
  paid_at?: string;
  invoice_id: number;
  created_at: string;
}

function formatRupiah(amount: number): string {
  return "Rp " + (amount || 0).toLocaleString("id-ID");
}

export default function AdminFinancePage() {
  const [invoices, setInvoices] = useState<InvoiceItem[]>([]);
  const [payments, setPayments] = useState<PaymentItem[]>([]);
  const [tab, setTab] = useState<"invoices" | "payments">("invoices");
  const [loading, setLoading] = useState(true);

  const loadData = () => {
    setLoading(true);
    Promise.all([
      fetchApi<{ data: InvoiceItem[] }>("/api/v1/admin/invoices"),
      fetchApi<{ data: PaymentItem[] }>("/api/v1/admin/payments"),
    ])
      .then(([invRes, payRes]) => {
        setInvoices(invRes.data || []);
        setPayments(payRes.data || []);
      })
      .catch((err) => console.error("Failed to load finance data:", err))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadData();
  }, []);

  const handleIssueInvoice = async (invoiceId: number) => {
    try {
      await fetchApi(`/api/v1/admin/invoices/${invoiceId}/issue`, { method: "POST" });
      alert("Invoice berhasil diterbitkan!");
      loadData();
    } catch (err: any) {
      alert(err.message || "Gagal menerbitkan invoice");
    }
  };

  const handleVoidInvoice = async (invoiceId: number) => {
    if (!confirm("Batalkan (VOID) invoice ini?")) return;
    try {
      await fetchApi(`/api/v1/admin/invoices/${invoiceId}/void`, { method: "POST" });
      alert("Invoice berhasil dibatalkan!");
      loadData();
    } catch (err: any) {
      alert(err.message || "Gagal membatalkan invoice");
    }
  };

  const handleCheckPaymentStatus = async (paymentId: number) => {
    try {
      await fetchApi(`/api/v1/admin/payments/${paymentId}/check-status`, { method: "POST" });
      alert("Status pembayaran berhasil diperbarui dari Duitku!");
      loadData();
    } catch (err: any) {
      alert(err.message || "Gagal memeriksa status pembayaran");
    }
  };

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>💰 Billing & Finance Hub</h1>
          <p>Kelola tagihan resmi studio, termin pembayaran, dan integrasi Duitku Sandbox</p>
        </div>
      </div>

      <div style={{ display: "flex", gap: 8, marginBottom: 16 }}>
        <button
          className={`fchip ${tab === "invoices" ? "on" : ""}`}
          onClick={() => setTab("invoices")}
        >
          🧾 Invoices ({invoices.length})
        </button>
        <button
          className={`fchip ${tab === "payments" ? "on" : ""}`}
          onClick={() => setTab("payments")}
        >
          💳 Duitku Transactions ({payments.length})
        </button>
      </div>

      {tab === "invoices" ? (
        <div className="card">
          <div className="chead">
            <h3>Daftar Invoice Tagihan Studio</h3>
          </div>
          <div className="cbody" style={{ padding: 0 }}>
            {loading ? (
              <div className="empty">Memuat data invoice...</div>
            ) : invoices.length === 0 ? (
              <div className="empty">Belum ada invoice diterbitkan.</div>
            ) : (
              <div style={{ overflowX: "auto" }}>
                <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
                  <thead>
                    <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                      <th style={{ padding: "12px 16px" }}>No. Invoice</th>
                      <th style={{ padding: "12px 16px" }}>Klien</th>
                      <th style={{ padding: "12px 16px" }}>Tipe Tagihan</th>
                      <th style={{ padding: "12px 16px" }}>Jumlah Tagihan</th>
                      <th style={{ padding: "12px 16px" }}>Terbayar</th>
                      <th style={{ padding: "12px 16px" }}>Jatuh Tempo</th>
                      <th style={{ padding: "12px 16px" }}>Status</th>
                      <th style={{ padding: "12px 16px", textAlign: "right" }}>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    {invoices.map((inv) => (
                      <tr
                        key={inv.id}
                        style={{
                          borderBottom: "1px solid rgba(255,255,255,0.04)",
                          transition: "background 0.2s",
                        }}
                      >
                        <td style={{ padding: "12px 16px", fontWeight: 700, color: "#F97316" }}>
                          {inv.invoice_number}
                        </td>
                        <td style={{ padding: "12px 16px", fontWeight: 600, color: "#FFF" }}>
                          {inv.client?.display_name || "-"}
                        </td>
                        <td style={{ padding: "12px 16px" }}>
                          <span className="chip c-black" style={{ fontSize: 11, fontWeight: 700 }}>
                            {inv.invoice_type}
                          </span>
                        </td>
                        <td style={{ padding: "12px 16px", fontWeight: 700, color: "#FFFFFF" }}>
                          {formatRupiah(inv.amount)}
                        </td>
                        <td style={{ padding: "12px 16px", color: "#10B981", fontWeight: 700 }}>
                          {formatRupiah(inv.paid_amount)}
                        </td>
                        <td style={{ padding: "12px 16px", fontSize: 12, color: "var(--muted)" }}>
                          {inv.due_at || "-"}
                        </td>
                        <td style={{ padding: "12px 16px" }}>
                          <span
                            className={`chip ${
                              inv.status === "PAID"
                                ? "c-green"
                                : inv.status === "ISSUED"
                                ? "c-blue"
                                : inv.status === "OVERDUE"
                                ? "c-red"
                                : "c-black"
                            }`}
                            style={{ fontSize: 11, fontWeight: 700 }}
                          >
                            {inv.status}
                          </span>
                        </td>
                        <td style={{ padding: "12px 16px", textAlign: "right" }}>
                          <div style={{ display: "flex", gap: 6, justifyContent: "flex-end" }}>
                            {inv.status === "DRAFT" && (
                              <button className="btn btn-p sm" onClick={() => handleIssueInvoice(inv.id)}>
                                Terbitkan
                              </button>
                            )}
                            {inv.status !== "PAID" && inv.status !== "VOID" && (
                              <button className="btn btn-x sm" onClick={() => handleVoidInvoice(inv.id)}>
                                Void
                              </button>
                            )}
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      ) : (
        <div className="card">
          <div className="chead">
            <h3>Riwayat Transaksi Duitku Sandbox</h3>
          </div>
          <div className="cbody" style={{ padding: 0 }}>
            {loading ? (
              <div className="empty">Memuat data transaksi...</div>
            ) : payments.length === 0 ? (
              <div className="empty">Belum ada transaksi pembayaran.</div>
            ) : (
              <div style={{ overflowX: "auto" }}>
                <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
                  <thead>
                    <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                      <th style={{ padding: "12px 16px" }}>Merchant Order ID</th>
                      <th style={{ padding: "12px 16px" }}>Metode Pembayaran</th>
                      <th style={{ padding: "12px 16px" }}>Jumlah</th>
                      <th style={{ padding: "12px 16px" }}>Ref Duitku</th>
                      <th style={{ padding: "12px 16px" }}>Status</th>
                      <th style={{ padding: "12px 16px", textAlign: "right" }}>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    {payments.map((p) => (
                      <tr
                        key={p.id}
                        style={{
                          borderBottom: "1px solid rgba(255,255,255,0.04)",
                          transition: "background 0.2s",
                        }}
                      >
                        <td style={{ padding: "12px 16px", fontWeight: 700, color: "#F97316" }}>
                          {p.merchant_order_id}
                        </td>
                        <td style={{ padding: "12px 16px", color: "#CBD5E1" }}>
                          {p.payment_method || "Online Payment"}
                        </td>
                        <td style={{ padding: "12px 16px", fontWeight: 700, color: "#10B981" }}>
                          {formatRupiah(p.amount)}
                        </td>
                        <td style={{ padding: "12px 16px", fontSize: 11, color: "var(--muted)" }}>
                          {p.provider_reference || "-"}
                        </td>
                        <td style={{ padding: "12px 16px" }}>
                          <span
                            className={`chip ${
                              p.status === "PAID"
                                ? "c-green"
                                : p.status === "PENDING"
                                ? "c-blue"
                                : "c-red"
                            }`}
                            style={{ fontSize: 11, fontWeight: 700 }}
                          >
                            {p.status}
                          </span>
                        </td>
                        <td style={{ padding: "12px 16px", textAlign: "right" }}>
                          <button className="btn btn-x sm" onClick={() => handleCheckPaymentStatus(p.id)}>
                            Sync Duitku
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      )}
    </>
  );
}
