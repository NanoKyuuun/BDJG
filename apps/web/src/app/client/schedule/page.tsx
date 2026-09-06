"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface ScheduleItem {
  id: number;
  title: string;
  schedule_type: string;
  start_time: string;
  end_time?: string;
  location?: string;
  notes?: string;
  project?: {
    id: number;
    name: string;
    project_number: string;
  };
}

export default function ClientSchedulePage() {
  const [schedules, setSchedules] = useState<ScheduleItem[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadSchedules() {
      try {
        setLoading(true);
        const res = await fetchApi<{ data: ScheduleItem[] }>("/api/v1/client/schedules");
        setSchedules(res.data || []);
      } catch (err) {
        console.error("Failed to load client schedules:", err);
      } finally {
        setLoading(false);
      }
    }
    loadSchedules();
  }, []);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight text-white">Production Schedule & Itinerary</h1>
        <p className="text-sm text-zinc-400 mt-1">
          Confirmed shooting days, studio recording sessions, and preview delivery dates.
        </p>
      </div>

      {loading ? (
        <div className="p-12 text-center text-zinc-500 text-xs">Loading itinerary...</div>
      ) : schedules.length === 0 ? (
        <div className="p-12 text-center bg-zinc-900/40 border border-zinc-800 rounded-xl text-xs text-zinc-500">
          <span>No production dates scheduled yet.</span>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {schedules.map((item) => (
            <div
              key={item.id}
              className="p-5 bg-zinc-900 border border-zinc-800 rounded-2xl shadow-xl space-y-3"
            >
              <div className="flex items-start justify-between">
                <span className="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full bg-amber-950 text-amber-400 border border-amber-800">
                  {item.schedule_type}
                </span>
                <span className="font-mono text-xs text-zinc-500">{item.project?.project_number}</span>
              </div>

              <h3 className="text-base font-bold text-white">{item.title}</h3>
              <p className="text-xs text-zinc-400">Project: {item.project?.name || "Production"}</p>

              <div className="pt-3 border-t border-zinc-800/80 space-y-1.5 text-xs text-zinc-300">
                <div className="flex items-center gap-2">
                  <span>📅</span>
                  <span className="font-medium">{item.start_time}</span>
                  {item.end_time && <span className="text-zinc-500">→ {item.end_time}</span>}
                </div>
                {item.location && (
                  <div className="flex items-center gap-2 text-zinc-400">
                    <span>📍</span>
                    <span>{item.location}</span>
                  </div>
                )}
                {item.notes && (
                  <p className="text-xs text-zinc-500 italic mt-2">{item.notes}</p>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
