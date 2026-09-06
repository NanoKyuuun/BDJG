"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface ProjectItem {
  id: number;
  project_number: string;
  name: string;
}

interface MediaAsset {
  id: number;
  filename: string;
  original_name: string;
  category: string;
  visibility: string;
  mime_type: string;
  size_bytes: number;
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

export default function WorkerFilesPage() {
  const [mediaAssets, setMediaAssets] = useState<MediaAsset[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadFiles() {
      try {
        setLoading(true);
        const projectsRes = await fetchApi<{ data: ProjectItem[] }>("/api/v1/worker/projects");
        const projectList = projectsRes.data || [];

        const assetResults = await Promise.all(
          projectList.map(async (p) => {
            try {
              const res = await fetchApi<{ data: MediaAsset[] }>(`/api/v1/worker/projects/${p.id}/media`);
              return (res.data || []).map((m) => ({ ...m, project: p }));
            } catch {
              return [];
            }
          })
        );

        setMediaAssets(assetResults.flat());
      } catch (err) {
        console.error("Failed to load worker files:", err);
      } finally {
        setLoading(false);
      }
    }

    loadFiles();
  }, []);

  return (
    <>
      <div className="pagehead">
        <div>
          <div style={{ fontSize: 11, fontWeight: 700, color: "var(--teal)", letterSpacing: 1.2 }}>
            PRODUCTION MEDIA ASSETS
          </div>
          <h1 style={{ marginTop: 4 }}>📁 Working Files &amp; Footage</h1>
          <p>Akses cepat file mentah (*raw footage*), aset grafis, dan draft video proyek Anda</p>
        </div>
      </div>

      {loading ? (
        <div className="empty" style={{ padding: "60px 0" }}>
          Memuat aset media...
        </div>
      ) : mediaAssets.length === 0 ? (
        <div className="card" style={{ padding: 40, textAlign: "center" }}>
          <div style={{ fontSize: 40, marginBottom: 12 }}>🎥</div>
          <h3 style={{ fontSize: 18, fontWeight: 700 }}>Belum Ada Media yang Diunggah</h3>
          <p style={{ color: "var(--muted)", fontSize: 13, marginTop: 4 }}>
            Buka halaman detail proyek untuk mengunggah draft preview atau working file baru.
          </p>
        </div>
      ) : (
        <div className="card" style={{ padding: 16 }}>
          <div style={{ overflowX: "auto" }}>
            <table className="tbl" style={{ width: "100%", fontSize: 13 }}>
              <thead>
                <tr style={{ color: "var(--muted)", borderBottom: "1px solid var(--line)" }}>
                  <th style={{ padding: "10px", textAlign: "left" }}>NAMA FILE</th>
                  <th style={{ padding: "10px", textAlign: "left" }}>PROYEK</th>
                  <th style={{ padding: "10px", textAlign: "left" }}>KATEGORI</th>
                  <th style={{ padding: "10px", textAlign: "left" }}>UKURAN</th>
                  <th style={{ padding: "10px", textAlign: "left" }}>TANGGAL</th>
                  <th style={{ padding: "10px", textAlign: "right" }}>AKSI</th>
                </tr>
              </thead>
              <tbody>
                {mediaAssets.map((m) => (
                  <tr key={m.id} style={{ borderBottom: "1px solid rgba(255,255,255,0.04)" }}>
                    <td style={{ padding: "12px 10px", fontWeight: 700, color: "#FFF" }}>
                      {m.mime_type.startsWith("video/") ? "🎬" : "🖼️"} {m.original_name}
                    </td>
                    <td style={{ padding: "12px 10px", color: "var(--orange)" }}>
                      {m.project ? `${m.project.project_number} — ${m.project.name}` : "-"}
                    </td>
                    <td style={{ padding: "12px 10px" }}>
                      <span className="chip" style={{ fontSize: 11 }}>{m.category}</span>
                    </td>
                    <td style={{ padding: "12px 10px", color: "var(--muted)" }}>{formatBytes(m.size_bytes)}</td>
                    <td style={{ padding: "12px 10px", color: "var(--muted)" }}>
                      {new Date(m.created_at).toLocaleDateString("id-ID", { month: "short", day: "numeric" })}
                    </td>
                    <td style={{ padding: "12px 10px", textAlign: "right" }}>
                      {m.project && (
                        <Link href={`/worker/projects/${m.project.id}`} className="btn btn-o" style={{ padding: "4px 10px", fontSize: 11 }}>
                          Buka di Player ↗
                        </Link>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </>
  );
}
