"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface QuotationItem {
  id: number;
  public_id: string;
  quotation_number: string;
  client?: {
    display_name: string;
  };
  current_version?: {
    project_name: string;
    grand_total: number;
    dp_amount: number;
    version_number: number;
  };
  status: string;
  sent_at?: string;
  created_at: string;
}

function formatRupiah(amount: number): string {
  return "Rp " + (amount || 0).toLocaleString("id-ID");
}

export default function AdminQuotationsPage() {
  const [quotations, setQuotations] = useState<QuotationItem[]>([]);
  const [loading, setLoading] = useState(true);

  const loadQuotations = () => {
    setLoading(true);
    fetchApi<{ data: QuotationItem[] }>("/api/v1/admin/quotations")
      .then((res) => setQuotations(res.data || []))
      .catch((err) => console.error("Failed to load quotations:", err))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadQuotations();
  }, []);

  const handleSendQuotation = async (quotationId: number) => {
    try {
      await fetchApi(`/api/v1/admin/quotations/${quotationId}/send`, { method: "POST" });
      alert("Penawaran berhasil dikirim ke Klien!");
      loadQuotations();
    } catch (err: any) {
      alert(err.message || "Gagal mengirim penawaran");
    }
  };

  const handleGenerateDpInvoice = async (quotationId: number) => {
    try {
      await fetchApi(`/api/v1/admin/quotations/${quotationId}/generate-dp-invoice`, { method: "POST" });
      alert("Invoice DP berhasil diterbitkan!");
      loadQuotations();
    } catch (err: any) {
      alert(err.message || "Gagal membuat invoice DP");
    }
  };

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>🧾 Quotations & Versioning</h1>
          <p>Kelola penawaran harga multi-versi, status persetujuan, dan tagihan DP</p>
        </div>
      </div>

      <div className="card">
        <div className="chead">
          <h3>Daftar Penawaran Harga ({quotations.length})</h3>
        </div>
        <div className="cbody" style={{ padding: 0 }}>
          {loading ? (
            <div className="empty">Memuat data penawaran...</div>
          ) : quotations.length === 0 ? (
            <div className="empty">Belum ada penawaran harga.</div>
          ) : (
            <div style={{ overflowX: "auto" }}>
              <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
                <thead>
                  <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                    <th style={{ padding: "12px 16px" }}>No. Quotation</th>
                    <th style={{ padding: "12px 16px" }}>Klien & Judul Proyek</th>
                    <th style={{ padding: "12px 16px" }}>Versi Aktif</th>
                    <th style={{ padding: "12px 16px" }}>Grand Total</th>
                    <th style={{ padding: "12px 16px" }}>DP (Down Payment)</th>
                    <th style={{ padding: "12px 16px" }}>Status</th>
                    <th style={{ padding: "12px 16px", textAlign: "right" }}>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  {quotations.map((q) => (
                    <tr
                      key={q.id}
                      style={{
                        borderBottom: "1px solid rgba(255,255,255,0.04)",
                        transition: "background 0.2s",
                      }}
                    >
                      <td style={{ padding: "12px 16px", fontWeight: 700, color: "#F97316" }}>
                        {q.quotation_number}
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <div style={{ fontWeight: 700, color: "#FFFFFF" }}>
                          {q.current_version?.project_name || "Quotation Draft"}
                        </div>
                        <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 2 }}>
                          Client: {q.client?.display_name || "-"}
                        </div>
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <span className="chip c-black" style={{ fontSize: 11, fontWeight: 700 }}>
                          V{q.current_version?.version_number || 1}
                        </span>
                      </td>
                      <td style={{ padding: "12px 16px", fontWeight: 700, color: "#10B981" }}>
                        {formatRupiah(q.current_version?.grand_total || 0)}
                      </td>
                      <td style={{ padding: "12px 16px", color: "#FBBF24" }}>
                        {formatRupiah(q.current_version?.dp_amount || 0)}
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <span
                          className={`chip ${
                            q.status === "ACCEPTED"
                              ? "c-green"
                              : q.status === "SENT"
                              ? "c-blue"
                              : q.status === "DECLINED"
                              ? "c-red"
                              : "c-black"
                          }`}
                          style={{ fontSize: 11, fontWeight: 700 }}
                        >
                          {q.status}
                        </span>
                      </td>
                      <td style={{ padding: "12px 16px", textAlign: "right" }}>
                        <div style={{ display: "flex", gap: 6, justifyContent: "flex-end" }}>
                          {q.status === "DRAFT" && (
                            <button className="btn btn-p sm" onClick={() => handleSendQuotation(q.id)}>
                              Kirim Klien
                            </button>
                          )}
                          {q.status === "ACCEPTED" && (
                            <button className="btn btn-o sm" onClick={() => handleGenerateDpInvoice(q.id)}>
                              + Terbitkan DP
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
    </>
  );
}
