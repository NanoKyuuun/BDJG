"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface ProjectItem {
  id: number;
  project_number: string;
  name: string;
}

interface RevisionComment {
  id: number;
  timecode_seconds: number;
  comment: string;
  status: "OPEN" | "RESOLVED";
  author?: {
    name: string;
  };
  created_at: string;
}

interface RevisionRound {
  id: number;
  round_number: number;
  title?: string;
  status: string;
  notes?: string;
  comments: RevisionComment[];
  project?: ProjectItem;
  created_at: string;
}

function formatTimecode(seconds?: number): string {
  if (seconds === undefined || seconds === null) return "--:--";
  const m = Math.floor(seconds / 60);
  const s = Math.floor(seconds % 60);
  return `${String(m).padStart(2, "0")}:${String(s).padStart(2, "0")}`;
}

export default function WorkerRevisionsPage() {
  const [revisions, setRevisions] = useState<RevisionRound[]>([]);
  const [loading, setLoading] = useState(true);

  const loadData = async () => {
    try {
      setLoading(true);
      const projectsRes = await fetchApi<{ data: ProjectItem[] }>("/api/v1/worker/projects");
      const projectList = projectsRes.data || [];

      const revResults = await Promise.all(
        projectList.map(async (p) => {
          try {
            const res = await fetchApi<{ data: RevisionRound[] }>(`/api/v1/worker/projects/${p.id}/revisions`);
            return (res.data || []).map((r) => ({ ...r, project: p }));
          } catch {
            return [];
          }
        })
      );

      setRevisions(revResults.flat());
    } catch (err) {
      console.error("Failed to load worker revisions:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const handleResolveComment = async (commentId: number) => {
    try {
      await fetchApi(`/api/v1/worker/revision-comments/${commentId}/resolve`, {
        method: "POST",
      });
      loadData();
    } catch (err: any) {
      alert(err.message || "Failed to resolve feedback");
    }
  };

  return (
    <>
      <div className="pagehead">
        <div>
          <div style={{ fontSize: 11, fontWeight: 700, color: "var(--yellow)", letterSpacing: 1.2 }}>
            POST-PRODUCTION REVISIONS
          </div>
          <h1 style={{ marginTop: 4 }}>🔁 Assigned Revisions &amp; Feedbacks</h1>
          <p>Catatan revisi per-timecode dari client pada proyek yang ditugaskan kepada Anda</p>
        </div>
      </div>

      {loading ? (
        <div className="empty" style={{ padding: "60px 0" }}>
          Memuat revisi...
        </div>
      ) : revisions.length === 0 ? (
        <div className="card" style={{ padding: 40, textAlign: "center" }}>
          <div style={{ fontSize: 40, marginBottom: 12 }}>✨</div>
          <h3 style={{ fontSize: 18, fontWeight: 700 }}>Tidak Ada Revisi Tertunda</h3>
          <p style={{ color: "var(--muted)", fontSize: 13, marginTop: 4 }}>
            Semua catatan feedback pada proyek Anda sudah tuntas atau belum ada putaran revisi baru dari client.
          </p>
        </div>
      ) : (
        <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
          {revisions.map((rev) => (
            <div key={rev.id} className="card" style={{ padding: 20 }}>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 12 }}>
                <div>
                  <span style={{ fontSize: 11, fontWeight: 800, color: "var(--orange)" }}>
                    PUTARAN #{rev.round_number}
                  </span>
                  <h3 style={{ fontSize: 15, fontWeight: 700, color: "#FFF" }}>
                    {rev.title || `Revision Round #${rev.round_number}`}
                  </h3>
                  {rev.project && (
                    <Link
                      href={`/worker/projects/${rev.project.id}`}
                      style={{ fontSize: 12, color: "var(--orange)", textDecoration: "none", fontWeight: 600 }}
                    >
                      🎬 {rev.project.project_number} — {rev.project.name}
                    </Link>
                  )}
                </div>

                <span className={`chip ${rev.status === "RESOLVED" ? "c-green" : "c-yellow"}`}>
                  {rev.status}
                </span>
              </div>

              {rev.notes && (
                <div style={{ padding: "8px 12px", background: "var(--card2)", borderRadius: 6, fontSize: 12, color: "var(--muted)", marginBottom: 12 }}>
                  📝 {rev.notes}
                </div>
              )}

              <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
                {rev.comments?.map((c) => {
                  const isOpen = c.status === "OPEN";
                  return (
                    <div
                      key={c.id}
                      style={{
                        display: "flex",
                        justifyContent: "space-between",
                        alignItems: "center",
                        padding: "8px 12px",
                        background: "var(--card2)",
                        borderRadius: 6,
                        fontSize: 12,
                      }}
                    >
                      <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                        <span style={{ fontFamily: "monospace", fontWeight: 700, color: "var(--yellow)" }}>
                          ⏱️ {formatTimecode(c.timecode_seconds)}
                        </span>
                        <span style={{ color: isOpen ? "#FFF" : "var(--muted)", textDecoration: isOpen ? "none" : "line-through" }}>
                          {c.comment}
                        </span>
                      </div>

                      {isOpen ? (
                        <button
                          className="btn btn-o"
                          style={{ padding: "3px 8px", fontSize: 11, color: "var(--green)" }}
                          onClick={() => handleResolveComment(c.id)}
                        >
                          ✓ Selesai
                        </button>
                      ) : (
                        <span className="chip c-green" style={{ fontSize: 10 }}>
                          ✓ Done
                        </span>
                      )}
                    </div>
                  );
                })}
              </div>
            </div>
          ))}
        </div>
      )}
    </>
  );
}
