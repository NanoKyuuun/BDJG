"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface TaskItem {
  id: number;
  public_id: string;
  project_id: number;
  title: string;
  description?: string;
  status: "TODO" | "IN_PROGRESS" | "REVIEW" | "DONE" | "BLOCKED";
  priority?: "LOW" | "MEDIUM" | "HIGH" | "URGENT";
  due_date?: string;
  assigned_worker_id?: number;
  assigned_worker?: {
    id: number;
    profession?: string;
    user?: {
      name: string;
      email: string;
    };
  };
  project?: {
    id: number;
    project_number: string;
    name: string;
  };
  created_at: string;
}

interface ProjectOption {
  id: number;
  project_number: string;
  name: string;
}

interface WorkerOption {
  id: number;
  profession?: string;
  user?: {
    id: number;
    name: string;
  };
}

const COLUMNS: Array<{ id: TaskItem["status"]; title: string; color: string; badgeClass: string }> = [
  { id: "TODO", title: "To Do", color: "var(--muted)", badgeClass: "c-grey" },
  { id: "IN_PROGRESS", title: "In Progress", color: "var(--blue)", badgeClass: "c-blue" },
  { id: "REVIEW", title: "Under Review", color: "var(--yellow)", badgeClass: "c-yellow" },
  { id: "BLOCKED", title: "Blocked", color: "var(--red)", badgeClass: "c-red" },
  { id: "DONE", title: "Done", color: "var(--green)", badgeClass: "c-green" },
];

