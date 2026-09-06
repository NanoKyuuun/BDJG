"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface RevisionComment {
  id: number;
  timecode_seconds?: number;
  frame_number?: number;
  comment: string;
  status: "OPEN" | "RESOLVED";
  author?: {
    id: number;
    name: string;
    email: string;
  };
  resolved_by?: {
    name: string;
  };
  resolved_at?: string;
  created_at: string;
}

interface RevisionRound {
  id: number;
  public_id: string;
  project_id: number;
  round_number: number;
  title?: string;
  notes?: string;
  status: "REQUESTED" | "IN_PROGRESS" | "REVIEW" | "RESOLVED" | "CLOSED";
  media_asset?: {
    id: number;
    filename: string;
    original_name: string;
  };
  requested_by?: {
    name: string;
  };
  comments: RevisionComment[];
  created_at: string;
  project?: {
    id: number;
    project_number: string;
    name: string;
  };
}

interface ProjectOption {
  id: number;
  project_number: string;
  name: string;
}

function formatTimecode(seconds?: number): string {
  if (seconds === undefined || seconds === null) return "--:--";
  const m = Math.floor(seconds / 60);
  const s = Math.floor(seconds % 60);
  return `${String(m).padStart(2, "0")}:${String(s).padStart(2, "0")}`;
}

