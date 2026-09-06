"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface ProjectItem {
  id: number;
  project_number: string;
  name: string;
}

interface DeliveryPackage {
  id: number;
  public_id: string;
  project_id: number;
  title: string;
  notes?: string;
  status: string;
  total_size_bytes: number;
  file_count: number;
  download_count: number;
  expires_at?: string;
  created_at: string;
  project?: ProjectItem;
}

function formatBytes(bytes: number): string {
  if (!bytes) return "0 B";
  const k = 1024;
  const sizes = ["B", "KB", "MB", "GB", "TB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
}

export default function ClientFilesPage() {
  const [deliveries, setDeliveries] = useState<DeliveryPackage[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadData() {
      try {
        setLoading(true);
        const projectsRes = await fetchApi<{ data: ProjectItem[] }>("/api/v1/client/projects");
        const projectList = projectsRes.data || [];

        const delivResults = await Promise.all(
          projectList.map(async (p) => {
            try {
              const res = await fetchApi<{ data: DeliveryPackage[] }>(`/api/v1/client/projects/${p.id}/deliveries`);
              return (res.data || []).map((d) => ({ ...d, project: p }));
            } catch {
              return [];
            }
          })
        );

        setDeliveries(delivResults.flat());
      } catch (err) {
        console.error("Failed to load client deliveries:", err);
      } finally {
        setLoading(false);
      }
    }

    loadData();
  }, []);

  const handleDownload = async (deliveryId: number) => {
    try {
      const res = await fetchApi<{ data: { download_url: string } }>(
        `/api/v1/client/deliveries/${deliveryId}/download-url`
      );
      if (res.data?.download_url) {
        window.open(res.data.download_url, "_blank");
      }
    } catch (err: any) {
      alert(err.message || "Failed to generate download link");
    }
  };

  return (
    <>
      <div className="pagehead">
        <div>
          <div style={{ fontSize: 11, fontWeight: 700, color: "var(--teal)", letterSpacing: 1.2 }}>
            FINAL DELIVERIES &amp; HANDOVER
          </div>
          <h1 style={{ marginTop: 4 }}>📁 Final Delivery Files</h1>
          <p>Unduh seluruh paket file master video, foto resolusi tinggi, dan aset final proyek Anda</p>
        </div>
      </div>

      {loading ? (
        <div className="empty" style={{ padding: "60px 0" }}>
          Memuat file delivery...
        </div>
      ) : deliveries.length === 0 ? (
        <div className="card" style={{ padding: 40, textAlign: "center" }}>
          <div style={{ fontSize: 40, marginBottom: 12 }}>📦</div>
          <h3 style={{ fontSize: 18, fontWeight: 700 }}>Belum Ada Paket File Final yang Dirilis</h3>
          <p style={{ color: "var(--muted)", fontSize: 13, marginTop: 4, maxWidth: 460, marginInline: "auto" }}>
            Paket file master resolusi penuh akan dirilis di halaman ini setelah seluruh proses revisi tuntas dan invoice pelunasan telah diverifikasi.
          </p>
        </div>
      ) : (
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(340px, 1fr))", gap: 16 }}>
          {deliveries.map((d) => (
            <div key={d.id} className="card" style={{ padding: 20, display: "flex", flexDirection: "column", gap: 12 }}>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start" }}>
                <span className="chip c-green" style={{ fontSize: 10, fontWeight: 800 }}>
                  🚀 {d.status}
                </span>
                <span style={{ fontSize: 11, color: "var(--muted)" }}>
                  {new Date(d.created_at).toLocaleDateString("id-ID", { month: "short", day: "numeric", year: "numeric" })}
                </span>
              </div>

              <div>
                <h3 style={{ fontSize: 16, fontWeight: 700, color: "#FFF" }}>{d.title}</h3>
                {d.project && (
                  <div style={{ fontSize: 11.5, color: "var(--orange)", fontWeight: 600, marginTop: 2 }}>
                    🎬 {d.project.project_number} — {d.project.name}
                  </div>
                )}
              </div>

              {d.notes && (
                <p style={{ fontSize: 12, color: "var(--muted)", lineHeight: 1.4 }}>
                  {d.notes}
                </p>
              )}

              <div
                style={{
                  display: "flex",
                  justifyContent: "space-between",
                  alignItems: "center",
                  padding: "10px 12px",
                  background: "var(--card2)",
                  borderRadius: 8,
                  fontSize: 11.5,
                  marginTop: "auto",
                }}
              >
                <span>📦 {d.file_count || 1} File</span>
                <span>💾 {formatBytes(d.total_size_bytes)}</span>
                <span>⬇️ {d.download_count}x</span>
              </div>

              <button
                className="btn btn-p"
                style={{ width: "100%", justifyContent: "center", padding: "10px" }}
                onClick={() => handleDownload(d.id)}
              >
                ⬇️ Unduh Paket File Master
              </button>
            </div>
          ))}
        </div>
      )}
    </>
  );
}
