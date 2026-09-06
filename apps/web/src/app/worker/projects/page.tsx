"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface WorkerProject {
  id: number;
  project_number: string;
  name: string;
  client?: { display_name: string };
  status: string;
  shoot_date?: string;
  deadline?: string;
  assignment_role?: string;
}

export default function WorkerProjectsPage() {
  const [projects, setProjects] = useState<WorkerProject[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadProjects() {
      try {
        setLoading(true);
        const res = await fetchApi<{ data: WorkerProject[] }>("/api/v1/worker/projects");
        setProjects(res.data || []);
      } catch (err) {
        console.error("Failed to load worker projects:", err);
      } finally {
        setLoading(false);
      }
    }
    loadProjects();
  }, []);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight text-white">Assigned Productions</h1>
        <p className="text-sm text-zinc-400 mt-1">
          Active film projects, footage upload pipelines, client revision notes, and call sheets.
        </p>
      </div>

      {loading ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {[1, 2, 3].map((n) => (
            <div key={n} className="h-52 bg-zinc-900/60 border border-zinc-800 rounded-xl animate-pulse" />
          ))}
        </div>
      ) : projects.length === 0 ? (
        <div className="p-12 text-center bg-zinc-900/40 border border-zinc-800 rounded-xl">
          <span className="text-4xl">🎬</span>
          <h3 className="text-lg font-semibold text-white mt-3">No Active Assignments</h3>
          <p className="text-sm text-zinc-400 mt-1">
            You will receive instant alerts when assigned to upcoming visual productions.
          </p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {projects.map((p) => (
            <Link
              key={p.id}
              href={`/worker/projects/${p.id}`}
              className="group block p-6 bg-zinc-900/70 hover:bg-zinc-900 border border-zinc-800 hover:border-amber-500/50 rounded-xl transition duration-200 shadow-lg"
            >
              <div className="flex items-start justify-between">
                <span className="font-mono text-xs text-zinc-500">{p.project_number}</span>
                <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-zinc-800 text-zinc-300 border border-zinc-700">
                  {p.status}
                </span>
              </div>

              <h3 className="text-lg font-bold text-white group-hover:text-amber-400 mt-3 transition">
                {p.name}
              </h3>
              <p className="text-xs text-zinc-400 mt-1">Client: {p.client?.display_name || "Confidential"}</p>

              <div className="mt-4 pt-4 border-t border-zinc-800 space-y-2 text-xs text-zinc-400">
                {p.shoot_date && (
                  <div className="flex justify-between">
                    <span>Shoot Date:</span>
                    <span className="text-zinc-200 font-medium">{p.shoot_date}</span>
                  </div>
                )}
                {p.deadline && (
                  <div className="flex justify-between">
                    <span>Delivery Deadline:</span>
                    <span className="text-zinc-200 font-medium">{p.deadline}</span>
                  </div>
                )}
              </div>

              <div className="mt-5 flex items-center justify-between text-xs text-amber-500 font-medium">
                <span>Open Project Workspace →</span>
                <span className="text-zinc-500 group-hover:translate-x-1 transition">➔</span>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
