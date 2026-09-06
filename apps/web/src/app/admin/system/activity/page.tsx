"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface AuditLogItem {
  id: number;
  user_email?: string;
  user_name?: string;
  action: string;
  auditable_type?: string;
  description?: string;
  old_values?: any;
  new_values?: any;
  ip_address?: string;
  created_at: string;
}

export default function AdminActivityPage() {
  const [logs, setLogs] = useState<AuditLogItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");

  const loadLogs = () => {
    setLoading(true);
    const query = new URLSearchParams();
    if (search) query.set("search", search);

    fetchApi<{ data: AuditLogItem[] }>(`/api/v1/admin/system/activity?${query.toString()}`)
      .then((res) => setLogs(res.data || []))
      .catch((err) => console.error("Failed to load activity logs:", err))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadLogs();
  }, []);

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>🕘 System Activity & Audit Logs</h1>
          <p>Rekam jejak mutasi data sensitif, perubahan status proyek, otorisasi, dan transaksi studio</p>
        </div>
        <div style={{ display: "flex", gap: 8 }}>
          <input
            type="text"
            placeholder="Cari aksi, user, deskripsi..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => e.key === "Enter" && loadLogs()}
            style={{
              padding: "7px 12px",
              background: "#09090B",
              border: "1px solid rgba(255,255,255,0.12)",
              borderRadius: 6,
              color: "#FFF",
              fontSize: 12,
              width: 240,
            }}
          />
          <button className="btn btn-x" onClick={loadLogs}>
            Filter
          </button>
        </div>
      </div>

      <div className="card">
        <div className="chead">
          <h3>Log Audit Aktivitas Studio ({logs.length})</h3>
        </div>
        <div className="cbody" style={{ padding: 0 }}>
          {loading ? (
            <div className="empty">Memuat audit logs...</div>
          ) : logs.length === 0 ? (
            <div className="empty">Belum ada catatan aktivitas tercatat.</div>
          ) : (
            <div style={{ overflowX: "auto" }}>
              <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
                <thead>
                  <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                    <th style={{ padding: "12px 16px" }}>Waktu Log</th>
                    <th style={{ padding: "12px 16px" }}>Pelaksana (Actor)</th>
                    <th style={{ padding: "12px 16px" }}>Aksi (Event)</th>
                    <th style={{ padding: "12px 16px" }}>Deskripsi & Mutasi</th>
                    <th style={{ padding: "12px 16px" }}>IP Address</th>
                  </tr>
                </thead>
                <tbody>
                  {logs.map((log) => (
                    <tr
                      key={log.id}
                      style={{
                        borderBottom: "1px solid rgba(255,255,255,0.04)",
                        transition: "background 0.2s",
                      }}
                    >
                      <td style={{ padding: "12px 16px", fontSize: 11, color: "var(--muted)" }}>
                        {new Date(log.created_at).toLocaleString("id-ID", {
                          day: "numeric",
                          month: "short",
                          hour: "2-digit",
                          minute: "2-digit",
                          second: "2-digit",
                        })}
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <div style={{ fontWeight: 700, color: "#FFFFFF" }}>
                          {log.user_name || "System"}
                        </div>
                        <div style={{ fontSize: 11, color: "var(--muted)" }}>{log.user_email || "-"}</div>
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <span className="chip c-black" style={{ fontSize: 10, fontWeight: 700 }}>
                          {log.action}
                        </span>
                      </td>
                      <td style={{ padding: "12px 16px", color: "#CBD5E1" }}>
                        <div>{log.description || "-"}</div>
                        {log.new_values && (
                          <div
                            style={{
                              fontSize: 10.5,
                              color: "var(--muted)",
                              fontFamily: "monospace",
                              marginTop: 4,
                            }}
                          >
                            {JSON.stringify(log.new_values)}
                          </div>
                        )}
                      </td>
                      <td style={{ padding: "12px 16px", fontSize: 11, color: "var(--muted)" }}>
                        {log.ip_address || "127.0.0.1"}
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
