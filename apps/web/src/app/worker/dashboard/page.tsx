"use client";

import { useEffect, useState } from "react";
import { useAuth } from "@/lib/use-auth";
import { fetchApi } from "@/lib/api";

interface WorkerProject {
  id: number;
  public_id: string;
  project_number: string;
  name: string;
  service_name?: string;
  package_name?: string;
  status: string;
  shoot_date?: string;
  deadline?: string;
  brief?: string;
}

interface WorkerTask {
  id: number;
  public_id: string;
  project_id: number;
  title: string;
  description?: string;
  status: string;
  priority: string;
  due_at?: string;
  project?: {
    name: string;
    project_number: string;
  };
}

interface WorkerSchedule {
  id: number;
  title: string;
  schedule_type: string;
  start_time: string;
  location?: string;
  project_name?: string;
}

export default function WorkerDashboardPage() {
  const { user, loading: authLoading } = useAuth();
  const [projects, setProjects] = useState<WorkerProject[]>([]);
  const [tasks, setTasks] = useState<WorkerTask[]>([]);
  const [schedules, setSchedules] = useState<WorkerSchedule[]>([]);
  const [loading, setLoading] = useState(true);

  const loadData = () => {
    setLoading(true);
    Promise.all([
      fetchApi<{ data: WorkerProject[] }>("/api/v1/worker/projects").catch(() => ({ data: [] })),
      fetchApi<{ data: WorkerTask[] }>("/api/v1/worker/tasks").catch(() => ({ data: [] })),
      fetchApi<{ data: WorkerSchedule[] }>("/api/v1/worker/schedules").catch(() => ({ data: [] })),
    ])
      .then(([projRes, taskRes, schedRes]) => {
        setProjects(projRes.data || []);
        setTasks(taskRes.data || []);
        setSchedules(schedRes.data || []);
      })
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadData();
  }, []);

  const handleNextTaskStatus = async (task: WorkerTask) => {
    const nextStatusMap: Record<string, string> = {
      TODO: "IN_PROGRESS",
      IN_PROGRESS: "REVIEW",
      REVIEW: "DONE",
    };

    const nextStatus = nextStatusMap[task.status];
    if (!nextStatus) return;

    try {
      await fetchApi(`/api/v1/worker/tasks/${task.id}/status`, {
        method: "PATCH",
        body: JSON.stringify({ status: nextStatus }),
      });
      loadData();
    } catch (err: any) {
      alert(err.message || "Gagal mengubah status tugas");
    }
  };

  if (authLoading || loading) {
    return <div className="empty">Loading dashboard…</div>;
  }

  const inProgressTasks = tasks.filter((t) => t.status === "IN_PROGRESS");
  const todoTasks = tasks.filter((t) => t.status === "TODO");
  const doneTasks = tasks.filter((t) => t.status === "DONE");

  const cards = [
    ["Assigned Projects", projects.length, "🎬", "go"],
    ["Tasks In Progress", inProgressTasks.length, "⚡", "gk"],
    ["Tasks To Do", todoTasks.length, "⏳", "gy"],
    ["Tasks Selesai", doneTasks.length, "✅", "gt"],
  ];

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>
            Halo, {user?.name} <span className="hi">💪</span>
          </h1>
          <p>Portal Kru BDJG Studio — Fokus pada tugas produksi dan jadwal call sheet yang ditugaskan</p>
        </div>
      </div>

      <div className="grid g-stats">
        {cards.map((c) => (
          <div key={c[0] as string} className="card stat">
            <div>
              <div className="lb">{c[0] as string}</div>
              <div className="vl">{c[1] as number}</div>
            </div>
            <div className={`ico ${c[3] as string}`}>{c[2] as string}</div>
          </div>
        ))}
      </div>

      <div className="grid g-21 mt">
        <div className="card">
          <div className="chead">
            <h3>✅ My Assigned Tasks ({tasks.length})</h3>
          </div>
          <div className="cbody">
            {tasks.length > 0 ? (
              <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
                {tasks.map((t) => (
                  <div
                    key={t.id}
                    style={{
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "space-between",
                      padding: "10px 12px",
                      background: "rgba(255,255,255,0.03)",
                      borderRadius: 8,
                      border: "1px solid rgba(255,255,255,0.06)",
                    }}
                  >
                    <div>
                      <div style={{ fontWeight: 700, fontSize: 13, color: "#FFFFFF" }}>{t.title}</div>
                      <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 2 }}>
                        {t.project?.project_number} — {t.project?.name} • Priority: {t.priority}
                      </div>
                    </div>

                    <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                      <span
                        className={`chip ${
                          t.status === "DONE"
                            ? "c-green"
                            : t.status === "IN_PROGRESS"
                            ? "c-blue"
                            : "c-black"
                        }`}
                        style={{ fontSize: 10, fontWeight: 700 }}
                      >
                        {t.status}
                      </span>
                      {t.status !== "DONE" && (
                        <button className="btn btn-p sm" onClick={() => handleNextTaskStatus(t)}>
                          ➔ Next Step
                        </button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="empty">Belum ada tugas yang ditugaskan kepada Anda. 🎉</div>
            )}
          </div>
        </div>

        <div className="card">
          <div className="chead">
            <h3>📅 Production Schedules & Call Sheets</h3>
          </div>
          <div className="cbody">
            {schedules.length > 0 ? (
              schedules.map((e) => (
                <div key={e.id} className="ev ev-SHOOT" style={{ marginBottom: 8 }}>
                  <div style={{ fontWeight: 700 }}>{e.title}</div>
                  <small>
                    {new Date(e.start_time).toLocaleString("id-ID", {
                      weekday: "short",
                      day: "numeric",
                      month: "short",
                      hour: "2-digit",
                      minute: "2-digit",
                    })}
                    {e.location ? ` • ${e.location}` : ""}
                  </small>
                </div>
              ))
            ) : (
              <div className="empty">Tidak ada jadwal syuting mendatang</div>
            )}
          </div>
        </div>
      </div>
    </>
  );
}
