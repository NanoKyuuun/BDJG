"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface ProjectItem {
  id: number;
  public_id: string;
  project_number: string;
  name: string;
  service_name_snapshot?: string;
  package_name_snapshot?: string;
  contract_value: number;
  status: string;
  allowed_next_statuses: string[];
  shoot_date?: string;
  deadline?: string;
  client?: {
    display_name: string;
  };
}

function formatRupiah(amount: number): string {
  return "Rp " + (amount || 0).toLocaleString("id-ID");
}

export default function AdminProjectsPage() {
  const [projects, setProjects] = useState<ProjectItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [statusFilter, setStatusFilter] = useState<string>("");
  const [search, setSearch] = useState<string>("");

  const loadProjects = () => {
    setLoading(true);
    const query = new URLSearchParams();
    if (statusFilter) query.set("status", statusFilter);
    if (search) query.set("search", search);

    fetchApi<{ data: ProjectItem[] }>(`/api/v1/admin/projects?${query.toString()}`)
      .then((res) => setProjects(res.data || []))
      .catch((err) => console.error("Failed to load projects:", err))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadProjects();
  }, [statusFilter]);

  const handleStatusChange = async (projectId: number, newStatus: string) => {
    try {
      await fetchApi(`/api/v1/admin/projects/${projectId}/status`, {
        method: "PATCH",
        body: JSON.stringify({ status: newStatus }),
      });
      loadProjects();
    } catch (err: any) {
      alert(err.message || "Failed to change status");
    }
  };

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>🎬 Projects Management</h1>
          <p>Kelola seluruh produksi video & foto studio aktif</p>
        </div>
        <div style={{ display: "flex", gap: 8 }}>
          <input
            type="text"
            placeholder="Cari nomor / nama proyek..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => e.key === "Enter" && loadProjects()}
            style={{
              padding: "7px 12px",
              background: "#09090B",
              border: "1px solid rgba(255,255,255,0.12)",
              borderRadius: 6,
              color: "#FFF",
              fontSize: 12,
            }}
          />
          <button className="btn btn-x" onClick={loadProjects}>
            Cari
          </button>
        </div>
      </div>

      <div style={{ display: "flex", gap: 8, marginBottom: 16, flexWrap: "wrap" }}>
        {["", "PRE_PRODUCTION", "PRODUCTION", "POST_PRODUCTION", "INTERNAL_REVIEW", "CLIENT_REVIEW", "COMPLETED"].map(
          (st) => (
            <button
              key={st}
              className={`fchip ${statusFilter === st ? "on" : ""}`}
              onClick={() => setStatusFilter(st)}
            >
              {st || "Semua Status"}
            </button>
          )
        )}
      </div>

      <div className="card">
        <div className="chead">
          <h3>Daftar Proyek Studio ({projects.length})</h3>
        </div>
        <div className="cbody" style={{ padding: 0 }}>
          {loading ? (
            <div className="empty">Memuat data proyek...</div>
          ) : projects.length === 0 ? (
            <div className="empty">Tidak ada proyek yang sesuai kriteria.</div>
          ) : (
            <div style={{ overflowX: "auto" }}>
              <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
                <thead>
                  <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                    <th style={{ padding: "12px 16px" }}>No. Proyek</th>
                    <th style={{ padding: "12px 16px" }}>Nama Proyek / Klien</th>
                    <th style={{ padding: "12px 16px" }}>Layanan & Paket</th>
                    <th style={{ padding: "12px 16px" }}>Nilai Kontrak</th>
                    <th style={{ padding: "12px 16px" }}>Shoot Date / Deadline</th>
                    <th style={{ padding: "12px 16px" }}>Status</th>
                    <th style={{ padding: "12px 16px", textAlign: "right" }}>Aksi Status</th>
                  </tr>
                </thead>
                <tbody>
                  {projects.map((p) => (
                    <tr
                      key={p.id}
                      style={{
                        borderBottom: "1px solid rgba(255,255,255,0.04)",
                        transition: "background 0.2s",
                      }}
                    >
                      <td style={{ padding: "12px 16px", fontWeight: 700, color: "#F97316" }}>
                        {p.project_number}
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <div style={{ fontWeight: 700, color: "#FFFFFF" }}>{p.name}</div>
                        <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 2 }}>
                          {p.client?.display_name || "Internal"}
                        </div>
                      </td>
                      <td style={{ padding: "12px 16px", fontSize: 12, color: "#CBD5E1" }}>
                        {p.package_name_snapshot || p.service_name_snapshot || "Custom Package"}
                      </td>
                      <td style={{ padding: "12px 16px", fontWeight: 700, color: "#10B981" }}>
                        {formatRupiah(p.contract_value)}
                      </td>
                      <td style={{ padding: "12px 16px", fontSize: 11, color: "var(--muted)" }}>
                        <div>🎥 {p.shoot_date || "-"}</div>
                        <div>🏁 {p.deadline || "-"}</div>
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <span className="chip c-blue" style={{ fontSize: 11, fontWeight: 700 }}>
                          {p.status}
                        </span>
                      </td>
                      <td style={{ padding: "12px 16px", textAlign: "right" }}>
                        {p.allowed_next_statuses && p.allowed_next_statuses.length > 0 ? (
                          <select
                            defaultValue=""
                            onChange={(e) => {
                              if (e.target.value) {
                                handleStatusChange(p.id, e.target.value);
                              }
                            }}
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
                            <option value="" disabled>
                              Ubah status...
                            </option>
                            {p.allowed_next_statuses.map((st) => (
                              <option key={st} value={st}>
                                ➔ {st}
                              </option>
                            ))}
                          </select>
                        ) : (
                          <span style={{ fontSize: 11, color: "var(--muted)" }}>Final</span>
                        )}
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
