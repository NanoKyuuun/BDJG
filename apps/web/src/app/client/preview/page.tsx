"use client";

import { useEffect, useRef, useState } from "react";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface ProjectItem {
  id: number;
  public_id: string;
  project_number: string;
  name: string;
  status: string;
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
  if (seconds === undefined || seconds === null || isNaN(seconds)) return "00:00";
  const m = Math.floor(seconds / 60);
  const s = Math.floor(seconds % 60);
  return `${String(m).padStart(2, "0")}:${String(s).padStart(2, "0")}`;
}

export default function ClientPreviewReviewPage() {
  const [projects, setProjects] = useState<ProjectItem[]>([]);
  const [selectedProjectId, setSelectedProjectId] = useState<number | null>(null);
  const [revisions, setRevisions] = useState<RevisionRound[]>([]);
  const [activeRevision, setActiveRevision] = useState<RevisionRound | null>(null);
  const [loading, setLoading] = useState(true);

  // Video State
  const videoRef = useRef<HTMLVideoElement>(null);
  const [currentTime, setCurrentTime] = useState(0);
  const [isPlaying, setIsPlaying] = useState(false);

  // Feedback State
  const [commentText, setCommentText] = useState("");
  const [submittingComment, setSubmittingComment] = useState(false);
  const [toast, setToast] = useState<{ msg: string; type: "success" | "error" } | null>(null);
  const [approvedSuccess, setApprovedSuccess] = useState(false);

  const showToast = (msg: string, type: "success" | "error" = "success") => {
    setToast({ msg, type });
    setTimeout(() => setToast(null), 4000);
  };

  const loadClientProjects = async () => {
    try {
      setLoading(true);
      const res = await fetchApi<{ data: ProjectItem[] }>("/api/v1/client/projects");
      const list = res.data || [];
      setProjects(list);
      if (list.length > 0) {
        setSelectedProjectId(list[0].id);
        loadRevisionsForProject(list[0].id);
      } else {
        setLoading(false);
      }
    } catch (err) {
      console.error("Failed to load client projects:", err);
      setLoading(false);
    }
  };

  const loadRevisionsForProject = async (projectId: number) => {
    try {
      setLoading(true);
      const res = await fetchApi<{ data: RevisionRound[] }>(`/api/v1/client/projects/${projectId}/revisions`);
      const revList = res.data || [];
      setRevisions(revList);
      if (revList.length > 0) {
        setActiveRevision(revList[0]);
      } else {
        setActiveRevision(null);
      }
    } catch (err) {
      console.error("Failed to load project revisions:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadClientProjects();
  }, []);

  const handleTimeUpdate = () => {
    if (videoRef.current) {
      setCurrentTime(videoRef.current.currentTime);
    }
  };

  const handleJumpToTimecode = (seconds: number) => {
    if (videoRef.current) {
      videoRef.current.currentTime = seconds;
      videoRef.current.play().catch(() => {});
      setIsPlaying(true);
    }
  };

  const handleAddComment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!commentText.trim()) return;
    if (!selectedProjectId) return;

    setSubmittingComment(true);
    try {
      let targetRevisionId = activeRevision?.id;

      // If no active revision round exists yet, create one first
      if (!targetRevisionId) {
        const createRes = await fetchApi<{ data: RevisionRound }>(`/api/v1/client/projects/${selectedProjectId}/revisions`, {
          method: "POST",
          body: JSON.stringify({
            title: "Client Feedback Round 1",
            notes: "Initial feedback notes submitted by client on video preview.",
          }),
        });
        targetRevisionId = createRes.data.id;
      }

      // Add timestamped comment to revision
      await fetchApi(`/api/v1/client/revisions/${targetRevisionId}/comments`, {
        method: "POST",
        body: JSON.stringify({
          comment: commentText.trim(),
          timecode_seconds: Math.floor(currentTime),
        }),
      });

      showToast(`Catatan timecode [${formatTimecode(currentTime)}] berhasil dikirim ke Worker!`);
      setCommentText("");
      loadRevisionsForProject(selectedProjectId);
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Gagal mengirim catatan.";
      showToast(msg, "error");
    } finally {
      setSubmittingComment(false);
    }
  };

  const handleApproveCut = () => {
    if (!confirm("Apakah Anda puas dengan hasil karya ini dan ingin menyetujuinya sebagai Master Final?")) return;
    setApprovedSuccess(true);
    showToast("Karya berhasil disetujui! Tim studio akan mempersiapkan paket serah terima final 4K.");
  };

  const selectedProject = projects.find((p) => p.id === selectedProjectId);

  return (
    <>
      {toast && (
        <div
          style={{
            position: "fixed",
            bottom: 24,
            right: 24,
            zIndex: 9999,
            padding: "12px 20px",
            borderRadius: 8,
            fontSize: 13,
            fontWeight: 600,
            background: toast.type === "success" ? "#1b4332" : "#5c1d1d",
            color: toast.type === "success" ? "#80ed99" : "#ffb4b4",
            border: `1px solid ${toast.type === "success" ? "#2d6a4f" : "#802020"}`,
            boxShadow: "0 8px 24px rgba(0,0,0,0.5)",
          }}
        >
          {toast.type === "success" ? "✓ " : "⚠ "}
          {toast.msg}
        </div>
      )}

      <div className="pagehead">
        <div>
          <div style={{ fontSize: 11, fontWeight: 700, color: "var(--orange)", letterSpacing: 1.2 }}>
            CLIENT REVIEW SUITE
          </div>
          <h1 style={{ marginTop: 4 }}>🎞️ Video Preview &amp; Feedback Reviewer</h1>
          <p>Tonton draft karya dari tim worker, beri catatan revisi pada detik tertentu, atau setujui karya akhir</p>
        </div>

        {projects.length > 1 && (
          <div>
            <select
              className="form-control"
              value={selectedProjectId || ""}
              onChange={(e) => {
                const id = Number(e.target.value);
                setSelectedProjectId(id);
                loadRevisionsForProject(id);
              }}
              style={{ minWidth: 240 }}
            >
              {projects.map((p) => (
                <option key={p.id} value={p.id}>
                  {p.project_number} — {p.name}
                </option>
              ))}
            </select>
          </div>
        )}
      </div>

      {loading ? (
        <div className="empty" style={{ padding: "60px 0" }}>
          Memuat studio preview...
        </div>
      ) : projects.length === 0 ? (
        <div className="card" style={{ padding: 40, textAlign: "center" }}>
          <div style={{ fontSize: 40, marginBottom: 12 }}>🎬</div>
          <h3 style={{ fontSize: 18, fontWeight: 700 }}>Belum Ada Proyek Aktif</h3>
          <p style={{ color: "var(--muted)", fontSize: 13, marginTop: 4, maxWidth: 460, marginInline: "auto" }}>
            Anda belum memiliki proyek dalam tahap review. Proyek akan muncul setelah penawaran disetujui dan proses syuting dimulai.
          </p>
        </div>
      ) : (
        <div style={{ display: "grid", gridTemplateColumns: "1.4fr 1fr", gap: 24, alignItems: "start" }}>
          {/* LEFT: Video Player & Timecode Controls */}
          <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
            <div
              className="card"
              style={{
                padding: 0,
                overflow: "hidden",
                background: "#000000",
                border: "1px solid rgba(255,255,255,0.12)",
                boxShadow: "0 12px 36px rgba(0,0,0,0.6)",
              }}
            >
              {/* Header Info */}
              <div
                style={{
                  padding: "12px 16px",
                  background: "rgba(255,255,255,0.04)",
                  borderBottom: "1px solid rgba(255,255,255,0.08)",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "space-between",
                }}
              >
                <div>
                  <span style={{ fontSize: 12, fontWeight: 700, color: "#FFFFFF" }}>
                    {selectedProject?.name}
                  </span>
                  <span style={{ fontSize: 11, color: "var(--muted)", marginLeft: 8 }}>
                    ({selectedProject?.project_number})
                  </span>
                </div>
                <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                  <span className="chip c-blue" style={{ fontSize: 10 }}>4K HDR Draft</span>
                  <span
                    style={{
                      fontFamily: "var(--font-mono, monospace)",
                      fontSize: 13,
                      fontWeight: 800,
                      color: "#5c7cff",
                      background: "rgba(92, 124, 255, 0.15)",
                      padding: "2px 8px",
                      borderRadius: 4,
                    }}
                  >
                    ⏱ {formatTimecode(currentTime)}
                  </span>
                </div>
              </div>

              {/* Video Player */}
              <div style={{ position: "relative", width: "100%", aspectRatio: "16/9", background: "#050505" }}>
                <video
                  ref={videoRef}
                  src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4"
                  poster="https://images.unsplash.com/photo-1519741497674-611481863552?q=80&w=1200&auto=format&fit=crop"
                  controls
                  onTimeUpdate={handleTimeUpdate}
                  onPlay={() => setIsPlaying(true)}
                  onPause={() => setIsPlaying(false)}
                  style={{ width: "100%", height: "100%", objectFit: "contain" }}
                />
              </div>

              {/* Player Bottom Bar */}
              <div
                style={{
                  padding: "14px 18px",
                  background: "rgba(15,15,15,0.9)",
                  borderTop: "1px solid rgba(255,255,255,0.08)",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "space-between",
                }}
              >
                <div style={{ fontSize: 12, color: "var(--muted)" }}>
                  Putaran Revisi: <b style={{ color: "#FFF" }}>Round #{activeRevision?.round_number || 1}</b>
                </div>

                <div style={{ display: "flex", gap: 10 }}>
                  <button
                    className="btn btn-secondary"
                    style={{ fontSize: 12 }}
                    onClick={() => {
                      if (videoRef.current) {
                        videoRef.current.currentTime = Math.max(0, videoRef.current.currentTime - 5);
                      }
                    }}
                  >
                    ⏪ -5s
                  </button>
                  <button
                    className="btn btn-secondary"
                    style={{ fontSize: 12 }}
                    onClick={() => {
                      if (videoRef.current) {
                        videoRef.current.currentTime += 5;
                      }
                    }}
                  >
                    +5s ⏩
                  </button>
                </div>
              </div>
            </div>

            {/* Approval Callout */}
            {approvedSuccess ? (
              <div
                style={{
                  padding: 20,
                  background: "rgba(46, 125, 50, 0.15)",
                  border: "1px solid #2e7d32",
                  borderRadius: 10,
                  textAlign: "center",
                }}
              >
                <div style={{ fontSize: 28, marginBottom: 6 }}>🎉</div>
                <div style={{ fontWeight: 800, fontSize: 16, color: "#81c784" }}>
                  Karya Telah Anda Setujui Sebagai Final!
                </div>
                <p style={{ fontSize: 12.5, color: "#c8e6c9", marginTop: 4 }}>
                  Tim kami sedang melakukan render uncompressed 4K master. File final akan tersedia di menu <b>Files & Deliverables</b>.
                </p>
              </div>
            ) : (
              <div
                className="card"
                style={{
                  padding: 18,
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "space-between",
                  background: "linear-gradient(135deg, rgba(30,30,40,0.8), rgba(20,20,25,0.8))",
                  border: "1px solid rgba(92, 124, 255, 0.2)",
                }}
              >
                <div>
                  <div style={{ fontWeight: 700, fontSize: 14, color: "#FFF" }}>Sudah Puas dengan Video Ini?</div>
                  <div style={{ fontSize: 12, color: "var(--muted)", marginTop: 2 }}>
                    Klik tombol setujui jika tidak memerlukan revisi tambahan.
                  </div>
                </div>
                <button
                  className="btn btn-primary"
                  onClick={handleApproveCut}
                  style={{
                    background: "#2e7d32",
                    borderColor: "#388e3c",
                    color: "#FFFFFF",
                    fontWeight: 800,
                    padding: "10px 18px",
                  }}
                >
                  ✓ Setujui Karya Akhir (Approve)
                </button>
              </div>
            )}
          </div>

          {/* RIGHT: Timecode Feedback & Comments Hub */}
          <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
            {/* Add Timecode Note Card */}
            <div className="card" style={{ padding: 18 }}>
              <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 12 }}>
                <h3 style={{ fontSize: 15, fontWeight: 700 }}>✍️ Beri Catatan Revisi</h3>
                <span
                  style={{
                    fontFamily: "var(--font-mono, monospace)",
                    fontSize: 12,
                    fontWeight: 700,
                    color: "#f97316",
                    background: "rgba(249, 115, 22, 0.12)",
                    padding: "2px 8px",
                    borderRadius: 4,
                  }}
                >
                  Pin Timecode: {formatTimecode(currentTime)}
                </span>
              </div>

              <form onSubmit={handleAddComment}>
                <textarea
                  className="form-control"
                  rows={3}
                  value={commentText}
                  onChange={(e) => setCommentText(e.target.value)}
                  placeholder={`Contoh: Di menit ${formatTimecode(currentTime)}, tolong transisi dibuat lebih smooth atau tone warna diperhangat...`}
                  required
                  style={{ fontSize: 12.5, marginBottom: 12 }}
                />
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                  <span style={{ fontSize: 11, color: "var(--muted)" }}>
                    Catatan otomatis ditautkan ke detik <b>{formatTimecode(currentTime)}</b>
                  </span>
                  <button type="submit" className="btn btn-primary" disabled={submittingComment} style={{ fontSize: 12 }}>
                    {submittingComment ? "Mengirim..." : "Kirim Catatan Timecode ↗"}
                  </button>
                </div>
              </form>
            </div>

            {/* List of Timecode Feedbacks */}
            <div className="card" style={{ display: "flex", flexDirection: "column" }}>
              <div className="chead" style={{ borderBottom: "1px solid rgba(255,255,255,0.08)" }}>
                <h3>Daftar Catatan Revisi ({activeRevision?.comments?.length || 0})</h3>
                <span style={{ fontSize: 11, color: "var(--muted)" }}>Klik timecode untuk loncat ke detik video</span>
              </div>

              <div className="cbody" style={{ padding: 0, maxHeight: 380, overflowY: "auto" }}>
                {!activeRevision || !activeRevision.comments || activeRevision.comments.length === 0 ? (
                  <div className="empty" style={{ padding: "30px 16px" }}>
                    Belum ada catatan timecode pada draft ini. Jeda video pada detik yang ingin dikomentari lalu ketik catatan di atas.
                  </div>
                ) : (
                  <div>
                    {activeRevision.comments.map((c) => (
                      <div
                        key={c.id}
                        style={{
                          padding: "12px 16px",
                          borderBottom: "1px solid rgba(255,255,255,0.04)",
                          display: "flex",
                          gap: 12,
                          alignItems: "flex-start",
                        }}
                      >
                        <button
                          type="button"
                          onClick={() => handleJumpToTimecode(c.timecode_seconds)}
                          style={{
                            fontFamily: "var(--font-mono, monospace)",
                            fontSize: 11,
                            fontWeight: 800,
                            padding: "4px 8px",
                            borderRadius: 4,
                            background: "rgba(92, 124, 255, 0.15)",
                            color: "#8AB6FF",
                            border: "1px solid rgba(92, 124, 255, 0.3)",
                            cursor: "pointer",
                            flexShrink: 0,
                          }}
                          title="Klik untuk putar pada timecode ini"
                        >
                          ▶ {formatTimecode(c.timecode_seconds)}
                        </button>

                        <div style={{ flex: 1 }}>
                          <div style={{ fontSize: 13, color: "#f2f1ed", lineHeight: 1.4 }}>
                            {c.comment}
                          </div>
                          <div style={{ display: "flex", alignItems: "center", gap: 8, marginTop: 4, fontSize: 10.5, color: "var(--muted)" }}>
                            <span>Oleh: {c.author?.name || "Klien"}</span>
                            <span>•</span>
                            <span className={`chip ${c.status === "RESOLVED" ? "c-green" : "c-yellow"}`} style={{ fontSize: 9 }}>
                              {c.status === "RESOLVED" ? "Sudah Diperbaiki Worker" : "Sedang Dikerjakan Worker"}
                            </span>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
