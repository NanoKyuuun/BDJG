"use client";

import { useEffect, useRef, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface MediaAsset {
  id: number;
  filename: string;
  original_name: string;
  category: string;
  visibility: string;
  mime_type: string;
  size_bytes: number;
  thumbnail_url?: string;
  preview_url?: string;
  created_at: string;
}

interface RevisionComment {
  id: number;
  timecode_seconds: number;
  frame_number?: number;
  coordinates?: { x: number; y: number };
  comment: string;
  status: string;
  author?: { name: string };
  created_at: string;
}

interface RevisionRound {
  id: number;
  round_number: number;
  title: string;
  status: string;
  notes?: string;
  comments: RevisionComment[];
  created_at: string;
}

interface DeliveryPackage {
  id: number;
  title: string;
  status: string;
  total_size_bytes: number;
  file_count: number;
  download_count: number;
  expires_at?: string;
  notes?: string;
  created_at: string;
}

interface ProjectDetail {
  id: number;
  project_number: string;
  name: string;
  status: string;
  shoot_date?: string;
  deadline?: string;
  contract_value: number;
}

export default function ClientProjectReviewHub() {
  const params = useParams();
  const projectId = params?.id as string;
  const router = useRouter();

  const [project, setProject] = useState<ProjectDetail | null>(null);
  const [mediaAssets, setMediaAssets] = useState<MediaAsset[]>([]);
  const [selectedAsset, setSelectedAsset] = useState<MediaAsset | null>(null);
  const [streamUrl, setStreamUrl] = useState<string | null>(null);
  const [revisions, setRevisions] = useState<RevisionRound[]>([]);
  const [deliveries, setDeliveries] = useState<DeliveryPackage[]>([]);
  const [loading, setLoading] = useState(true);

  // Video Player state
  const videoRef = useRef<HTMLVideoElement>(null);
  const [currentTime, setCurrentTime] = useState(0);
  const [duration, setDuration] = useState(0);
  const [isPlaying, setIsPlaying] = useState(false);
  const [activePin, setActivePin] = useState<{ x: number; y: number } | null>(null);

  // Feedback form
  const [commentText, setCommentText] = useState("");
  const [submittingComment, setSubmittingComment] = useState(false);
  const [commentFilter, setCommentFilter] = useState<"ALL" | "OPEN" | "RESOLVED">("ALL");

  // New Round modal
  const [showNewRoundModal, setShowNewRoundModal] = useState(false);
  const [newRoundTitle, setNewRoundTitle] = useState("");
  const [newRoundNotes, setNewRoundNotes] = useState("");

  // Download state
  const [downloadingPackageId, setDownloadingPackageId] = useState<number | null>(null);

  useEffect(() => {
    if (!projectId) return;

    async function loadData() {
      try {
        setLoading(true);
        // Load Project
        const projRes = await fetchApi<{ data: ProjectDetail }>(`/api/v1/client/projects/${projectId}`);
        setProject(projRes.data);

        // Load Media Assets (Client Preview or Final)
        const mediaRes = await fetchApi<{ data: MediaAsset[] }>(`/api/v1/client/projects/${projectId}/media`);
        setMediaAssets(mediaRes.data || []);
        if (mediaRes.data && mediaRes.data.length > 0) {
          const firstVideo = mediaRes.data.find((m) => m.mime_type.startsWith("video/")) || mediaRes.data[0];
          setSelectedAsset(firstVideo);
          loadSignedUrl(firstVideo.id);
        }

        // Load Revisions
        const revRes = await fetchApi<{ data: RevisionRound[] }>(`/api/v1/client/projects/${projectId}/revisions`);
        setRevisions(revRes.data || []);

        // Load Deliveries
        const delRes = await fetchApi<{ data: DeliveryPackage[] }>(`/api/v1/client/projects/${projectId}/deliveries`);
        setDeliveries(delRes.data || []);
      } catch (err) {
        console.error("Failed to load project details:", err);
      } finally {
        setLoading(false);
      }
    }

    loadData();
  }, [projectId]);

  async function loadSignedUrl(assetId: number) {
    try {
      const res = await fetchApi<{ url: string }>(`/api/v1/client/media/${assetId}/signed-url`);
      setStreamUrl(res.url);
    } catch (err) {
      console.error("Failed to load signed stream URL:", err);
    }
  }

  function handleVideoClick(e: React.MouseEvent<HTMLDivElement>) {
    const rect = e.currentTarget.getBoundingClientRect();
    const x = Math.round(((e.clientX - rect.left) / rect.width) * 100) / 100;
    const y = Math.round(((e.clientY - rect.top) / rect.height) * 100) / 100;

    if (videoRef.current) {
      videoRef.current.pause();
      setIsPlaying(false);
      setCurrentTime(videoRef.current.currentTime);
    }

    setActivePin({ x, y });
  }

  function seekTo(seconds: number) {
    if (videoRef.current) {
      videoRef.current.currentTime = seconds;
      setCurrentTime(seconds);
    }
  }

  const formatTimecode = (secs: number) => {
    const m = Math.floor(secs / 60);
    const s = Math.floor(secs % 60);
    const ms = Math.floor((secs % 1) * 100);
    return `${m.toString().padStart(2, "0")}:${s.toString().padStart(2, "0")}.${ms.toString().padStart(2, "0")}`;
  };

  async function handleSubmitComment(e: React.FormEvent) {
    e.preventDefault();
    if (!commentText.trim()) return;

    let activeRound = revisions.find((r) => r.status === "OPEN" || r.status === "IN_PROGRESS");
    if (!activeRound) {
      // Auto-create round if none exists
      try {
        const createRoundRes = await fetchApi<{ data: RevisionRound }>(`/api/v1/client/projects/${projectId}/revisions`, {
          method: "POST",
          body: JSON.stringify({
            media_asset_id: selectedAsset?.id,
            title: `Revision Round #${revisions.length + 1}`,
          }),
        });
        activeRound = createRoundRes.data;
        setRevisions([activeRound, ...revisions]);
      } catch (err: any) {
        alert("Failed to initialize revision round: " + err.message);
        return;
      }
    }

    try {
      setSubmittingComment(true);
      const res = await fetchApi<{ data: RevisionComment }>(`/api/v1/client/revisions/${activeRound.id}/comments`, {
        method: "POST",
        body: JSON.stringify({
          timecode_seconds: Math.round(currentTime * 100) / 100,
          frame_number: Math.floor(currentTime * 24),
          coordinates: activePin || { x: 0.5, y: 0.5 },
          comment: commentText,
        }),
      });

      // Update local state
      const newComment = res.data;
      setRevisions((prev) =>
        prev.map((r) =>
          r.id === activeRound!.id ? { ...r, comments: [...(r.comments || []), newComment] } : r
        )
      );

      setCommentText("");
      setActivePin(null);
    } catch (err: any) {
      alert("Failed to post comment: " + err.message);
    } finally {
      setSubmittingComment(false);
    }
  }

  async function handleDownloadPackage(packageId: number) {
    try {
      setDownloadingPackageId(packageId);
      const res = await fetchApi<{ download_url: string }>(`/api/v1/client/deliveries/${packageId}/download-url`);
      if (res.download_url) {
        window.open(res.download_url, "_blank");
      }
    } catch (err: any) {
      alert("Failed to generate download link: " + err.message);
    } finally {
      setDownloadingPackageId(null);
    }
  }

  // Flatten comments of active round
  const activeRound = revisions[0] || null;
  const filteredComments = (activeRound?.comments || []).filter((c) => {
    if (commentFilter === "OPEN") return c.status === "OPEN" || c.status === "IN_PROGRESS";
    if (commentFilter === "RESOLVED") return c.status === "RESOLVED";
    return true;
  });

  if (loading) {
    return (
      <div className="p-12 text-center text-zinc-500">
        <div className="animate-spin text-3xl mb-3">⏳</div>
        <p className="text-sm">Loading Project Review Workspace...</p>
      </div>
    );
  }

  if (!project) {
    return (
      <div className="p-8 bg-zinc-900 border border-zinc-800 rounded-xl text-center">
        <h2 className="text-lg font-bold text-white">Project Not Found</h2>
        <Link href="/client/projects" className="mt-4 inline-block text-amber-500 text-sm">
          ← Back to Projects
        </Link>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      {/* Header Bar */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-zinc-800">
        <div>
          <div className="flex items-center gap-3">
            <Link href="/client/projects" className="text-xs text-zinc-400 hover:text-white transition">
              ← Projects
            </Link>
            <span className="text-zinc-700">/</span>
            <span className="text-xs font-mono text-amber-500 font-semibold">{project.project_number}</span>
          </div>
          <h1 className="text-2xl font-extrabold tracking-tight text-white mt-1">{project.name}</h1>
        </div>

        <div className="flex items-center gap-3">
          <button
            onClick={async () => {
              if (!confirm("Buat pesanan baru dengan menduplikasi rincian brief project ini?")) return;
              try {
                const res = await fetch(`/api/v1/client/projects/${projectId}/reorder`, {
                  method: "POST",
                  credentials: "include",
                  headers: { Accept: "application/json" },
                });
                if (res.ok) {
                  const json = await res.json();
                  const newOrderId = json.data?.id;
                  router.push(`/client/orders/${newOrderId}/brief`);
                } else {
                  alert("Gagal melakukan re-order.");
                }
              } catch (e) {
                console.error(e);
                alert("Terjadi kesalahan.");
              }
            }}
            className="px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-zinc-900 hover:bg-zinc-800 text-amber-300 border border-amber-500/30 transition-colors flex items-center gap-1.5"
          >
            <span>🔄</span> Pesan Lagi (Re-order)
          </button>
          <span className="px-3 py-1.5 text-xs font-semibold rounded-full bg-zinc-800 text-zinc-300 border border-zinc-700">
            {project.status}
          </span>
        </div>
      </div>

      {/* FINAL MASTER DELIVERABLES HANDOVER BANNER (If Ready) */}
      {deliveries.length > 0 && (
        <div className="p-6 bg-gradient-to-r from-amber-950/40 via-amber-900/20 to-zinc-900 border-2 border-amber-500/60 rounded-2xl shadow-2xl relative overflow-hidden">
          <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div>
              <div className="flex items-center gap-2">
                <span className="text-amber-400 text-xl">🏆</span>
                <span className="text-xs uppercase tracking-widest font-extrabold text-amber-400">
                  Official Master Deliverables Ready
                </span>
              </div>
              <h3 className="text-xl font-extrabold text-white mt-1">
                {deliveries[0].title}
              </h3>
              <p className="text-xs text-zinc-400 mt-1">
                Includes {deliveries[0].file_count} master assets (
                {Math.round((deliveries[0].total_size_bytes / 1048576) * 100) / 100} MB). High-speed signed handover.
              </p>
            </div>

            <button
              onClick={() => handleDownloadPackage(deliveries[0].id)}
              disabled={downloadingPackageId === deliveries[0].id}
              className="px-6 py-3 bg-amber-500 hover:bg-amber-400 active:scale-95 text-black font-extrabold text-sm rounded-xl shadow-lg transition duration-150 flex items-center gap-2 disabled:opacity-50"
            >
              <span>{downloadingPackageId === deliveries[0].id ? "Preparing Link..." : "⚡ Download Master Package (4K ZIP)"}</span>
            </button>
          </div>
        </div>
      )}

      {/* WORKSPACE GRID: Video Review Player (Left) + Feedback Drawer (Right) */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {/* LEFT: Video Player & Controls (7 Cols) */}
        <div className="lg:col-span-7 space-y-4">
          <div className="bg-black border border-zinc-800 rounded-2xl overflow-hidden shadow-2xl relative group">
            {/* Video Canvas Container */}
            <div
              className="relative w-full aspect-video bg-black cursor-crosshair flex items-center justify-center select-none"
              onClick={handleVideoClick}
            >
              {streamUrl ? (
                <video
                  ref={videoRef}
                  src={streamUrl}
                  className="w-full h-full object-contain"
                  onTimeUpdate={() => videoRef.current && setCurrentTime(videoRef.current.currentTime)}
                  onLoadedMetadata={() => videoRef.current && setDuration(videoRef.current.duration)}
                  onPlay={() => setIsPlaying(true)}
                  onPause={() => setIsPlaying(false)}
                  controls={false}
                />
              ) : (
                <div className="text-zinc-600 text-sm text-center p-8">
                  <span className="text-3xl block mb-2">🎞️</span>
                  <span>No preview video uploaded or awaiting release from studio.</span>
                </div>
              )}

              {/* Pin Overlay Marker */}
              {activePin && (
                <div
                  className="absolute w-6 h-6 -ml-3 -mt-3 rounded-full bg-amber-500 text-black font-extrabold text-xs flex items-center justify-center ring-4 ring-amber-500/40 animate-bounce pointer-events-none shadow-xl"
                  style={{ left: `${activePin.x * 100}%`, top: `${activePin.y * 100}%` }}
                >
                  📍
                </div>
              )}
            </div>

            {/* Custom Transport Controls */}
            {streamUrl && (
              <div className="p-4 bg-zinc-950 border-t border-zinc-800/80 flex flex-col gap-3">
                {/* Scrubber Timeline */}
                <div className="relative w-full flex items-center">
                  <input
                    type="range"
                    min={0}
                    max={duration || 100}
                    step={0.01}
                    value={currentTime}
                    onChange={(e) => seekTo(parseFloat(e.target.value))}
                    className="w-full h-1.5 bg-zinc-800 rounded-lg appearance-none cursor-pointer accent-amber-500"
                  />

                  {/* Comment Markers on timeline */}
                  {(activeRound?.comments || []).map((c) => (
                    <div
                      key={c.id}
                      onClick={() => seekTo(c.timecode_seconds)}
                      title={`${formatTimecode(c.timecode_seconds)}: ${c.comment}`}
                      className={`absolute top-0 w-2 h-2 -mt-0.5 rounded-full cursor-pointer transition transform hover:scale-150 ${
                        c.status === "RESOLVED" ? "bg-emerald-400" : "bg-amber-400 ring-2 ring-amber-400/40"
                      }`}
                      style={{ left: `${(c.timecode_seconds / (duration || 1)) * 100}%` }}
                    />
                  ))}
                </div>

                <div className="flex items-center justify-between text-xs text-zinc-400">
                  <div className="flex items-center gap-4">
                    <button
                      onClick={() => {
                        if (videoRef.current) {
                          if (isPlaying) videoRef.current.pause();
                          else videoRef.current.play();
                        }
                      }}
                      className="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 text-white font-semibold rounded-md transition"
                    >
                      {isPlaying ? "⏸ Pause" : "▶ Play"}
                    </button>

                    <span className="font-mono text-white text-sm font-bold">
                      {formatTimecode(currentTime)} <span className="text-zinc-500 text-xs font-normal">/ {formatTimecode(duration)}</span>
                    </span>
                  </div>

                  <span className="text-zinc-500 italic hidden sm:inline">
                    💡 Click anywhere on the video frame to drop a feedback pin
                  </span>
                </div>
              </div>
            )}
          </div>

          {/* Media Drafts Switcher */}
          {mediaAssets.length > 1 && (
            <div className="p-4 bg-zinc-900/60 border border-zinc-800 rounded-xl">
              <span className="text-xs uppercase tracking-wider text-zinc-500 font-bold block mb-2">Available Drafts</span>
              <div className="flex gap-2 overflow-x-auto pb-1">
                {mediaAssets.map((asset) => (
                  <button
                    key={asset.id}
                    onClick={() => {
                      setSelectedAsset(asset);
                      loadSignedUrl(asset.id);
                    }}
                    className={`px-3 py-2 text-xs rounded-lg border font-medium whitespace-nowrap transition ${
                      selectedAsset?.id === asset.id
                        ? "bg-amber-500 text-black border-amber-500 font-bold"
                        : "bg-zinc-800/80 text-zinc-300 border-zinc-700 hover:bg-zinc-800"
                    }`}
                  >
                    {asset.original_name}
                  </button>
                ))}
              </div>
            </div>
          )}
        </div>

        {/* RIGHT: Timecode Revision Comments & Form (5 Cols) */}
        <div className="lg:col-span-5 flex flex-col h-full space-y-4">
          <div className="bg-zinc-900 border border-zinc-800 rounded-2xl p-5 flex flex-col flex-grow shadow-xl">
            {/* Round Title & Status */}
            <div className="flex items-center justify-between pb-4 border-b border-zinc-800">
              <div>
                <span className="text-xs text-zinc-500 uppercase font-mono font-bold tracking-wider">
                  {activeRound ? `Round #${activeRound.round_number}` : "Revision Feedback"}
                </span>
                <h3 className="text-base font-bold text-white mt-0.5">
                  {activeRound?.title || "Draft Feedback Notes"}
                </h3>
              </div>

              <div className="flex items-center gap-1 bg-zinc-950 p-1 rounded-lg border border-zinc-800 text-xs">
                {(["ALL", "OPEN", "RESOLVED"] as const).map((tab) => (
                  <button
                    key={tab}
                    onClick={() => setCommentFilter(tab)}
                    className={`px-2.5 py-1 rounded-md text-xs font-semibold transition ${
                      commentFilter === tab ? "bg-amber-500 text-black" : "text-zinc-400 hover:text-white"
                    }`}
                  >
                    {tab}
                  </button>
                ))}
              </div>
            </div>

            {/* Comments List */}
            <div className="flex-grow overflow-y-auto max-h-[380px] my-4 space-y-3 pr-1">
              {filteredComments.length === 0 ? (
                <div className="p-8 text-center text-zinc-500 text-xs">
                  <span>No feedback comments yet. Pause the video and write your notes below.</span>
                </div>
              ) : (
                filteredComments.map((comment) => (
                  <div
                    key={comment.id}
                    onClick={() => seekTo(comment.timecode_seconds)}
                    className="p-3.5 bg-zinc-950/80 hover:bg-zinc-950 border border-zinc-800/80 hover:border-zinc-700 rounded-xl cursor-pointer transition group"
                  >
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <span className="px-2 py-0.5 text-xs font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded">
                          ⏱ {formatTimecode(comment.timecode_seconds)}
                        </span>
                        <span className="text-xs font-semibold text-zinc-300">{comment.author?.name || "Client"}</span>
                      </div>
                      <span
                        className={`text-[10px] uppercase font-extrabold px-2 py-0.5 rounded-full ${
                          comment.status === "RESOLVED"
                            ? "bg-emerald-950 text-emerald-400 border border-emerald-800"
                            : "bg-amber-950 text-amber-400 border border-amber-800"
                        }`}
                      >
                        {comment.status}
                      </span>
                    </div>

                    <p className="text-xs text-zinc-300 mt-2 leading-relaxed">{comment.comment}</p>
                  </div>
                ))
              )}
            </div>

            {/* Submit Comment Form */}
            <form onSubmit={handleSubmitComment} className="pt-4 border-t border-zinc-800 space-y-3">
              <div className="flex items-center justify-between text-xs">
                <span className="text-zinc-400">
                  Comment at <strong className="text-amber-400 font-mono">{formatTimecode(currentTime)}</strong>
                  {activePin && <span className="ml-1 text-emerald-400">(Pin set 📍)</span>}
                </span>
                {activePin && (
                  <button
                    type="button"
                    onClick={() => setActivePin(null)}
                    className="text-zinc-500 hover:text-red-400 text-xs"
                  >
                    Clear Pin
                  </button>
                )}
              </div>

              <textarea
                value={commentText}
                onChange={(e) => setCommentText(e.target.value)}
                placeholder="Type frame-specific feedback (e.g. adjust color grade, cut 2s earlier)..."
                rows={2}
                className="w-full px-3 py-2 bg-zinc-950 border border-zinc-800 focus:border-amber-500 rounded-xl text-xs text-white placeholder-zinc-600 focus:outline-none transition resize-none"
              />

              <button
                type="submit"
                disabled={submittingComment || !commentText.trim()}
                className="w-full py-2.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-black font-extrabold text-xs rounded-xl shadow transition"
              >
                {submittingComment ? "Posting Feedback..." : "Submit Timecode Feedback"}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
