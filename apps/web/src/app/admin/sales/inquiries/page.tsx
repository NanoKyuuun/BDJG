"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface InquiryItem {
  id: number;
  public_id: string;
  inquiry_number: string;
  name: string;
  email: string;
  phone?: string;
  company_name?: string;
  service?: {
    name: string;
  };
  event_name?: string;
  event_date?: string;
  status: string;
  estimated_budget?: number;
  created_at: string;
}

function formatRupiah(amount: number): string {
  return "Rp " + (amount || 0).toLocaleString("id-ID");
}

export default function AdminInquiriesPage() {
  const [inquiries, setInquiries] = useState<InquiryItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [statusFilter, setStatusFilter] = useState<string>("");

  const loadInquiries = () => {
    setLoading(true);
    const query = new URLSearchParams();
    if (statusFilter) query.set("status", statusFilter);

    fetchApi<{ data: InquiryItem[] }>(`/api/v1/admin/inquiries?${query.toString()}`)
      .then((res) => setInquiries(res.data || []))
      .catch((err) => console.error("Failed to load inquiries:", err))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadInquiries();
  }, [statusFilter]);

  const handleStatusChange = async (inquiryId: number, newStatus: string) => {
    try {
      await fetchApi(`/api/v1/admin/inquiries/${inquiryId}/status`, {
        method: "PATCH",
        body: JSON.stringify({ status: newStatus }),
      });
      loadInquiries();
    } catch (err: any) {
      alert(err.message || "Failed to update inquiry status");
    }
  };

  const handleConvertToClient = async (inquiryId: number) => {
    if (!confirm("Konversi inquiry ini menjadi entitas Klien resmi?")) return;
    try {
      await fetchApi(`/api/v1/admin/inquiries/${inquiryId}/convert-to-client`, {
        method: "POST",
      });
      alert("Berhasil dikonversi menjadi Klien!");
      loadInquiries();
    } catch (err: any) {
      alert(err.message || "Gagal konversi ke klien");
    }
  };

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>📥 CRM & Inquiries</h1>
          <p>Kelola calon klien & prospek proyek masuk dari website studio</p>
        </div>
      </div>

      <div style={{ display: "flex", gap: 8, marginBottom: 16, flexWrap: "wrap" }}>
        {["", "NEW", "CONTACTED", "QUALIFIED", "QUOTATION", "WON", "LOST"].map((st) => (
          <button
            key={st}
            className={`fchip ${statusFilter === st ? "on" : ""}`}
            onClick={() => setStatusFilter(st)}
          >
            {st || "Semua Leads"}
          </button>
        ))}
      </div>

      <div className="card">
        <div className="chead">
          <h3>Daftar Inquiry Masuk ({inquiries.length})</h3>
        </div>
        <div className="cbody" style={{ padding: 0 }}>
          {loading ? (
            <div className="empty">Memuat data inquiry...</div>
          ) : inquiries.length === 0 ? (
            <div className="empty">Belum ada inquiry leads pada status ini.</div>
          ) : (
            <div style={{ overflowX: "auto" }}>
              <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
                <thead>
                  <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                    <th style={{ padding: "12px 16px" }}>No. Lead</th>
                    <th style={{ padding: "12px 16px" }}>Kontak Prospek</th>
                    <th style={{ padding: "12px 16px" }}>Kebutuhan Layanan</th>
                    <th style={{ padding: "12px 16px" }}>Estimasi Budget</th>
                    <th style={{ padding: "12px 16px" }}>Status</th>
                    <th style={{ padding: "12px 16px", textAlign: "right" }}>Tindakan</th>
                  </tr>
                </thead>
                <tbody>
                  {inquiries.map((inq) => (
                    <tr
                      key={inq.id}
                      style={{
                        borderBottom: "1px solid rgba(255,255,255,0.04)",
                        transition: "background 0.2s",
                      }}
                    >
                      <td style={{ padding: "12px 16px", fontWeight: 700, color: "#F97316" }}>
                        {inq.inquiry_number}
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <div style={{ fontWeight: 700, color: "#FFFFFF" }}>{inq.name}</div>
                        <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 2 }}>
                          {inq.email} • {inq.phone || "-"}
                        </div>
                        {inq.company_name && (
                          <div style={{ fontSize: 11, color: "#93C5FD", marginTop: 2 }}>
                            🏢 {inq.company_name}
                          </div>
                        )}
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <div style={{ color: "#CBD5E1" }}>{inq.service?.name || inq.event_name || "Layanan Video/Foto"}</div>
                        {inq.event_date && (
                          <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 2 }}>
                            📅 Tgl Acara: {inq.event_date}
                          </div>
                        )}
                      </td>
                      <td style={{ padding: "12px 16px", fontWeight: 700, color: "#10B981" }}>
                        {inq.estimated_budget ? formatRupiah(inq.estimated_budget) : "Belum ditentukan"}
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <span
                          className={`chip ${
                            inq.status === "NEW"
                              ? "c-black"
                              : inq.status === "WON"
                              ? "c-green"
                              : inq.status === "LOST"
                              ? "c-red"
                              : "c-blue"
                          }`}
                          style={{ fontSize: 11, fontWeight: 700 }}
                        >
                          {inq.status}
                        </span>
                      </td>
                      <td style={{ padding: "12px 16px", textAlign: "right" }}>
                        <div style={{ display: "flex", gap: 6, justifyContent: "flex-end" }}>
                          <select
                            value={inq.status}
                            onChange={(e) => handleStatusChange(inq.id, e.target.value)}
                            style={{
                              padding: "4px 8px",
                              background: "#000",
                              border: "1px solid rgba(255,255,255,0.2)",
                              borderRadius: 4,
                              color: "#FFF",
                              fontSize: 11,
                              cursor: "pointer",
                            }}
                          >
                            <option value="NEW">NEW</option>
                            <option value="CONTACTED">CONTACTED</option>
                            <option value="QUALIFIED">QUALIFIED</option>
                            <option value="QUOTATION">QUOTATION</option>
                            <option value="WON">WON</option>
                            <option value="LOST">LOST</option>
                          </select>

                          <button
                            className="btn btn-x sm"
                            title="Konversi ke Client"
                            onClick={() => handleConvertToClient(inq.id)}
                          >
                            + Klien
                          </button>
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
