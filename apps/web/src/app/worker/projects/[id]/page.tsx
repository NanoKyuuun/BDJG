"use client";

import { useEffect, useRef, useState } from "react";
import { useParams } from "next/navigation";
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
  comments: RevisionComment[];
}

interface ProjectDetail {
  id: number;
  project_number: string;
  name: string;
  status: string;
  shoot_date?: string;
  deadline?: string;
}

export default function WorkerProjectWorkspace() {
  const params = useParams();
  const projectId = params?.id as string;

  const [project, setProject] = useState<ProjectDetail | null>(null);
  const [mediaAssets, setMediaAssets] = useState<MediaAsset[]>([]);
  const [selectedAsset, setSelectedAsset] = useState<MediaAsset | null>(null);
  const [streamUrl, setStreamUrl] = useState<string | null>(null);
  const [revisions, setRevisions] = useState<RevisionRound[]>([]);
  const [loading, setLoading] = useState(true);

  // Video State
  const videoRef = useRef<HTMLVideoElement>(null);
  const [currentTime, setCurrentTime] = useState(0);
  const [duration, setDuration] = useState(0);
  const [isPlaying, setIsPlaying] = useState(false);

  // Action states
  const [resolvingCommentId, setResolvingCommentId] = useState<number | null>(null);
  const [uploadModalOpen, setUploadModalOpen] = useState(false);
  const [uploadFilename, setUploadFilename] = useState("");
  const [uploadCategory, setUploadCategory] = useState("INTERNAL_DRAFT");
  const [uploading, setUploading] = useState(false);

  useEffect(() => {
    if (!projectId) return;

    async function loadData() {
      try {
        setLoading(true);
        const projRes = await fetchApi<{ data: ProjectDetail }>(`/api/v1/worker/projects/${projectId}`);
        setProject(projRes.data);

        const mediaRes = await fetchApi<{ data: MediaAsset[] }>(`/api/v1/worker/projects/${projectId}/media`);
        setMediaAssets(mediaRes.data || []);
        if (mediaRes.data && mediaRes.data.length > 0) {
          const firstVideo = mediaRes.data.find((m) => m.mime_type.startsWith("video/")) || mediaRes.data[0];
          setSelectedAsset(firstVideo);
          loadSignedUrl(firstVideo.id);
        }

        const revRes = await fetchApi<{ data: RevisionRound[] }>(`/api/v1/worker/projects/${projectId}/revisions`);
        setRevisions(revRes.data || []);
      } catch (err) {
        console.error("Failed to load worker workspace:", err);
      } finally {
        setLoading(false);
      }
    }

    loadData();
  }, [projectId]);

  async function loadSignedUrl(assetId: number) {
    try {
      const res = await fetchApi<{ url: string }>(`/api/v1/worker/media/${assetId}/signed-url`);
      setStreamUrl(res.url);
    } catch (err) {
      console.error("Failed to load signed stream URL:", err);
    }
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

  async function handleResolveComment(commentId: number) {
    try {
      setResolvingCommentId(commentId);
      await fetchApi(`/api/v1/worker/revision-comments/${commentId}/resolve`, {
        method: "POST",
      });

      // Update local state
      setRevisions((prev) =>
        prev.map((r) => ({
          ...r,
          comments: r.comments.map((c) => (c.id === commentId ? { ...c, status: "RESOLVED" } : c)),
        }))
      );
    } catch (err: any) {
      alert("Failed to resolve comment: " + err.message);
    } finally {
      setResolvingCommentId(null);
    }
  }

  async function handleDirectUpload(e: React.FormEvent) {
    e.preventDefault();
    if (!uploadFilename.trim()) return;

    try {
      setUploading(true);
      // 1. Request upload intent
      const intentRes = await fetchApi<{
        data: {
          public_id: string;
          upload_url: string;
        };
      }>("/api/v1/worker/media/upload-intent", {
        method: "POST",
        body: JSON.stringify({
          project_id: Number(projectId),
          filename: uploadFilename,
          mime_type: "video/mp4",
          size_bytes: 250000000,
          category: uploadCategory,
        }),
      });

      // 2. Direct upload simulation
      await fetch(intentRes.data.upload_url, {
        method: "PUT",
        body: "SAMPLE_BINARY_DRAFT_PAYLOAD",
      });

      // 3. Finalize upload
      await fetchApi("/api/v1/worker/media/finalize-upload", {
        method: "POST",
        body: JSON.stringify({
          pending_upload_public_id: intentRes.data.public_id,
        }),
      });

      setUploadModalOpen(false);
      setUploadFilename("");
      alert("Draft uploaded successfully! Background transcode has been initiated.");

      // Reload media
      const mediaRes = await fetchApi<{ data: MediaAsset[] }>(`/api/v1/worker/projects/${projectId}/media`);
      setMediaAssets(mediaRes.data || []);
    } catch (err: any) {
      alert("Upload failed: " + err.message);
    } finally {
      setUploading(false);
    }
  }

  const activeRound = revisions[0] || null;

  if (loading) {
    return <div className="p-12 text-center text-zinc-500">Loading Workspace...</div>;
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-zinc-800">
        <div>
          <div className="flex items-center gap-2">
            <Link href="/worker/projects" className="text-xs text-zinc-400 hover:text-white">
              ← Projects
            </Link>
            <span className="text-zinc-700">/</span>
            <span className="text-xs font-mono text-amber-500 font-semibold">{project?.project_number}</span>
          </div>
          <h1 className="text-2xl font-bold text-white mt-1">{project?.name}</h1>
        </div>

        <button
          onClick={() => setUploadModalOpen(true)}
          className="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-black font-extrabold text-xs rounded-xl shadow transition"
        >
          + Upload Footage / Draft
        </button>
      </div>

      {/* Grid: Video Review (Left) + Feedback Resolution Checklist (Right) */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {/* Left: Video Player */}
        <div className="lg:col-span-7 space-y-4">
          <div className="bg-black border border-zinc-800 rounded-2xl overflow-hidden shadow-2xl">
            <div className="relative w-full aspect-video bg-black flex items-center justify-center">
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
                  <span>No footage preview available yet.</span>
                </div>
              )}
            </div>

            {streamUrl && (
              <div className="p-4 bg-zinc-950 border-t border-zinc-800 flex flex-col gap-3">
                <input
                  type="range"
                  min={0}
                  max={duration || 100}
                  step={0.01}
                  value={currentTime}
                  onChange={(e) => seekTo(parseFloat(e.target.value))}
                  className="w-full h-1.5 bg-zinc-800 rounded-lg appearance-none cursor-pointer accent-amber-500"
                />

                <div className="flex items-center justify-between text-xs text-zinc-400">
                  <button
                    onClick={() => {
                      if (videoRef.current) {
                        if (isPlaying) videoRef.current.pause();
                        else videoRef.current.play();
                      }
                    }}
                    className="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 text-white font-semibold rounded-md"
                  >
                    {isPlaying ? "⏸ Pause" : "▶ Play"}
                  </button>

                  <span className="font-mono text-white text-sm font-bold">
                    {formatTimecode(currentTime)} / {formatTimecode(duration)}
                  </span>
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Right: Revision Checklist */}
        <div className="lg:col-span-5 space-y-4">
          <div className="bg-zinc-900 border border-zinc-800 rounded-2xl p-5 shadow-xl">
            <div className="flex items-center justify-between pb-4 border-b border-zinc-800">
              <h3 className="text-base font-bold text-white">Client Feedback Checklist</h3>
              <span className="text-xs font-mono text-amber-400 font-semibold">
                {activeRound?.comments.filter((c) => c.status === "RESOLVED").length || 0} /{" "}
                {activeRound?.comments.length || 0} Resolved
              </span>
            </div>

            <div className="my-4 max-h-[420px] overflow-y-auto space-y-3 pr-1">
              {!activeRound || activeRound.comments.length === 0 ? (
                <div className="p-8 text-center text-zinc-500 text-xs">
                  <span>No client revision notes pending for this project.</span>
                </div>
              ) : (
                activeRound.comments.map((c) => (
                  <div
                    key={c.id}
                    className={`p-4 rounded-xl border transition ${
                      c.status === "RESOLVED"
                        ? "bg-zinc-950/40 border-zinc-800/40 opacity-70"
                        : "bg-zinc-950 border-zinc-800"
                    }`}
                  >
                    <div className="flex items-center justify-between">
                      <button
                        onClick={() => seekTo(c.timecode_seconds)}
                        className="text-xs font-mono font-bold text-amber-400 hover:underline"
                      >
                        ⏱ {formatTimecode(c.timecode_seconds)}
                      </button>

                      {c.status === "RESOLVED" ? (
                        <span className="text-[10px] font-bold text-emerald-400 bg-emerald-950/80 px-2 py-0.5 rounded border border-emerald-800">
                          ✓ RESOLVED
                        </span>
                      ) : (
                        <button
                          onClick={() => handleResolveComment(c.id)}
                          disabled={resolvingCommentId === c.id}
                          className="text-[11px] font-bold px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-md transition"
                        >
                          {resolvingCommentId === c.id ? "Saving..." : "Mark as Resolved"}
                        </button>
                      )}
                    </div>

                    <p className="text-xs text-zinc-300 mt-2 leading-relaxed">{c.comment}</p>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      </div>

      {/* DIRECT UPLOAD MODAL */}
      {uploadModalOpen && (
        <div className="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <form
            onSubmit={handleDirectUpload}
            className="bg-zinc-900 border border-zinc-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl"
          >
            <h3 className="text-lg font-bold text-white">Upload Footage or Video Draft</h3>
            <p className="text-xs text-zinc-400">
              Direct high-speed presigned upload directly to secure studio object storage.
            </p>

            <div className="space-y-3 pt-2">
              <div>
                <label className="text-xs text-zinc-400 block mb-1">File Name</label>
                <input
                  type="text"
                  required
                  value={uploadFilename}
                  onChange={(e) => setUploadFilename(e.target.value)}
                  placeholder="e.g. Wedding_Highlight_FirstCut_v1.mp4"
                  className="w-full px-3 py-2 bg-zinc-950 border border-zinc-800 rounded-lg text-xs text-white focus:outline-none focus:border-amber-500"
                />
              </div>

              <div>
                <label className="text-xs text-zinc-400 block mb-1">Category</label>
                <select
                  value={uploadCategory}
                  onChange={(e) => setUploadCategory(e.target.value)}
                  className="w-full px-3 py-2 bg-zinc-950 border border-zinc-800 rounded-lg text-xs text-white focus:outline-none focus:border-amber-500"
                >
                  <option value="RAW_FOOTAGE">Raw Footage</option>
                  <option value="INTERNAL_DRAFT">Internal Draft</option>
                  <option value="FINAL_MASTER">Final Master Delivery</option>
                </select>
              </div>
            </div>

            <div className="flex justify-end gap-3 pt-4 border-t border-zinc-800">
              <button
                type="button"
                onClick={() => setUploadModalOpen(false)}
                className="px-4 py-2 bg-zinc-800 text-zinc-400 hover:text-white rounded-lg text-xs"
              >
                Cancel
              </button>
              <button
                type="submit"
                disabled={uploading}
                className="px-5 py-2 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-black font-bold rounded-lg text-xs"
              >
                {uploading ? "Uploading & Transcoding..." : "Upload File"}
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
