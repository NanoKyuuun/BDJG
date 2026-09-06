"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface ClientProject {
  id: number;
  public_id: string;
  project_number: string;
  name: string;
  status: string;
  shoot_date?: string;
  deadline?: string;
  contract_value: number;
  created_at: string;
}

export default function ClientProjectsPage() {
  const [projects, setProjects] = useState<ClientProject[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function loadProjects() {
      try {
        setLoading(true);
        const res = await fetchApi<{ data: ClientProject[] }>("/api/v1/client/projects");
        setProjects(res.data || []);
      } catch (err: any) {
        setError(err.message || "Failed to load projects.");
      } finally {
        setLoading(false);
      }
    }
    loadProjects();
  }, []);

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "PRODUCTION":
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-900/60 text-blue-300 border border-blue-700/50">Production</span>;
      case "POST_PRODUCTION":
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-900/60 text-amber-300 border border-amber-700/50">In Post-Production / Review</span>;
      case "DELIVERED":
      case "COMPLETED":
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-900/60 text-emerald-300 border border-emerald-700/50">Delivered</span>;
      default:
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-zinc-800 text-zinc-400 border border-zinc-700">{status}</span>;
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-white">My Projects</h1>
          <p className="text-sm text-zinc-400 mt-1">
            Active visual productions, timelines, draft review streams, and master deliverables.
          </p>
        </div>
      </div>

      {loading ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {[1, 2, 3].map((n) => (
            <div key={n} className="h-56 bg-zinc-900/60 border border-zinc-800 rounded-xl animate-pulse" />
          ))}
        </div>
      ) : error ? (
        <div className="p-6 bg-red-950/40 border border-red-800/50 rounded-xl text-red-300">
          <p className="font-semibold">Error Loading Projects</p>
          <p className="text-sm mt-1">{error}</p>
        </div>
      ) : projects.length === 0 ? (
        <div className="p-12 text-center bg-zinc-900/40 border border-zinc-800/60 rounded-xl">
          <span className="text-4xl">🎬</span>
          <h3 className="text-lg font-semibold text-white mt-3">No Active Projects Yet</h3>
          <p className="text-sm text-zinc-400 max-w-md mx-auto mt-1">
            Your projects will appear here as soon as an accepted quotation is activated by our studio team.
          </p>
          <Link
            href="/client/quotations"
            className="inline-block mt-5 px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-black font-semibold text-sm rounded-lg transition"
          >
            View Quotations
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {projects.map((project) => (
            <Link
              key={project.id}
              href={`/client/projects/${project.id}`}
              className="group block p-6 bg-zinc-900/70 hover:bg-zinc-900 border border-zinc-800 hover:border-amber-500/50 rounded-xl transition duration-200 shadow-lg hover:shadow-amber-500/5"
            >
              <div className="flex items-start justify-between">
                <span className="text-xs font-mono text-zinc-500">{project.project_number}</span>
                {getStatusBadge(project.status)}
              </div>

              <h3 className="text-lg font-bold text-white group-hover:text-amber-400 mt-3 transition">
                {project.name}
              </h3>

              <div className="mt-4 pt-4 border-t border-zinc-800/70 space-y-2 text-xs text-zinc-400">
                {project.shoot_date && (
                  <div className="flex justify-between">
                    <span>Shoot Date:</span>
                    <span className="text-zinc-200 font-medium">{project.shoot_date}</span>
                  </div>
                )}
                {project.deadline && (
                  <div className="flex justify-between">
                    <span>Target Delivery:</span>
                    <span className="text-zinc-200 font-medium">{project.deadline}</span>
                  </div>
                )}
                <div className="flex justify-between">
                  <span>Investment:</span>
                  <span className="text-amber-400 font-semibold">
                    IDR {Number(project.contract_value || 0).toLocaleString("id-ID")}
                  </span>
                </div>
              </div>

              <div className="mt-5 flex items-center justify-between text-xs text-amber-500 font-medium">
                <span>Open Project Hub & Review →</span>
                <span className="text-zinc-500 group-hover:translate-x-1 transition">➔</span>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
