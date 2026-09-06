"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface ProjectItem {
  id: number;
  project_number: string;
  name: string;
  status: string;
}

interface RevisionRound {
  id: number;
  round_number: number;
  title?: string;
  status: string;
  notes?: string;
  comments: Array<{
    id: number;
    timecode_seconds: number;
    comment: string;
    status: string;
  }>;
  project?: ProjectItem;
  created_at: string;
}

function formatTimecode(seconds?: number): string {
  if (seconds === undefined || seconds === null) return "--:--";
  const m = Math.floor(seconds / 60);
  const s = Math.floor(seconds % 60);
  return `${String(m).padStart(2, "0")}:${String(s).padStart(2, "0")}`;
}

export default function ClientPreviewPage() {
  const [projects, setProjects] = useState<ProjectItem[]>([]);
  const [revisions, setRevisions] = useState<RevisionRound[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadData() {
      try {
        setLoading(true);
        const projectsRes = await fetchApi<{ data: ProjectItem[] }>("/api/v1/client/projects");
        const projectList = projectsRes.data || [];
        setProjects(projectList);

        const revResults = await Promise.all(
          projectList.map(async (p) => {
            try {
              const res = await fetchApi<{ data: RevisionRound[] }>(`/api/v1/client/projects/${p.id}/revisions`);
              return (res.data || []).map((r) => ({ ...r, project: p }));
            } catch {
              return [];
            }
          })
        );

        setRevisions(revResults.flat());
      } catch (err) {
        console.error("Failed to load client preview revisions:", err);
      } finally {
        setLoading(false);
      }
    }

    loadData();
  }, []);

  return (
    <>
      <div className="pagehead">
        <div>
          <div style={{ fontSize: 11, fontWeight: 700, color: "var(--orange)", letterSpacing: 1.2 }}>
            VIDEO PREVIEW &amp; FEEDBACK
          </div>
          <h1 style={{ marginTop: 4 }}>🎞️ Preview &amp; Revision Hub</h1>
          <p>Tinjau draft video proyek Anda, berikan catatan per-timecode, dan pantau hasil revisi</p>
        </div>
      </div>

      {loading ? (
        <div className="empty" style={{ padding: "60px 0" }}>
          Memuat draft preview...
        </div>
      ) : revisions.length === 0 ? (
        <div className="card" style={{ padding: 40, textAlign: "center" }}>
          <div style={{ fontSize: 40, marginBottom: 12 }}>🎬</div>
          <h3 style={{ fontSize: 18, fontWeight: 700 }}>Belum Ada Draft Review Aktif</h3>
          <p style={{ color: "var(--muted)", fontSize: 13, marginTop: 4, maxWidth: 460, marginInline: "auto" }}>
            Tim BDJG sedang memproses video Anda. Draft preview akan muncul di sini segera setelah putaran review internal selesai.
          </p>
          {projects.length > 0 && (
            <div style={{ marginTop: 20 }}>
              <Link href={`/client/projects/${projects[0].id}`} className="btn btn-p">
                Buka Proyek Saya ↗
              </Link>
            </div>
          )}
        </div>
      ) : (
        <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
          {revisions.map((rev) => (
            <div key={rev.id} className="card" style={{ padding: 22 }}>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 14 }}>
                <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
                  <span
                    style={{
                      fontSize: 12,
                      fontWeight: 800,
                      padding: "4px 10px",
                      borderRadius: 6,
                      background: "rgba(255, 255, 255, 0.08)",
                      color: "#FFF",
                    }}
                  >
                    PUTARAN #{rev.round_number}
                  </span>
                  <div>
                    <div style={{ fontWeight: 700, fontSize: 16 }}>{rev.title || `Revision Round #${rev.round_number}`}</div>
                    {rev.project && (
                      <div style={{ fontSize: 12, color: "var(--orange)", fontWeight: 600, marginTop: 2 }}>
                        🎬 {rev.project.project_number} — {rev.project.name}
                      </div>
                    )}
                  </div>
                </div>

                <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                  <span
                    className={`chip ${
                      rev.status === "RESOLVED"
                        ? "c-green"
                        : rev.status === "IN_PROGRESS"
                        ? "c-blue"
                        : "c-yellow"
                    }`}
                  >
                    {rev.status}
                  </span>
                  {rev.project && (
                    <Link href={`/client/projects/${rev.project.id}`} className="btn btn-p" style={{ fontSize: 12, padding: "6px 14px" }}>
                      Buka Video Player &amp; Beri Catatan ↗
                    </Link>
                  )}
                </div>
              </div>

              {rev.notes && (
                <div
                  style={{
                    padding: "10px 14px",
                    background: "rgba(255, 255, 255, 0.02)",
                    borderRadius: 8,
                    border: "1px solid rgba(255, 255, 255, 0.05)",
                    fontSize: 12,
                    color: "var(--muted)",
                    marginBottom: 12,
                  }}
                >
                  📝 <strong>Catatan dari Studio:</strong> {rev.notes}
                </div>
              )}

              {/* Timestamp Comments Summary */}
              <div>
                <div style={{ fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 8 }}>
                  POIN REVISI ANDA ({rev.comments?.length || 0})
                </div>

                {!rev.comments || rev.comments.length === 0 ? (
                  <div style={{ fontSize: 12, color: "var(--muted)", fontStyle: "italic" }}>
                    Belum ada poin catatan spesifik pada putaran ini.
                  </div>
                ) : (
                  <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                    {rev.comments.map((c) => (
                      <div
                        key={c.id}
                        style={{
                          display: "flex",
                          alignItems: "center",
                          gap: 10,
                          padding: "8px 12px",
                          background: "var(--card2)",
                          borderRadius: 6,
                          fontSize: 12,
                        }}
                      >
                        <span
                          style={{
                            fontFamily: "monospace",
                            fontWeight: 700,
                            padding: "2px 6px",
                            borderRadius: 4,
                            background: "rgba(255, 199, 0, 0.15)",
                            color: "var(--yellow)",
                          }}
                        >
                          ⏱️ {formatTimecode(c.timecode_seconds)}
                        </span>
                        <span style={{ flex: 1, color: "#FFF" }}>{c.comment}</span>
                        <span className={`chip ${c.status === "RESOLVED" ? "c-green" : "c-yellow"}`} style={{ fontSize: 10 }}>
                          {c.status}
                        </span>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </>
  );
}
