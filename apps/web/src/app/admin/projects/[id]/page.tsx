"use client";

import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface ClientDetail {
  id: number;
  display_name: string;
  email: string;
}

interface ProjectAssignment {
  id: number;
  worker_id: number;
  worker?: {
    user?: { name: string; email: string };
    profession?: string;
  };
  assignment_role: string;
  is_active: boolean;
  assigned_at: string;
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
}

interface RevisionRound {
  id: number;
  round_number: number;
  title: string;
  status: string;
  comments: Array<{
    id: number;
    timecode_seconds: number;
    comment: string;
    status: string;
    author?: { name: string };
  }>;
}

interface DeliveryPackage {
  id: number;
  title: string;
  status: string;
  total_size_bytes: number;
  file_count: number;
  download_count: number;
  expires_at?: string;
  created_at: string;
}

interface ProjectProfit {
  total_invoiced: number;
  total_paid_revenue: number;
  total_approved_expenses: number;
  total_worker_costs: number;
  gross_profit: number;
  margin_percentage: number;
}

interface ProjectDetail {
  id: number;
  project_number: string;
  name: string;
  status: string;
  shoot_date?: string;
  deadline?: string;
  contract_value: number;
  client?: ClientDetail;
  assignments: ProjectAssignment[];
}

export default function AdminProjectCommandCenter() {
  const params = useParams();
  const projectId = params?.id as string;

  const [project, setProject] = useState<ProjectDetail | null>(null);
  const [activeTab, setActiveTab] = useState<"OVERVIEW" | "CREW" | "MEDIA" | "REVISIONS" | "DELIVERIES" | "FINANCE">("OVERVIEW");
  const [mediaAssets, setMediaAssets] = useState<MediaAsset[]>([]);
  const [revisions, setRevisions] = useState<RevisionRound[]>([]);
  const [deliveries, setDeliveries] = useState<DeliveryPackage[]>([]);
  const [profit, setProfit] = useState<ProjectProfit | null>(null);
  const [loading, setLoading] = useState(true);

  // Status Change State
  const [updatingStatus, setUpdatingStatus] = useState(false);

  // Media Release State
  const [updatingMediaId, setUpdatingMediaId] = useState<number | null>(null);

  // Package Modal State
  const [showPackageModal, setShowPackageModal] = useState(false);
  const [selectedMediaIds, setSelectedMediaIds] = useState<number[]>([]);
  const [packageTitle, setPackageTitle] = useState("");
  const [packaging, setPackaging] = useState(false);

  useEffect(() => {
    if (!projectId) return;
    loadAllData();
  }, [projectId]);

  async function loadAllData() {
    try {
      setLoading(true);
      const [projRes, mediaRes, revRes, delRes, profRes] = await Promise.all([
        fetchApi<{ data: ProjectDetail }>(`/api/v1/admin/projects/${projectId}`),
        fetchApi<{ data: MediaAsset[] }>(`/api/v1/admin/projects/${projectId}/media`),
        fetchApi<{ data: RevisionRound[] }>(`/api/v1/admin/projects/${projectId}/revisions`),
        fetchApi<{ data: DeliveryPackage[] }>(`/api/v1/admin/projects/${projectId}/deliveries`),
        fetchApi<{ data: ProjectProfit }>(`/api/v1/admin/finance/projects/${projectId}/profit`).catch(() => ({ data: null })),
      ]);

      setProject(projRes.data);
      setMediaAssets(mediaRes.data || []);
      setRevisions(revRes.data || []);
      setDeliveries(delRes.data || []);
      if (profRes.data) setProfit(profRes.data);
    } catch (err) {
      console.error("Failed to load project command center:", err);
    } finally {
      setLoading(false);
    }
  }

  async function handleStatusChange(newStatus: string) {
    try {
      setUpdatingStatus(true);
      const res = await fetchApi<{ data: ProjectDetail }>(`/api/v1/admin/projects/${projectId}/status`, {
        method: "PATCH",
        body: JSON.stringify({ status: newStatus }),
      });
      setProject(res.data);
      alert(`Project status updated to ${newStatus}`);
    } catch (err: any) {
      alert("Failed to update status: " + err.message);
    } finally {
      setUpdatingStatus(false);
    }
  }

  async function handleMediaRelease(assetId: number, visibility: string) {
    try {
      setUpdatingMediaId(assetId);
      await fetchApi(`/api/v1/admin/media/${assetId}/release`, {
        method: "PATCH",
        body: JSON.stringify({ visibility }),
      });
      setMediaAssets((prev) =>
        prev.map((m) => (m.id === assetId ? { ...m, visibility } : m))
      );
    } catch (err: any) {
      alert("Failed to update media visibility: " + err.message);
    } finally {
      setUpdatingMediaId(null);
    }
  }

  async function handleCreateDeliveryPackage(e: React.FormEvent) {
    e.preventDefault();
    if (selectedMediaIds.length === 0 || !packageTitle.trim()) return;

    try {
      setPackaging(true);
      await fetchApi(`/api/v1/admin/projects/${projectId}/deliveries`, {
        method: "POST",
        body: JSON.stringify({
          media_asset_ids: selectedMediaIds,
          title: packageTitle,
          notes: "Official Master Handover Package.",
          expiry_days: 30,
        }),
      });

      setShowPackageModal(false);
      setPackageTitle("");
      setSelectedMediaIds([]);
      await loadAllData();
      alert("Delivery package created and notification email dispatched to client.");
    } catch (err: any) {
      alert("Failed to package deliverables: " + err.message);
    } finally {
      setPackaging(false);
    }
  }

  if (loading) {
    return <div className="p-12 text-center text-zinc-500">Loading Command Center...</div>;
  }

  if (!project) {
    return (
      <div className="p-8 bg-zinc-900 border border-zinc-800 rounded-xl text-center">
        <h2 className="text-lg font-bold text-white">Project Not Found</h2>
        <Link href="/admin/projects" className="mt-4 inline-block text-amber-500 text-sm">
          ← Back to Projects
        </Link>
      </div>
    );
  }

  const STATUS_FLOW = [
    "DRAFT",
    "PRE_PRODUCTION",
    "PRODUCTION",
    "POST_PRODUCTION",
    "INTERNAL_REVIEW",
    "CLIENT_REVIEW",
    "DELIVERED",
    "COMPLETED",
  ];

  return (
    <div className="space-y-6">
      {/* Top Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-zinc-800">
        <div>
          <div className="flex items-center gap-2">
            <Link href="/admin/projects" className="text-xs text-zinc-400 hover:text-white">
              ← Projects
            </Link>
            <span className="text-zinc-700">/</span>
            <span className="text-xs font-mono text-amber-500 font-semibold">{project.project_number}</span>
          </div>
          <h1 className="text-2xl font-extrabold text-white mt-1">{project.name}</h1>
          <p className="text-xs text-zinc-400 mt-0.5">Client: {project.client?.display_name || "Internal"}</p>
        </div>

        {/* Live Status Selector */}
        <div className="flex items-center gap-2 bg-zinc-950 p-1.5 rounded-xl border border-zinc-800">
          <span className="text-xs text-zinc-500 font-bold px-2">Status:</span>
          <select
            value={project.status}
            disabled={updatingStatus}
            onChange={(e) => handleStatusChange(e.target.value)}
            className="bg-zinc-900 text-xs font-bold text-amber-400 border border-zinc-700 rounded-lg px-3 py-1.5 focus:outline-none cursor-pointer"
          >
            {STATUS_FLOW.map((s) => (
              <option key={s} value={s}>
                {s}
              </option>
            ))}
          </select>
        </div>
      </div>

      {/* Tabs Bar */}
      <div className="flex gap-2 border-b border-zinc-800 overflow-x-auto pb-1 text-xs font-semibold">
        {[
          { key: "OVERVIEW", label: "Overview & Schedule", icon: "📊" },
          { key: "CREW", label: `Crew (${project.assignments?.length || 0})`, icon: "👥" },
          { key: "MEDIA", label: `Media Assets (${mediaAssets.length})`, icon: "🎞️" },
          { key: "REVISIONS", label: `Revisions (${revisions.length})`, icon: "⏱" },
          { key: "DELIVERIES", label: `Handover (${deliveries.length})`, icon: "🏆" },
          { key: "FINANCE", label: "Profitability", icon: "💰" },
        ].map((tab) => (
          <button
            key={tab.key}
            onClick={() => setActiveTab(tab.key as any)}
            className={`px-4 py-2.5 rounded-xl border transition flex items-center gap-2 whitespace-nowrap ${
              activeTab === tab.key
                ? "bg-amber-500 text-black border-amber-500 font-bold shadow"
                : "bg-zinc-900/60 text-zinc-400 border-zinc-800 hover:bg-zinc-800 hover:text-white"
            }`}
          >
            <span>{tab.icon}</span>
            <span>{tab.label}</span>
          </button>
        ))}
      </div>

      {/* TAB CONTENT: OVERVIEW */}
      {activeTab === "OVERVIEW" && (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="p-5 bg-zinc-900 border border-zinc-800 rounded-xl space-y-2">
            <span className="text-xs text-zinc-500 block">Shoot Date</span>
            <span className="text-base font-bold text-white font-mono">{project.shoot_date || "To be scheduled"}</span>
          </div>
          <div className="p-5 bg-zinc-900 border border-zinc-800 rounded-xl space-y-2">
            <span className="text-xs text-zinc-500 block">Target Delivery</span>
            <span className="text-base font-bold text-white font-mono">{project.deadline || "TBD"}</span>
          </div>
          <div className="p-5 bg-zinc-900 border border-zinc-800 rounded-xl space-y-2">
            <span className="text-xs text-zinc-500 block">Contract Value</span>
            <span className="text-base font-extrabold text-amber-400 font-mono">
              IDR {Number(project.contract_value || 0).toLocaleString("id-ID")}
            </span>
          </div>
        </div>
      )}

      {/* TAB CONTENT: CREW */}
      {activeTab === "CREW" && (
        <div className="bg-zinc-900 border border-zinc-800 rounded-2xl p-5 shadow-xl">
          <h3 className="text-sm font-bold text-white mb-4">Assigned Creative Crew</h3>
          <div className="divide-y divide-zinc-800 text-xs">
            {project.assignments?.length === 0 ? (
              <div className="p-6 text-center text-zinc-500">No crew assigned yet.</div>
            ) : (
              project.assignments.map((a) => (
                <div key={a.id} className="py-3 flex items-center justify-between">
                  <div>
                    <span className="font-bold text-white block">{a.worker?.user?.name || "Kru"}</span>
                    <span className="text-zinc-500 text-[11px]">{a.assignment_role}</span>
                  </div>
                  <span className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-800">
                    ACTIVE
                  </span>
                </div>
              ))
            )}
          </div>
        </div>
      )}

      {/* TAB CONTENT: MEDIA */}
      {activeTab === "MEDIA" && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="text-sm font-bold text-white">Project Footage & Video Drafts</h3>
          </div>

          <div className="bg-zinc-900 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl">
            <table className="w-full text-left text-xs">
              <thead className="bg-zinc-950 text-zinc-400 border-b border-zinc-800">
                <tr>
                  <th className="py-3 px-4">Filename</th>
                  <th className="py-3 px-4">Category</th>
                  <th className="py-3 px-4">Size</th>
                  <th className="py-3 px-4 text-center">Visibility Control</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-zinc-800 text-zinc-300">
                {mediaAssets.map((asset) => (
                  <tr key={asset.id} className="hover:bg-zinc-800/40">
                    <td className="py-3 px-4 font-semibold text-white">{asset.original_name}</td>
                    <td className="py-3 px-4 text-zinc-400">{asset.category}</td>
                    <td className="py-3 px-4 font-mono text-zinc-400">
                      {Math.round((asset.size_bytes / 1048576) * 10) / 10} MB
                    </td>
                    <td className="py-3 px-4 text-center">
                      <select
                        value={asset.visibility}
                        disabled={updatingMediaId === asset.id}
                        onChange={(e) => handleMediaRelease(asset.id, e.target.value)}
                        className={`text-xs font-bold rounded-lg px-2.5 py-1 border cursor-pointer ${
                          asset.visibility === "CLIENT_PREVIEW"
                            ? "bg-blue-950 text-blue-400 border-blue-800"
                            : asset.visibility === "FINAL_RELEASED"
                            ? "bg-emerald-950 text-emerald-400 border-emerald-800"
                            : "bg-zinc-800 text-zinc-400 border-zinc-700"
                        }`}
                      >
                        <option value="INTERNAL">Internal Only</option>
                        <option value="CLIENT_PREVIEW">Release to Client Preview</option>
                        <option value="FINAL_RELEASED">Final Released</option>
                      </select>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* TAB CONTENT: DELIVERIES */}
      {activeTab === "DELIVERIES" && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="text-sm font-bold text-white">Final Handover Packages</h3>
            <button
              onClick={() => setShowPackageModal(true)}
              className="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-black font-extrabold text-xs rounded-xl shadow"
            >
              + Create Delivery Package (ZIP)
            </button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {deliveries.length === 0 ? (
              <div className="col-span-2 p-12 text-center text-zinc-500 text-xs bg-zinc-900 border border-zinc-800 rounded-xl">
                <span>No master delivery packages created yet.</span>
              </div>
            ) : (
              deliveries.map((pkg) => (
                <div key={pkg.id} className="p-5 bg-zinc-900 border border-zinc-800 rounded-xl space-y-3">
                  <div className="flex justify-between items-start">
                    <h4 className="font-bold text-white text-sm">{pkg.title}</h4>
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-950 text-emerald-400 border border-emerald-800">
                      {pkg.status}
                    </span>
                  </div>
                  <p className="text-xs text-zinc-400">
                    {pkg.file_count} files ({Math.round(pkg.total_size_bytes / 1048576)} MB) · Downloaded {pkg.download_count} times
                  </p>
                </div>
              ))
            )}
          </div>
        </div>
      )}

      {/* TAB CONTENT: FINANCE */}
      {activeTab === "FINANCE" && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div className="p-5 bg-zinc-900 border border-zinc-800 rounded-xl">
            <span className="text-xs text-zinc-400">Paid Revenue</span>
            <p className="text-xl font-extrabold text-white font-mono mt-1">
              IDR {profit?.total_paid_revenue?.toLocaleString("id-ID") || 0}
            </p>
          </div>
          <div className="p-5 bg-zinc-900 border border-zinc-800 rounded-xl">
            <span className="text-xs text-zinc-400">Approved Expenses</span>
            <p className="text-xl font-extrabold text-amber-400 font-mono mt-1">
              IDR {profit?.total_approved_expenses?.toLocaleString("id-ID") || 0}
            </p>
          </div>
          <div className="p-5 bg-zinc-900 border border-zinc-800 rounded-xl">
            <span className="text-xs text-zinc-400">Gross Margin</span>
            <p className="text-xl font-extrabold text-emerald-400 font-mono mt-1">
              {profit?.margin_percentage || 0}%
            </p>
          </div>
          <div className="p-5 bg-zinc-900 border border-zinc-800 rounded-xl">
            <span className="text-xs text-zinc-400">Net Profit</span>
            <p className="text-xl font-extrabold text-emerald-400 font-mono mt-1">
              IDR {profit?.gross_profit?.toLocaleString("id-ID") || 0}
            </p>
          </div>
        </div>
      )}

      {/* PACKAGE DELIVERABLES MODAL */}
      {showPackageModal && (
        <div className="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <form
            onSubmit={handleCreateDeliveryPackage}
            className="bg-zinc-900 border border-zinc-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl"
          >
            <h3 className="text-lg font-bold text-white">Create Final Master Delivery Package</h3>

            <div className="space-y-3 pt-2">
              <div>
                <label className="text-xs text-zinc-400 block mb-1">Package Title</label>
                <input
                  type="text"
                  required
                  value={packageTitle}
                  onChange={(e) => setPackageTitle(e.target.value)}
                  placeholder="e.g. Official 4K Cinema Master & High-Res Gallery"
                  className="w-full px-3 py-2 bg-zinc-950 border border-zinc-800 rounded-lg text-xs text-white focus:outline-none focus:border-amber-500"
                />
              </div>

              <div>
                <label className="text-xs text-zinc-400 block mb-1">Select Master Media Assets</label>
                <div className="max-h-40 overflow-y-auto bg-zinc-950 p-2 rounded-lg border border-zinc-800 space-y-2">
                  {mediaAssets.map((asset) => (
                    <label key={asset.id} className="flex items-center gap-2 text-xs text-zinc-300 cursor-pointer">
                      <input
                        type="checkbox"
                        checked={selectedMediaIds.includes(asset.id)}
                        onChange={(e) => {
                          if (e.target.checked) setSelectedMediaIds([...selectedMediaIds, asset.id]);
                          else setSelectedMediaIds(selectedMediaIds.filter((id) => id !== asset.id));
                        }}
                        className="rounded bg-zinc-900 border-zinc-700 text-amber-500"
                      />
                      <span className="truncate">{asset.original_name}</span>
                    </label>
                  ))}
                </div>
              </div>
            </div>

            <div className="flex justify-end gap-3 pt-4 border-t border-zinc-800">
              <button
                type="button"
                onClick={() => setShowPackageModal(false)}
                className="px-4 py-2 bg-zinc-800 text-zinc-400 hover:text-white rounded-lg text-xs"
              >
                Cancel
              </button>
              <button
                type="submit"
                disabled={packaging || selectedMediaIds.length === 0}
                className="px-5 py-2 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-black font-bold rounded-lg text-xs"
              >
                {packaging ? "Creating Package..." : "Package & Release"}
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
