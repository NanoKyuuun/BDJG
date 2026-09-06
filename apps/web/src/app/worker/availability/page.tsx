"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface ScheduleItem {
  id: number;
  schedule_type: string;
  title: string;
  start_time: string;
  end_time?: string;
  location?: string;
  project_number?: string;
  project_name?: string;
}

export default function WorkerAvailabilityPage() {
  const [schedules, setSchedules] = useState<ScheduleItem[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadSchedules() {
      try {
        setLoading(true);
        const res = await fetchApi<{ data: ScheduleItem[] }>("/api/v1/worker/schedules");
        setSchedules(res.data || []);
      } catch (err) {
        console.error("Failed to load worker schedules:", err);
      } finally {
        setLoading(false);
      }
    }

    loadSchedules();
  }, []);

  return (
    <>
      <div className="pagehead">
        <div>
          <div style={{ fontSize: 11, fontWeight: 700, color: "var(--orange)", letterSpacing: 1.2 }}>
            SCHEDULE &amp; AVAILABILITY
          </div>
          <h1 style={{ marginTop: 4 }}>🗓️ My Availability &amp; Booking Status</h1>
          <p>Kalender jadwal penugasan syuting dan ketersediaan waktu kerja Anda</p>
        </div>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 20 }}>
        <div className="card" style={{ padding: 22 }}>
          <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 6 }}>📅 Jadwal Syuting &amp; Penugasan Aktif</h3>
          <p style={{ fontSize: 12, color: "var(--muted)", marginBottom: 16 }}>
            Tanggal-tanggal di bawah ini telah dikunci oleh Admin untuk jadwal produksi lapangan:
          </p>

          {loading ? (
            <div className="empty">Memuat jadwal...</div>
          ) : schedules.length === 0 ? (
            <div className="empty">Belum ada jadwal syuting aktif yang terdaftar</div>
          ) : (
            <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
              {schedules.map((s) => (
                <div
                  key={s.id}
                  style={{
                    padding: "12px 14px",
                    background: "var(--card2)",
                    borderRadius: 8,
                    border: "1px solid var(--line)",
                    display: "flex",
                    flexDirection: "column",
                    gap: 4,
                  }}
                >
                  <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                    <span className="chip c-orange" style={{ fontSize: 10, fontWeight: 800 }}>
                      🎥 {s.schedule_type}
                    </span>
                    <span style={{ fontSize: 11, color: "var(--yellow)", fontWeight: 700 }}>
                      {new Date(s.start_time).toLocaleDateString("id-ID", {
                        weekday: "short",
                        day: "numeric",
                        month: "short",
                      })}
                    </span>
                  </div>
                  <div style={{ fontWeight: 700, fontSize: 13, color: "#FFF" }}>{s.title}</div>
                  {s.location && <div style={{ fontSize: 11, color: "var(--muted)" }}>📍 {s.location}</div>}
                </div>
              ))}
            </div>
          )}
        </div>

        <div className="card" style={{ padding: 22 }}>
          <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 6 }}>🛡️ Status Ketersediaan Worker</h3>
          <p style={{ fontSize: 12, color: "var(--muted)", marginBottom: 16 }}>
            Status profil Anda saat ini di sistem studio:
          </p>

          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            <div style={{ padding: "14px", background: "rgba(22, 163, 74, 0.1)", borderRadius: 8, border: "1px solid rgba(22, 163, 74, 0.2)" }}>
              <div style={{ fontSize: 11, fontWeight: 700, color: "var(--green)" }}>STATUS PROFIL</div>
              <div style={{ fontSize: 18, fontWeight: 800, color: "#FFF", marginTop: 2 }}>🟢 ACTIVE &amp; READY TO SHOOT</div>
              <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 4 }}>
                Admin dapat menugaskan Anda ke proyek baru dan menjadwalkan agenda syuting.
              </div>
            </div>

            <div style={{ padding: "14px", background: "var(--card2)", borderRadius: 8, border: "1px solid var(--line)" }}>
              <div style={{ fontSize: 11, fontWeight: 700, color: "var(--muted)" }}>PENGAJUAN LIBUR / OFF DAY</div>
              <p style={{ fontSize: 12, color: "var(--muted)", marginTop: 4, lineHeight: 1.4 }}>
                Jika Anda memiliki jadwal berhalangan hadir atau izin cuti, silakan beritahu Project Manager studio minimal H-3 sebelum jadwal syuting dikunci.
              </p>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
