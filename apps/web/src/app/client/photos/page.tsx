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

export default function ClientPhotosPage() {
  const [photos, setPhotos] = useState<MediaAsset[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadPhotos() {
      try {
        setLoading(true);
        const projectsRes = await fetchApi<{ data: ProjectItem[] }>("/api/v1/client/projects");
        const projectList = projectsRes.data || [];

        const photoResults = await Promise.all(
          projectList.map(async (p) => {
            try {
              const res = await fetchApi<{ data: MediaAsset[] }>(`/api/v1/client/projects/${p.id}/media`);
              return (res.data || [])
                .filter((m) => m.mime_type.startsWith("image/") || m.category === "PHOTO_SELECT")
                .map((m) => ({ ...m, project: p }));
            } catch {
              return [];
            }
          })
        );

        setPhotos(photoResults.flat());
      } catch (err) {
        console.error("Failed to load client photos:", err);
      } finally {
        setLoading(false);
      }
    }

    loadPhotos();
  }, []);

  return (
    <>
      <div className="pagehead">
        <div>
          <div style={{ fontSize: 11, fontWeight: 700, color: "var(--yellow)", letterSpacing: 1.2 }}>
            PHOTO SELECTION &amp; CURATION
          </div>
          <h1 style={{ marginTop: 4 }}>🖼️ Photo Selection Gallery</h1>
          <p>Pilih dan kurasi hasil jepretan foto sesi produksi untuk proses editing final</p>
        </div>
      </div>

      {loading ? (
        <div className="empty" style={{ padding: "60px 0" }}>
          Memuat galeri foto...
        </div>
      ) : photos.length === 0 ? (
        <div className="card" style={{ padding: 40, textAlign: "center" }}>
          <div style={{ fontSize: 40, marginBottom: 12 }}>📸</div>
          <h3 style={{ fontSize: 18, fontWeight: 700 }}>Belum Ada Set Foto yang Diunggah</h3>
          <p style={{ color: "var(--muted)", fontSize: 13, marginTop: 4, maxWidth: 460, marginInline: "auto" }}>
            Fotografer BDJG akan mengunggah set foto setelah sesi shooting selesai untuk proses kurasi dan pemilihan oleh Anda.
          </p>
        </div>
      ) : (
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(260px, 1fr))", gap: 16 }}>
          {photos.map((p) => (
            <div key={p.id} className="card" style={{ padding: 14 }}>
              <div
                style={{
                  height: 180,
                  background: "rgba(255,255,255,0.03)",
                  borderRadius: 8,
                  display: "grid",
                  placeItems: "center",
                  fontSize: 32,
                  marginBottom: 10,
                  border: "1px solid rgba(255,255,255,0.06)",
                }}
              >
                🖼️
              </div>
              <div style={{ fontWeight: 700, fontSize: 13, color: "#FFF", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
                {p.original_name}
              </div>
              {p.project && (
                <div style={{ fontSize: 11, color: "var(--orange)", marginTop: 2 }}>
                  {p.project.project_number} — {p.project.name}
                </div>
              )}
            </div>
          ))}
        </div>
      )}
    </>
  );
}