export default function ProductionBoardPage() {
  const [tasks, setTasks] = useState<TaskItem[]>([]);
  const [projects, setProjects] = useState<ProjectOption[]>([]);
  const [workers, setWorkers] = useState<WorkerOption[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedProjectId, setSelectedProjectId] = useState<string>("");
  const [searchQuery, setSearchQuery] = useState("");

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [formData, setFormData] = useState({
    project_id: "",
    title: "",
    description: "",
    status: "TODO",
    priority: "MEDIUM",
    due_date: "",
    assigned_worker_id: "",
  });
  const [submitting, setSubmitting] = useState(false);

  const loadData = async () => {
    setLoading(true);
    try {
      const [tasksRes, projectsRes, workersRes] = await Promise.all([
        fetchApi<{ data: TaskItem[] }>(
          `/api/v1/admin/tasks?per_page=100${selectedProjectId ? `&project_id=${selectedProjectId}` : ""}`
        ),
        fetchApi<{ data: ProjectOption[] }>("/api/v1/admin/projects?per_page=100"),
        fetchApi<{ data: WorkerOption[] }>("/api/v1/admin/workers?per_page=100").catch(() => ({ data: [] })),
      ]);

      setTasks(tasksRes.data || []);
      setProjects(projectsRes.data || []);
      setWorkers(workersRes.data || []);
    } catch (err) {
      console.error("Failed to load production board data:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [selectedProjectId]);

  const handleStatusChange = async (taskId: number, newStatus: TaskItem["status"]) => {
    try {
      await fetchApi(`/api/v1/admin/tasks/${taskId}/status`, {
        method: "PATCH",
        body: JSON.stringify({ status: newStatus }),
      });
      setTasks((prev) =>
        prev.map((t) => (t.id === taskId ? { ...t, status: newStatus } : t))
      );
    } catch (err: any) {
      alert(err.message || "Failed to update task status");
    }
  };

  const handleCreateTask = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.project_id || !formData.title) {
      alert("Pilih project dan isi judul task");
      return;
    }
    setSubmitting(true);
    try {
      await fetchApi("/api/v1/admin/tasks", {
        method: "POST",
        body: JSON.stringify({
          project_id: Number(formData.project_id),
          title: formData.title,
          description: formData.description || undefined,
          status: formData.status,
          priority: formData.priority,
          due_date: formData.due_date || undefined,
          assigned_worker_id: formData.assigned_worker_id ? Number(formData.assigned_worker_id) : undefined,
        }),
      });
      setIsModalOpen(false);
      setFormData({
        project_id: "",
        title: "",
        description: "",
        status: "TODO",
        priority: "MEDIUM",
        due_date: "",
        assigned_worker_id: "",
      });
      loadData();
    } catch (err: any) {
      alert(err.message || "Failed to create task");
    } finally {
      setSubmitting(false);
    }
  };

  const filteredTasks = tasks.filter((t) => {
    if (!searchQuery) return true;
    const q = searchQuery.toLowerCase();
    return (
      t.title.toLowerCase().includes(q) ||
      t.project?.name.toLowerCase().includes(q) ||
      t.project?.project_number.toLowerCase().includes(q) ||
      t.assigned_worker?.user?.name.toLowerCase().includes(q)
    );
  });

  return (
    <>
      <div className="pagehead">
        <div>
          <div style={{ fontSize: 11, fontWeight: 700, color: "var(--orange)", letterSpacing: 1.2 }}>
            PRODUCTION &amp; POST-PRODUCTION
          </div>
          <h1 style={{ marginTop: 4 }}>🗂️ Production Task Board</h1>
          <p>Pantau progres dan status tugas produksi studio secara visual</p>
        </div>
        <div style={{ display: "flex", gap: 10, alignItems: "center" }}>
          <select
            value={selectedProjectId}
            onChange={(e) => setSelectedProjectId(e.target.value)}
            style={{
              padding: "8px 14px",
              borderRadius: 10,
              border: "1px solid var(--line)",
              fontSize: 13,
            }}
          >
            <option value="">Semua Proyek ({projects.length})</option>
            {projects.map((p) => (
              <option key={p.id} value={p.id}>
                {p.project_number} — {p.name}
              </option>
            ))}
          </select>

          <button className="btn btn-p" onClick={() => setIsModalOpen(true)}>
            + Tambah Task
          </button>
        </div>
      </div>

      {/* Filter / Search bar */}
      <div style={{ display: "flex", gap: 12, marginBottom: 20 }}>
        <input
          type="text"
          placeholder="Filter berdasarkan judul tugas, nomor proyek, atau nama worker…"
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          style={{
            flex: 1,
            padding: "10px 14px",
            borderRadius: 10,
            border: "1px solid var(--line)",
            fontSize: 13,
          }}
        />
        <button className="btn btn-o" onClick={loadData}>
          🔄 Refresh
        </button>
      </div>

      {loading ? (
        <div className="empty" style={{ padding: "60px 0" }}>
          Memuat Kanban Board…
        </div>
      ) : (
        <div
          style={{
            display: "grid",
            gridTemplateColumns: "repeat(5, minmax(240px, 1fr))",
            gap: 16,
            alignItems: "start",
            overflowX: "auto",
            paddingBottom: 20,
          }}
        >
          {COLUMNS.map((col) => {
            const colTasks = filteredTasks.filter((t) => t.status === col.id);
            return (
              <div
                key={col.id}
                style={{
                  background: "rgba(255, 255, 255, 0.02)",
                  border: "1px solid var(--line)",
                  borderRadius: 14,
                  padding: 14,
                  minHeight: 480,
                  display: "flex",
                  flexDirection: "column",
                  gap: 12,
                }}
              >
                {/* Column Header */}
                <div
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    alignItems: "center",
                    paddingBottom: 10,
                    borderBottom: "1px solid rgba(255, 255, 255, 0.06)",
                  }}
                >
                  <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                    <span
                      style={{
                        width: 10,
                        height: 10,
                        borderRadius: "50%",
                        background: col.color,
                      }}
                    />
                    <span style={{ fontWeight: 700, fontSize: 13 }}>{col.title}</span>
                  </div>
                  <span className="chip" style={{ fontSize: 11, padding: "2px 8px" }}>
                    {colTasks.length}
                  </span>
                </div>

                {/* Column Cards */}
                <div style={{ display: "flex", flexDirection: "column", gap: 10, flex: 1 }}>
                  {colTasks.length === 0 ? (
                    <div
                      style={{
                        padding: "30px 10px",
                        textAlign: "center",
                        fontSize: 12,
                        color: "var(--muted)",
                        border: "1px dashed rgba(255, 255, 255, 0.08)",
                        borderRadius: 8,
                      }}
                    >
                      Tidak ada tugas
                    </div>
                  ) : (
                    colTasks.map((t) => (
                      <div
                        key={t.id}
                        className="card"
                        style={{
                          padding: 12,
                          background: "var(--card2)",
                          border: "1px solid rgba(255, 255, 255, 0.08)",
                          borderRadius: 10,
                          display: "flex",
                          flexDirection: "column",
                          gap: 8,
                          transition: "all 0.2s ease",
                        }}
                      >
                        {/* Project Tag */}
                        {t.project && (
                          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                            <Link
                              href={`/admin/projects/${t.project.id}`}
                              style={{
                                fontSize: 11,
                                fontWeight: 700,
                                color: "var(--orange)",
                                textDecoration: "none",
                              }}
                            >
                              {t.project.project_number}
                            </Link>
                            {t.priority && (
                              <span
                                style={{
                                  fontSize: 9.5,
                                  fontWeight: 800,
                                  textTransform: "uppercase",
                                  padding: "1px 6px",
                                  borderRadius: 4,
                                  background:
                                    t.priority === "URGENT" || t.priority === "HIGH"
                                      ? "rgba(225, 29, 72, 0.15)"
                                      : "rgba(255, 255, 255, 0.08)",
                                  color:
                                    t.priority === "URGENT" || t.priority === "HIGH"
                                      ? "var(--red)"
                                      : "var(--muted)",
                                }}
                              >
                                {t.priority}
                              </span>
                            )}
                          </div>
                        )}

                        {/* Title & Description */}
                        <div style={{ fontWeight: 700, fontSize: 13, color: "#FFFFFF", lineHeight: 1.4 }}>
                          {t.title}
                        </div>
                        {t.description && (
                          <div
                            style={{
                              fontSize: 11.5,
                              color: "var(--muted)",
                              lineHeight: 1.4,
                              display: "-webkit-box",
                              WebkitLineClamp: 2,
                              WebkitBoxOrient: "vertical",
                              overflow: "hidden",
                            }}
                          >
                            {t.description}
                          </div>
                        )}

                        {/* Worker & Due Date */}
                        <div
                          style={{
                            display: "flex",
                            justifyContent: "space-between",
                            alignItems: "center",
                            paddingTop: 6,
                            borderTop: "1px solid rgba(255, 255, 255, 0.05)",
                            fontSize: 11,
                          }}
                        >
                          <span style={{ color: "var(--muted)", display: "flex", alignItems: "center", gap: 4 }}>
                            🧑‍🎨 {t.assigned_worker?.user?.name || "Unassigned"}
                          </span>
                          {t.due_date && (
                            <span style={{ color: "var(--yellow)", fontWeight: 600 }}>
                              📅 {new Date(t.due_date).toLocaleDateString("id-ID", { month: "short", day: "numeric" })}
                            </span>
                          )}
                        </div>

                        {/* Quick Move Status Selector */}
                        <div style={{ paddingTop: 4 }}>
                          <select
                            value={t.status}
                            onChange={(e) => handleStatusChange(t.id, e.target.value as TaskItem["status"])}
                            style={{
                              width: "100%",
                              padding: "4px 8px",
                              fontSize: 10.5,
                              borderRadius: 6,
                              background: "rgba(255, 255, 255, 0.05)",
                              border: "1px solid rgba(255, 255, 255, 0.1)",
                              color: "var(--ink)",
                              cursor: "pointer",
                            }}
                          >
                            {COLUMNS.map((c) => (
                              <option key={c.id} value={c.id}>
                                Pindah ke: {c.title}
                              </option>
                            ))}
                          </select>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>
            );
          })}
        </div>
      )}

      {/* Modal Add Task */}
      {isModalOpen && (
        <div
          style={{
            position: "fixed",
            inset: 0,
            background: "rgba(0,0,0,0.75)",
            backdropFilter: "blur(6px)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 100,
            padding: 20,
          }}
        >
          <div
            className="card"
            style={{
              width: "100%",
              maxWidth: 520,
              background: "#121215",
              borderRadius: 16,
              border: "1px solid var(--line)",
              padding: 24,
            }}
          >
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 18 }}>
              <h3 style={{ fontSize: 18, fontWeight: 700 }}>+ Tambah Task Produksi Baru</h3>
              <button
                onClick={() => setIsModalOpen(false)}
                style={{ background: "none", border: "none", color: "var(--muted)", cursor: "pointer", fontSize: 20 }}
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleCreateTask} style={{ display: "flex", flexDirection: "column", gap: 14 }}>
              <div>
                <label style={{ display: "block", fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 6 }}>
                  PROJECT *
                </label>
                <select
                  value={formData.project_id}
                  onChange={(e) => setFormData({ ...formData, project_id: e.target.value })}
                  required
                  style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid var(--line)" }}
                >
                  <option value="">Pilih Proyek...</option>
                  {projects.map((p) => (
                    <option key={p.id} value={p.id}>
                      {p.project_number} — {p.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label style={{ display: "block", fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 6 }}>
                  JUDUL TUGAS *
                </label>
                <input
                  type="text"
                  placeholder="Contoh: Color Grading V02, Shoot Day 1 Drone..."
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  required
                  style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid var(--line)" }}
                />
              </div>

              <div>
                <label style={{ display: "block", fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 6 }}>
                  DESKRIPSI / BRIEF TUGAS
                </label>
                <textarea
                  rows={3}
                  placeholder="Catatan instruksi pengerjaan..."
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid var(--line)" }}
                />
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                <div>
                  <label style={{ display: "block", fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 6 }}>
                    STATUS AWAL
                  </label>
                  <select
                    value={formData.status}
                    onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                    style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid var(--line)" }}
                  >
                    <option value="TODO">To Do</option>
                    <option value="IN_PROGRESS">In Progress</option>
                    <option value="REVIEW">Review</option>
                    <option value="BLOCKED">Blocked</option>
                  </select>
                </div>

                <div>
                  <label style={{ display: "block", fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 6 }}>
                    PRIORITAS
                  </label>
                  <select
                    value={formData.priority}
                    onChange={(e) => setFormData({ ...formData, priority: e.target.value })}
                    style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid var(--line)" }}
                  >
                    <option value="LOW">Low</option>
                    <option value="MEDIUM">Medium</option>
                    <option value="HIGH">High</option>
                    <option value="URGENT">Urgent</option>
                  </select>
                </div>
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                <div>
                  <label style={{ display: "block", fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 6 }}>
                    ASSIGN WORKER
                  </label>
                  <select
                    value={formData.assigned_worker_id}
                    onChange={(e) => setFormData({ ...formData, assigned_worker_id: e.target.value })}
                    style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid var(--line)" }}
                  >
                    <option value="">Belum Ditugaskan</option>
                    {workers.map((w) => (
                      <option key={w.id} value={w.id}>
                        {w.user?.name} {w.profession ? `(${w.profession})` : ""}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label style={{ display: "block", fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 6 }}>
                    DEADLINE
                  </label>
                  <input
                    type="date"
                    value={formData.due_date}
                    onChange={(e) => setFormData({ ...formData, due_date: e.target.value })}
                    style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid var(--line)" }}
                  />
                </div>
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: 10, marginTop: 10 }}>
                <button
                  type="button"
                  className="btn btn-o"
                  onClick={() => setIsModalOpen(false)}
                  disabled={submitting}
                >
                  Batal
                </button>
                <button type="submit" className="btn btn-p" disabled={submitting}>
                  {submitting ? "Menyimpan..." : "Simpan Task"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  );
}
