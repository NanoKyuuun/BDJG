"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface ClientItem {
  id: number;
  public_id: string;
  display_name: string;
  company_name?: string;
  email: string;
  phone?: string;
  city?: string;
  type: string;
  users_count?: number;
  created_at: string;
}

export default function AdminClientsPage() {
  const [clients, setClients] = useState<ClientItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [inviteEmail, setInviteEmail] = useState("");
  const [selectedClientId, setSelectedClientId] = useState<number | null>(null);

  const loadClients = () => {
    setLoading(true);
    fetchApi<{ data: ClientItem[] }>("/api/v1/admin/clients")
      .then((res) => setClients(res.data || []))
      .catch((err) => console.error("Failed to load clients:", err))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadClients();
  }, []);

  const handleSendInvite = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedClientId || !inviteEmail) return;

    try {
      const res = await fetchApi<{ data: { token: string } }>(`/api/v1/admin/clients/${selectedClientId}/invite`, {
        method: "POST",
        body: JSON.stringify({ email: inviteEmail }),
      });
      alert(`Undangan aktivasi akun berhasil dibuat! Link aktivasi: /invitation/${res.data.token}`);
      setInviteEmail("");
      setSelectedClientId(null);
    } catch (err: any) {
      alert(err.message || "Gagal mengirim undangan");
    }
  };

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>👥 Clients Directory</h1>
          <p>Daftar klien studio, profil perusahaan, dan akses aktivasi akun portal</p>
        </div>
      </div>

      {selectedClientId && (
        <div className="card mb" style={{ marginBottom: 16 }}>
          <div className="chead">
            <h3>✉️ Kirim Undangan Portal Klien</h3>
            <button className="btn btn-x sm" onClick={() => setSelectedClientId(null)}>
              Tutup
            </button>
          </div>
          <div className="cbody">
            <form onSubmit={handleSendInvite} style={{ display: "flex", gap: 8, alignItems: "center" }}>
              <input
                type="email"
                required
                placeholder="Masukkan email PIC klien..."
                value={inviteEmail}
                onChange={(e) => setInviteEmail(e.target.value)}
                style={{
                  flex: 1,
                  padding: "8px 12px",
                  background: "#09090B",
                  border: "1px solid rgba(255,255,255,0.15)",
                  borderRadius: 6,
                  color: "#FFF",
                  fontSize: 13,
                }}
              />
              <button type="submit" className="btn btn-p">
                Kirim Undangan Aktivasi
              </button>
            </form>
          </div>
        </div>
      )}

      <div className="card">
        <div className="chead">
          <h3>Direktori Klien ({clients.length})</h3>
        </div>
        <div className="cbody" style={{ padding: 0 }}>
          {loading ? (
            <div className="empty">Memuat data klien...</div>
          ) : clients.length === 0 ? (
            <div className="empty">Belum ada klien terdaftar.</div>
          ) : (
            <div style={{ overflowX: "auto" }}>
              <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
                <thead>
                  <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                    <th style={{ padding: "12px 16px" }}>Nama Klien</th>
                    <th style={{ padding: "12px 16px" }}>Tipe Klien</th>
                    <th style={{ padding: "12px 16px" }}>Kontak & Email</th>
                    <th style={{ padding: "12px 16px" }}>Kota</th>
                    <th style={{ padding: "12px 16px", textAlign: "right" }}>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  {clients.map((c) => (
                    <tr
                      key={c.id}
                      style={{
                        borderBottom: "1px solid rgba(255,255,255,0.04)",
                        transition: "background 0.2s",
                      }}
                    >
                      <td style={{ padding: "12px 16px" }}>
                        <div style={{ fontWeight: 700, color: "#FFFFFF" }}>{c.display_name}</div>
                        {c.company_name && (
                          <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 2 }}>
                            🏢 {c.company_name}
                          </div>
                        )}
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <span className="chip c-blue" style={{ fontSize: 11, fontWeight: 700 }}>
                          {c.type}
                        </span>
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <div>{c.email}</div>
                        <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 2 }}>{c.phone || "-"}</div>
                      </td>
                      <td style={{ padding: "12px 16px", color: "var(--muted)" }}>{c.city || "Jakarta"}</td>
                      <td style={{ padding: "12px 16px", textAlign: "right" }}>
                        <button
                          className="btn btn-x sm"
                          onClick={() => {
                            setSelectedClientId(c.id);
                            setInviteEmail(c.email);
                          }}
                        >
                          + Undang Akun
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
    </>
  );
}
