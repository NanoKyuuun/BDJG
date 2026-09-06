"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface WorkerItem {
  id: number;
  public_id: string;
  name: string;
  email: string;
  profession: string;
  skills?: string[];
  phone?: string;
  status: string;
}

export default function AdminWorkersPage() {
  const [workers, setWorkers] = useState<WorkerItem[]>([]);
  const [loading, setLoading] = useState(true);

  const loadWorkers = () => {
    setLoading(true);
    fetchApi<{ data: WorkerItem[] }>("/api/v1/admin/workers")
      .then((res) => setWorkers(res.data || []))
      .catch((err) => console.error("Failed to load workers:", err))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadWorkers();
  }, []);

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>🧑‍🎨 Production Team & Crew</h1>
          <p>Kru profesional studio, keahlian khusus, dan ketersediaan penugasan proyek</p>
        </div>
      </div>

      <div className="card">
        <div className="chead">
          <h3>Daftar Kru Studio ({workers.length})</h3>
        </div>
        <div className="cbody" style={{ padding: 0 }}>
          {loading ? (
            <div className="empty">Memuat data kru...</div>
          ) : workers.length === 0 ? (
            <div className="empty">Belum ada profil kru terdaftar.</div>
          ) : (
            <div style={{ overflowX: "auto" }}>
              <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
                <thead>
                  <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                    <th style={{ padding: "12px 16px" }}>Nama Kru</th>
                    <th style={{ padding: "12px 16px" }}>Profesi</th>
                    <th style={{ padding: "12px 16px" }}>Kontak / Email</th>
                    <th style={{ padding: "12px 16px" }}>Keahlian (Skills)</th>
                    <th style={{ padding: "12px 16px" }}>Status</th>
                  </tr>
                </thead>
                <tbody>
                  {workers.map((w) => (
                    <tr
                      key={w.id}
                      style={{
                        borderBottom: "1px solid rgba(255,255,255,0.04)",
                        transition: "background 0.2s",
                      }}
                    >
                      <td style={{ padding: "12px 16px", fontWeight: 700, color: "#FFFFFF" }}>
                        {w.name}
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <span className="chip c-blue" style={{ fontSize: 11, fontWeight: 700 }}>
                          {w.profession}
                        </span>
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <div>{w.email}</div>
                        <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 2 }}>{w.phone || "-"}</div>
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        {w.skills && w.skills.length > 0 ? (
                          <div style={{ display: "flex", gap: 4, flexWrap: "wrap" }}>
                            {w.skills.map((s, idx) => (
                              <span key={idx} className="chip c-black" style={{ fontSize: 10 }}>
                                {s}
                              </span>
                            ))}
                          </div>
                        ) : (
                          <span style={{ fontSize: 11, color: "var(--muted)" }}>-</span>
                        )}
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <span
                          className={`chip ${w.status === "ACTIVE" ? "c-green" : "c-red"}`}
                          style={{ fontSize: 11, fontWeight: 700 }}
                        >
                          {w.status}
                        </span>
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
