"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface ScheduleItem {
  id: number;
  public_id: string;
  project_id?: number;
  schedule_type: "SHOOT" | "MEETING" | "RECCE" | "EDIT_DEADLINE" | "DELIVERY_DEADLINE";
  title: string;
  start_time: string;
  end_time?: string;
  location?: string;
  notes?: string;
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

const TYPE_CONFIG = {
  SHOOT: { label: "Shooting Day", color: "var(--orange)", bg: "rgba(249, 115, 22, 0.15)", icon: "🎥" },
  MEETING: { label: "Client Meeting", color: "var(--blue)", bg: "rgba(27, 79, 216, 0.15)", icon: "👥" },
  RECCE: { label: "Location Recce", color: "var(--teal)", bg: "rgba(13, 148, 136, 0.15)", icon: "📍" },
  EDIT_DEADLINE: { label: "Editing Cut Due", color: "var(--yellow)", bg: "rgba(255, 199, 0, 0.15)", icon: "✂️" },
  DELIVERY_DEADLINE: { label: "Final Delivery", color: "var(--red)", bg: "rgba(225, 29, 72, 0.15)", icon: "🚀" },
};

export default function ProductionCalendarPage() {
  const [schedules, setSchedules] = useState<ScheduleItem[]>([]);
  const [projects, setProjects] = useState<ProjectOption[]>([]);
  const [loading, setLoading] = useState(true);
  const [typeFilter, setTypeFilter] = useState<string>("");
  const [currentDate, setCurrentDate] = useState(new Date());

  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [formData, setFormData] = useState({
    project_id: "",
    schedule_type: "SHOOT",
    title: "",
    start_time: "",
    end_time: "",
    location: "",
    notes: "",
  });
  const [submitting, setSubmitting] = useState(false);

  const loadData = async () => {
    setLoading(true);
    try {
      const [schedulesRes, projectsRes] = await Promise.all([
        fetchApi<{ data: ScheduleItem[] }>(
          `/api/v1/admin/schedules?per_page=100${typeFilter ? `&schedule_type=${typeFilter}` : ""}`
        ),
        fetchApi<{ data: ProjectOption[] }>("/api/v1/admin/projects?per_page=100"),
      ]);

      setSchedules(schedulesRes.data || []);
      setProjects(projectsRes.data || []);
    } catch (err) {
      console.error("Failed to load schedules:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [typeFilter]);

  const handleCreateSchedule = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.title || !formData.start_time) {
      alert("Isi judul dan waktu mulai jadwal");
      return;
    }
    setSubmitting(true);
    try {
      await fetchApi("/api/v1/admin/schedules", {
        method: "POST",
        body: JSON.stringify({
          project_id: formData.project_id ? Number(formData.project_id) : undefined,
          schedule_type: formData.schedule_type,
          title: formData.title,
          start_time: formData.start_time,
          end_time: formData.end_time || undefined,
          location: formData.location || undefined,
          notes: formData.notes || undefined,
        }),
      });
      setIsModalOpen(false);
      setFormData({
        project_id: "",
        schedule_type: "SHOOT",
        title: "",
        start_time: "",
        end_time: "",
        location: "",
        notes: "",
      });
      loadData();
    } catch (err: any) {
      alert(err.message || "Failed to create schedule");
    } finally {
      setSubmitting(false);
    }
  };

  const handleDeleteSchedule = async (id: number) => {
    if (!confirm("Hapus jadwal ini?")) return;
    try {
      await fetchApi(`/api/v1/admin/schedules/${id}`, { method: "DELETE" });
      setSchedules((prev) => prev.filter((s) => s.id !== id));
    } catch (err: any) {
      alert(err.message || "Failed to delete schedule");
    }
  };

  // Calendar Helpers
  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();
  const firstDayOfMonth = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  const monthNames = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
  ];

  return (
    <>
      <div className="pagehead">
        <div>
          <div style={{ fontSize: 11, fontWeight: 700, color: "var(--yellow)", letterSpacing: 1.2 }}>
            PRODUCTION TIMELINE
          </div>
          <h1 style={{ marginTop: 4 }}>📅 Production &amp; Shoot Calendar</h1>
          <p>Kalender jadwal syuting, meeting client, deadline revisi, dan handover final</p>
        </div>
        <div style={{ display: "flex", gap: 10, alignItems: "center" }}>
          <select
            value={typeFilter}
            onChange={(e) => setTypeFilter(e.target.value)}
            style={{ padding: "8px 14px", borderRadius: 10, border: "1px solid var(--line)", fontSize: 13 }}
          >
            <option value="">Semua Kategori Jadwal</option>
            <option value="SHOOT">🎥 Shooting Day</option>
            <option value="MEETING">👥 Client Meeting</option>
            <option value="RECCE">📍 Location Recce</option>
            <option value="EDIT_DEADLINE">✂️ Editing Cut Due</option>
            <option value="DELIVERY_DEADLINE">🚀 Final Delivery</option>
          </select>

          <button className="btn btn-p" onClick={() => setIsModalOpen(true)}>
            + Tambah Jadwal
          </button>
        </div>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "2fr 1fr", gap: 20 }}>
        {/* Main Calendar View */}
        <div className="card" style={{ padding: 20 }}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 20 }}>
            <h2 style={{ fontSize: 18, fontWeight: 800 }}>
              {monthNames[month]} {year}
            </h2>
            <div style={{ display: "flex", gap: 8 }}>
              <button
                className="btn btn-o"
                style={{ padding: "4px 12px", fontSize: 12 }}
                onClick={() => setCurrentDate(new Date(year, month - 1, 1))}
              >
                ◀ Sebelumnya
              </button>
              <button
                className="btn btn-o"
                style={{ padding: "4px 12px", fontSize: 12 }}
                onClick={() => setCurrentDate(new Date())}
              >
                Hari Ini
              </button>
              <button
                className="btn btn-o"
                style={{ padding: "4px 12px", fontSize: 12 }}
                onClick={() => setCurrentDate(new Date(year, month + 1, 1))}
              >
                Berikutnya ▶
              </button>
            </div>
          </div>

          {/* Weekday Header */}
          <div
            style={{
              display: "grid",
              gridTemplateColumns: "repeat(7, 1fr)",
              textAlign: "center",
              fontWeight: 700,
              fontSize: 11,
              color: "var(--muted)",
              marginBottom: 8,
            }}
          >
            {["MIN", "SEN", "SEL", "RAB", "KAM", "JUM", "SAB"].map((day) => (
              <div key={day} style={{ padding: "6px 0" }}>
                {day}
              </div>
            ))}
          </div>

          {/* Days Grid */}
          <div
            style={{
              display: "grid",
              gridTemplateColumns: "repeat(7, 1fr)",
              gap: 6,
            }}
          >
            {/* Empty slots for previous month */}
            {Array.from({ length: firstDayOfMonth }).map((_, i) => (
              <div
                key={`empty-${i}`}
                style={{
                  minHeight: 80,
                  background: "rgba(255, 255, 255, 0.01)",
                  borderRadius: 8,
                  opacity: 0.3,
                }}
              />
            ))}

            {/* Days of current month */}
            {Array.from({ length: daysInMonth }).map((_, i) => {
              const dayNum = i + 1;
              const dateStr = `${year}-${String(month + 1).padStart(2, "0")}-${String(dayNum).padStart(2, "0")}`;
              const dayEvents = schedules.filter((s) => s.start_time.startsWith(dateStr));
              const isToday =
                new Date().toISOString().slice(0, 10) === dateStr;

              return (
                <div
                  key={`day-${dayNum}`}
                  style={{
                    minHeight: 90,
                    background: isToday ? "rgba(249, 115, 22, 0.06)" : "var(--card2)",
                    border: isToday ? "1px solid var(--orange)" : "1px solid rgba(255, 255, 255, 0.05)",
                    borderRadius: 8,
                    padding: 6,
                    display: "flex",
                    flexDirection: "column",
                    gap: 4,
                  }}
                >
                  <div
                    style={{
                      fontSize: 11,
                      fontWeight: 700,
                      color: isToday ? "var(--orange)" : "var(--ink)",
                      marginBottom: 2,
                    }}
                  >
                    {dayNum}
                  </div>

                  <div style={{ display: "flex", flexDirection: "column", gap: 3, overflowY: "auto" }}>
                    {dayEvents.map((e) => {
                      const cfg = TYPE_CONFIG[e.schedule_type] || TYPE_CONFIG.SHOOT;
                      return (
                        <div
                          key={e.id}
                          title={`${e.title} (${e.location || "No location"})`}
                          style={{
                            fontSize: 10,
                            fontWeight: 600,
                            padding: "2px 5px",
                            borderRadius: 4,
                            background: cfg.bg,
                            color: cfg.color,
                            borderLeft: `2px solid ${cfg.color}`,
                            whiteSpace: "nowrap",
                            overflow: "hidden",
                            textOverflow: "ellipsis",
                          }}
                        >
                          {cfg.icon} {e.title}
                        </div>
                      );
                    })}
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Upcoming Agenda Sidebar */}
        <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
          <div className="card" style={{ padding: 18 }}>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 14 }}>
              <h3 style={{ fontSize: 15, fontWeight: 700 }}>📌 Upcoming Production Agenda</h3>
              <span className="chip" style={{ fontSize: 11 }}>{schedules.length} Items</span>
            </div>

            {loading ? (
              <div className="empty">Memuat jadwal...</div>
            ) : schedules.length === 0 ? (
              <div className="empty">Belum ada jadwal yang terdaftar</div>
            ) : (
              <div style={{ display: "flex", flexDirection: "column", gap: 10, maxHeight: 600, overflowY: "auto" }}>
                {schedules.map((item) => {
                  const cfg = TYPE_CONFIG[item.schedule_type] || TYPE_CONFIG.SHOOT;
                  return (
                    <div
                      key={item.id}
                      style={{
                        padding: 12,
                        background: "var(--card2)",
                        border: "1px solid rgba(255, 255, 255, 0.06)",
                        borderRadius: 10,
                        display: "flex",
                        flexDirection: "column",
                        gap: 6,
                      }}
                    >
                      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start" }}>
                        <span
                          style={{
                            fontSize: 10,
                            fontWeight: 800,
                            color: cfg.color,
                            background: cfg.bg,
                            padding: "2px 6px",
                            borderRadius: 4,
                          }}
                        >
                          {cfg.icon} {cfg.label}
                        </span>
                        <button
                          onClick={() => handleDeleteSchedule(item.id)}
                          style={{
                            background: "none",
                            border: "none",
                            color: "var(--muted)",
                            cursor: "pointer",
                            fontSize: 12,
                          }}
                        >
                          🗑️
                        </button>
                      </div>

                      <div style={{ fontWeight: 700, fontSize: 13, color: "#FFF" }}>{item.title}</div>

                      {item.project && (
                        <Link
                          href={`/admin/projects/${item.project.id}`}
                          style={{ fontSize: 11, color: "var(--orange)", textDecoration: "none" }}
                        >
                          🎬 {item.project.project_number} — {item.project.name}
                        </Link>
                      )}

                      <div style={{ fontSize: 11, color: "var(--muted)", display: "flex", flexDirection: "column", gap: 2 }}>
                        <div>
                          ⏰ {new Date(item.start_time).toLocaleString("id-ID", {
                            weekday: "short",
                            day: "numeric",
                            month: "short",
                            hour: "2-digit",
                            minute: "2-digit",
                          })}
                        </div>
                        {item.location && <div>📍 {item.location}</div>}
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Modal Add Schedule */}
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
              maxWidth: 500,
              background: "#121215",
              borderRadius: 16,
              border: "1px solid var(--line)",
              padding: 24,
            }}
          >
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 18 }}>
              <h3 style={{ fontSize: 18, fontWeight: 700 }}>+ Tambah Jadwal Produksi</h3>
              <button
                onClick={() => setIsModalOpen(false)}
                style={{ background: "none", border: "none", color: "var(--muted)", cursor: "pointer", fontSize: 20 }}
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleCreateSchedule} style={{ display: "flex", flexDirection: "column", gap: 14 }}>
              <div>
                <label style={{ display: "block", fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 6 }}>
                  PROJECT (OPSIONAL)
                </label>
                <select
                  value={formData.project_id}
                  onChange={(e) => setFormData({ ...formData, project_id: e.target.value })}
                  style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid var(--line)" }}
                >
                  <option value="">Tidak terikat project spesifik</option>
                  {projects.map((p) => (
                    <option key={p.id} value={p.id}>
                      {p.project_number} — {p.name}
                    </option>
                  ))}
                </select>
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                <div>
                  <label style={{ display: "block", fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 6 }}>
                    KATEGORI JADWAL *
                  </label>
                  <select
                    value={formData.schedule_type}
                    onChange={(e) => setFormData({ ...formData, schedule_type: e.target.value })}
                    required
                    style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid var(--line)" }}
                  >
                    <option value="SHOOT">🎥 Shooting Day</option>
                    <option value="MEETING">👥 Client Meeting</option>
                    <option value="RECCE">📍 Location Recce</option>
                    <option value="EDIT_DEADLINE">✂️ Editing Cut Due</option>
                    <option value="DELIVERY_DEADLINE">🚀 Final Delivery</option>
                  </select>
                </div>

                <div>
                  <label style={{ display: "block", fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 6 }}>
                    LOKASI
                  </label>
                  <input
                    type="text"
                    placeholder="Studio / Venue / Zoom"
                    value={formData.location}
                    onChange={(e) => setFormData({ ...formData, location: e.target.value })}
                    style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid var(--line)" }}
                  />
                </div>
              </div>

              <div>
                <label style={{ display: "block", fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 6 }}>
                  JUDUL ACARA / JADWAL *
                </label>
                <input
                  type="text"
                  placeholder="Contoh: Shooting Wedding Day 1, Final Cut Handover..."
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  required
                  style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid var(--line)" }}
                />
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                <div>
                  <label style={{ display: "block", fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 6 }}>
                    WAKTU MULAI *
                  </label>
                  <input
                    type="datetime-local"
                    value={formData.start_time}
                    onChange={(e) => setFormData({ ...formData, start_time: e.target.value })}
                    required
                    style={{ width: "100%", padding: "10px 12px", borderRadius: 8, border: "1px solid var(--line)" }}
                  />
                </div>

                <div>
                  <label style={{ display: "block", fontSize: 11, fontWeight: 700, color: "var(--muted)", marginBottom: 6 }}>
                    WAKTU SELESAI
                  </label>
                  <input
                    type="datetime-local"
                    value={formData.end_time}
                    onChange={(e) => setFormData({ ...formData, end_time: e.target.value })}
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
                  {submitting ? "Menyimpan..." : "Simpan Jadwal"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  );
}