export default function AdminRevisionsHubPage() {
  const [projects, setProjects] = useState<ProjectOption[]>([]);
  const [revisions, setRevisions] = useState<RevisionRound[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedProjectId, setSelectedProjectId] = useState<string>("");
  const [statusFilter, setStatusFilter] = useState<string>("");

  const loadAllRevisions = async () => {
    setLoading(true);
    try {
      const projectsRes = await fetchApi<{ data: ProjectOption[] }>("/api/v1/admin/projects?per_page=100");
      const projectList = projectsRes.data || [];
      setProjects(projectList);

      const targetProjects = selectedProjectId
        ? projectList.filter((p) => String(p.id) === selectedProjectId)
        : projectList;

      // Fetch revisions for all target projects in parallel
      const revisionResults = await Promise.all(
        targetProjects.map(async (p) => {
          try {
            const res = await fetchApi<{ data: RevisionRound[] }>(`/api/v1/admin/projects/${p.id}/revisions`);
            return (res.data || []).map((r) => ({ ...r, project: p }));
          } catch {
            return [];
          }
        })
      );

      const allRevs = revisionResults.flat();
      setRevisions(allRevs);
    } catch (err) {
      console.error("Failed to load revisions:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadAllRevisions();
  }, [selectedProjectId]);

  const handleResolveComment = async (commentId: number) => {
    try {
      await fetchApi(`/api/v1/admin/revision-comments/${commentId}/resolve`, {
        method: "POST",
      });
      loadAllRevisions();
    } catch (err: any) {
      alert(err.message || "Failed to resolve feedback");
    }
  };

  const filteredRevisions = revisions.filter((r) => {
    if (!statusFilter) return true;
    return r.status === statusFilter;
  });

  const totalComments = filteredRevisions.reduce((acc, r) => acc + (r.comments?.length || 0), 0);
  const openComments = filteredRevisions.reduce(
    (acc, r) => acc + (r.comments?.filter((c) => c.status === "OPEN").length || 0),
    0
  );

  return (
    <>
      <div className="pagehead">
        <div>
          <div style={{ fontSize: 11, fontWeight: 700, color: "var(--teal)", letterSpacing: 1.2 }}>
            CLIENT REVIEW &amp; TIMESTAMPS
          </div>
          <h1 style={{ marginTop: 4 }}>🔁 Client Revisions &amp; Feedback Hub</h1>
          <p>Pusat pemantauan seluruh revisi draft video, feedback per-timecode, dan status approval</p>
        </div>
        <div style={{ display: "flex", gap: 10, alignItems: "center" }}>
          <select
            value={selectedProjectId}
            onChange={(e) => setSelectedProjectId(e.target.value)}
            style={{ padding: "8px 14px", borderRadius: 10, border: "1px solid var(--line)", fontSize: 13 }}
          >
            <option value="">Semua Proyek ({projects.length})</option>
            {projects.map((p) => (
              <option key={p.id} value={p.id}>
                {p.project_number} — {p.name}
              </option>
            ))}
          </select>

          <select
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            style={{ padding: "8px 14px", borderRadius: 10, border: "1px solid var(--line)", fontSize: 13 }}
          >
            <option value="">Semua Status Putaran</option>
            <option value="REQUESTED">Requested (Baru Masuk)</option>
            <option value="IN_PROGRESS">In Progress (Sedang Dikerjakan)</option>
            <option value="REVIEW">Under Review</option>
            <option value="RESOLVED">Resolved (Tuntas)</option>
            <option value="CLOSED">Closed</option>
          </select>

          <button className="btn btn-o" onClick={loadAllRevisions}>
            🔄 Refresh
          </button>
        </div>
      </div>

      {/* Summary KPI Cards */}
      <div className="grid g-stats" style={{ gridTemplateColumns: "repeat(3, 1fr)", marginBottom: 20 }}>
        <div className="card stat">
          <div>
            <div className="lb">Total Revision Rounds</div>
            <div className="vl">{filteredRevisions.length}</div>
            <div className="dl up">Putaran revisi aktif</div>
          </div>
          <div className="ico gv">🔁</div>
        </div>

        <div className="card stat">
          <div>
            <div className="lb">Open Timestamp Feedbacks</div>
            <div className="vl" style={{ color: openComments > 0 ? "var(--yellow)" : "var(--green)" }}>
              {openComments}
            </div>
            <div className="dl wr">Menunggu eksekusi editor</div>
          </div>
          <div className="ico go">💬</div>
        </div>

        <div className="card stat">
          <div>
            <div className="lb">Total Feedback Comments</div>
            <div className="vl">{totalComments}</div>
            <div className="dl up">Histori seluruh catatan</div>
          </div>
          <div className="ico gk">✨</div>
        </div>
      </div>

      {/* Revisions Feed */}
      {loading ? (
        <div className="empty" style={{ padding: "60px 0" }}>
          Memuat data revisi studio...
        </div>
      ) : filteredRevisions.length === 0 ? (
        <div className="empty" style={{ padding: "60px 0" }}>
          Belum ada putaran revisi atau feedback untuk filter ini.
        </div>
      ) : (
        <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
          {filteredRevisions.map((rev) => {
            const hasOpen = rev.comments?.some((c) => c.status === "OPEN");
            return (
              <div
                key={rev.id}
                className="card"
                style={{
                  padding: 20,
                  border: hasOpen ? "1px solid rgba(255, 199, 0, 0.3)" : "1px solid var(--line)",
                  background: "var(--card)",
                }}
              >
                {/* Revision Header */}
                <div
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    alignItems: "center",
                    paddingBottom: 14,
                    borderBottom: "1px solid rgba(255, 255, 255, 0.06)",
                    marginBottom: 14,
                  }}
                >
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
                      ROUND #{rev.round_number}
                    </span>
                    <div>
                      <div style={{ fontWeight: 700, fontSize: 15, color: "#FFFFFF" }}>
                        {rev.title || `Revision Round #${rev.round_number}`}
                      </div>
                      {rev.project && (
                        <Link
                          href={`/admin/projects/${rev.project.id}`}
                          style={{
                            fontSize: 12,
                            color: "var(--orange)",
                            textDecoration: "none",
                            fontWeight: 600,
                          }}
                        >
                          🎬 {rev.project.project_number} — {rev.project.name}
                        </Link>
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
                      style={{ fontWeight: 700 }}
                    >
                      {rev.status}
                    </span>
                    {rev.project && (
                      <Link
                        href={`/admin/projects/${rev.project.id}`}
                        className="btn btn-o"
                        style={{ padding: "6px 12px", fontSize: 12 }}
                      >
                        Buka Project Detail ↗
                      </Link>
                    )}
                  </div>
                </div>

                {/* Revision Notes */}
                {rev.notes && (
                  <div
                    style={{
                      padding: "10px 14px",
                      background: "rgba(255, 255, 255, 0.02)",
                      borderRadius: 8,
                      border: "1px solid rgba(255, 255, 255, 0.05)",
                      fontSize: 12,
                      color: "var(--muted)",
                      marginBottom: 14,
                    }}
                  >
                    📝 <strong>Catatan Umum:</strong> {rev.notes}
                  </div>
                )}

                {/* Feedback Comments Timeline */}
                <div>
                  <div style={{ fontSize: 12, fontWeight: 700, color: "var(--muted)", marginBottom: 10 }}>
                    💬 FEEDBACK PER-TIMECODE ({rev.comments?.length || 0})
                  </div>

                  {!rev.comments || rev.comments.length === 0 ? (
                    <div style={{ fontSize: 12, color: "var(--muted)", fontStyle: "italic" }}>
                      Belum ada poin catatan spesifik pada putaran ini.
                    </div>
                  ) : (
                    <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
                      {rev.comments.map((c) => {
                        const isOpen = c.status === "OPEN";
                        return (
                          <div
                            key={c.id}
                            style={{
                              display: "flex",
                              justifyContent: "space-between",
                              alignItems: "center",
                              padding: "10px 14px",
                              background: isOpen ? "rgba(255, 199, 0, 0.04)" : "rgba(255, 255, 255, 0.01)",
                              border: isOpen
                                ? "1px solid rgba(255, 199, 0, 0.2)"
                                : "1px solid rgba(255, 255, 255, 0.04)",
                              borderRadius: 8,
                              gap: 14,
                            }}
                          >
                            <div style={{ display: "flex", alignItems: "flex-start", gap: 12 }}>
                              <span
                                style={{
                                  fontSize: 11,
                                  fontFamily: "monospace",
                                  fontWeight: 800,
                                  padding: "3px 8px",
                                  borderRadius: 4,
                                  background: isOpen ? "var(--yellow)" : "rgba(255, 255, 255, 0.1)",
                                  color: isOpen ? "#000" : "var(--muted)",
                                }}
                              >
                                ⏱️ {formatTimecode(c.timecode_seconds)}
                              </span>
                              <div>
                                <div
                                  style={{
                                    fontSize: 13,
                                    fontWeight: 600,
                                    color: isOpen ? "#FFF" : "var(--muted)",
                                    textDecoration: isOpen ? "none" : "line-through",
                                  }}
                                >
                                  {c.comment}
                                </div>
                                <div style={{ fontSize: 10.5, color: "var(--muted)", marginTop: 3 }}>
                                  Ditulis oleh: {c.author?.name || "Client"} •{" "}
                                  {new Date(c.created_at).toLocaleDateString("id-ID", {
                                    day: "numeric",
                                    month: "short",
                                    hour: "2-digit",
                                    minute: "2-digit",
                                  })}
                                  {c.resolved_by && ` • Selesai oleh: ${c.resolved_by.name}`}
                                </div>
                              </div>
                            </div>

                            <div>
                              {isOpen ? (
                                <button
                                  className="btn btn-o"
                                  style={{ padding: "4px 10px", fontSize: 11, color: "var(--green)" }}
                                  onClick={() => handleResolveComment(c.id)}
                                >
                                  ✓ Tandai Selesai
                                </button>
                              ) : (
                                <span className="chip c-green" style={{ fontSize: 10 }}>
                                  ✓ Resolved
                                </span>
                              )}
                            </div>
                          </div>
                        );
                      })}
                    </div>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      )}
    </>
  );
}
