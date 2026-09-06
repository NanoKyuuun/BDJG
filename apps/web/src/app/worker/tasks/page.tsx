"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface WorkerTask {
  id: number;
  title: string;
  description?: string;
  status: string;
  priority: string;
  due_date?: string;
  project?: {
    id: number;
    name: string;
    project_number: string;
  };
}

export default function WorkerTasksPage() {
  const [tasks, setTasks] = useState<WorkerTask[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState<"ALL" | "PENDING" | "COMPLETED">("ALL");

  useEffect(() => {
    loadTasks();
  }, []);

  async function loadTasks() {
    try {
      setLoading(true);
      const res = await fetchApi<{ data: WorkerTask[] }>("/api/v1/worker/tasks");
      setTasks(res.data || []);
    } catch (err) {
      console.error("Failed to load worker tasks:", err);
    } finally {
      setLoading(false);
    }
  }

  async function toggleTaskStatus(taskId: number, currentStatus: string) {
    const nextStatus = currentStatus === "COMPLETED" ? "IN_PROGRESS" : "COMPLETED";
    try {
      await fetchApi(`/api/v1/worker/tasks/${taskId}/status`, {
        method: "PATCH",
        body: JSON.stringify({ status: nextStatus }),
      });
      setTasks((prev) =>
        prev.map((t) => (t.id === taskId ? { ...t, status: nextStatus } : t))
      );
    } catch (err: any) {
      alert("Failed to update task: " + err.message);
    }
  }

  const filteredTasks = tasks.filter((t) => {
    if (filter === "PENDING") return t.status !== "COMPLETED";
    if (filter === "COMPLETED") return t.status === "COMPLETED";
    return true;
  });

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-white">My Production Tasks</h1>
          <p className="text-sm text-zinc-400 mt-1">
            Assigned duties, editing milestones, color grading checkpoints, and asset delivery checklists.
          </p>
        </div>

        <div className="flex items-center gap-1 bg-zinc-900 p-1 rounded-xl border border-zinc-800 text-xs">
          {(["ALL", "PENDING", "COMPLETED"] as const).map((tab) => (
            <button
              key={tab}
              onClick={() => setFilter(tab)}
              className={`px-3 py-1.5 rounded-lg font-semibold transition ${
                filter === tab ? "bg-amber-500 text-black font-bold" : "text-zinc-400 hover:text-white"
              }`}
            >
              {tab}
            </button>
          ))}
        </div>
      </div>

      {loading ? (
        <div className="p-12 text-center text-zinc-500 text-xs">Loading assigned tasks...</div>
      ) : filteredTasks.length === 0 ? (
        <div className="p-12 text-center bg-zinc-900/40 border border-zinc-800 rounded-xl text-xs text-zinc-500">
          <span>No tasks found in this view.</span>
        </div>
      ) : (
        <div className="space-y-3">
          {filteredTasks.map((t) => (
            <div
              key={t.id}
              className={`p-4 rounded-xl border transition flex items-start justify-between gap-4 ${
                t.status === "COMPLETED"
                  ? "bg-zinc-950/40 border-zinc-800/40 opacity-70"
                  : "bg-zinc-900 border-zinc-800 shadow-md hover:border-zinc-700"
              }`}
            >
              <div className="flex items-start gap-3">
                <input
                  type="checkbox"
                  checked={t.status === "COMPLETED"}
                  onChange={() => toggleTaskStatus(t.id, t.status)}
                  className="mt-1 rounded bg-zinc-950 border-zinc-700 text-amber-500 focus:ring-0 cursor-pointer"
                />
                <div>
                  <div className="flex items-center gap-2">
                    <span
                      className={`text-sm font-bold ${
                        t.status === "COMPLETED" ? "line-through text-zinc-500" : "text-white"
                      }`}
                    >
                      {t.title}
                    </span>
                    {t.priority === "HIGH" && (
                      <span className="text-[10px] uppercase font-extrabold px-2 py-0.5 rounded-full bg-red-950 text-red-400 border border-red-800">
                        HIGH PRIORITY
                      </span>
                    )}
                  </div>

                  {t.description && <p className="text-xs text-zinc-400 mt-1">{t.description}</p>}

                  <div className="mt-2 flex items-center gap-4 text-xs text-zinc-500 font-mono">
                    {t.project && <span>🎬 {t.project.name}</span>}
                    {t.due_date && <span>📅 Due: {t.due_date}</span>}
                  </div>
                </div>
              </div>

              <span
                className={`text-[10px] uppercase font-bold px-2.5 py-1 rounded-full ${
                  t.status === "COMPLETED"
                    ? "bg-emerald-950 text-emerald-400 border border-emerald-800"
                    : "bg-amber-950 text-amber-400 border border-amber-800"
                }`}
              >
                {t.status}
              </span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
