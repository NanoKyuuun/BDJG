// Demo fixtures ported from tamplate-dashboard/hitam-dashboard.html.
// User requested copying the template's demo data into the dashboards.
// This module is intentionally isolated so it can be replaced by real
// backend data later without touching the page markup.

export type Role = "admin" | "worker" | "client";

export const ROLE_META: Record<
  Role,
  { title: string; hello: string; sub: string; icon: string }
> = {
  admin: {
    title: "Admin",
    hello: "Halo",
    sub: "Operational overview — Selasa, 25 Agu 2026",
    icon: "🛠️",
  },
  worker: {
    title: "Worker · Editor",
    hello: "Halo",
    sub: "Fokus pada task & project yang ditugaskan",
    icon: "🎬",
  },
  client: {
    title: "Client",
    hello: "Halo",
    sub: "Pantau project, preview, dan pembayaran Anda",
    icon: "🎉",
  },
};

export const PROJ_ST: Record<string, [string, string]> = {
  DRAFT: ["gray", "Draft"],
  PRE_PRODUCTION: ["blue", "Pre-Production"],
  PRODUCTION: ["orange", "Production"],
  POST_PRODUCTION: ["yellow", "Post-Production"],
  INTERNAL_REVIEW: ["violet", "Internal Review"],
  CLIENT_REVIEW: ["teal", "Client Review"],
  REVISION: ["red", "Revision"],
  FINAL_APPROVAL: ["green", "Final Approval"],
  FINAL_DELIVERY: ["green", "Final Delivery"],
  COMPLETED: ["green", "Completed"],
  ARCHIVED: ["gray", "Archived"],
};

export const TASK_ST: Record<string, [string, string]> = {
  TODO: ["gray", "TODO"],
  IN_PROGRESS: ["blue", "IN PROGRESS"],
  REVIEW: ["violet", "REVIEW"],
  BLOCKED: ["red", "BLOCKED"],
  DONE: ["green", "DONE"],
  CANCELLED: ["gray", "CANCELLED"],
};

export const REV_ST: Record<string, [string, string]> = {
  REQUESTED: ["orange", "REQUESTED"],
  TRIAGE: ["yellow", "TRIAGE"],
  IN_PROGRESS: ["blue", "IN PROGRESS"],
  INTERNAL_REVIEW: ["violet", "INTERNAL REVIEW"],
  READY_FOR_CLIENT: ["teal", "READY FOR CLIENT"],
  APPROVED: ["green", "APPROVED"],
  CLOSED: ["gray", "CLOSED"],
};

export const INV_ST: Record<string, [string, string]> = {
  DRAFT: ["gray", "DRAFT"],
  ISSUED: ["blue", "ISSUED"],
  PARTIALLY_PAID: ["yellow", "PARTIAL"],
  PAID: ["green", "PAID"],
  OVERDUE: ["red", "OVERDUE"],
  VOID: ["gray", "VOID"],
  REFUNDED: ["violet", "REFUNDED"],
};

export interface Project {
  id: string;
  name: string;
  client: string;
  service: string;
  pkg: string;
  status: string;
  deadline: string;
  value: number;
  paid: number;
  workers: string[];
}

export interface Task {
  id: string;
  proj: string;
  title: string;
  ass: string;
  status: string;
  due: string;
  overdue?: boolean;
}

export interface Revision {
  id: string;
  proj: string;
  round: number;
  status: string;
  ass: string;
  due: string;
  items: { txt: string; done: boolean }[];
}

export interface ScheduleEvent {
  day: number;
  type: string;
  title: string;
  proj: string;
  time: string;
}

export interface Activity {
  t: string;
  who: string;
  txt: string;
  detail: string;
  c: string;
}

export interface Notification {
  ico: string;
  txt: string;
  time: string;
  unread: boolean;
}

interface Worker {
  id: string;
  name: string;
  prof: string;
}

interface Invoice {
  id: string;
  num: string;
  proj: string;
  label: string;
  amt: number;
  status: string;
  due: string;
}

export const WORKERS: Worker[] = [
  { id: "w1", name: "Budi", prof: "Director" },
  { id: "w2", name: "Andi", prof: "Videographer" },
  { id: "w3", name: "Rama", prof: "Photographer" },
  { id: "w4", name: "Rizky", prof: "Drone Pilot" },
  { id: "w5", name: "Fajar", prof: "Editor" },
  { id: "w6", name: "Akbar", prof: "Colorist" },
  { id: "w7", name: "Sinta", prof: "Motion Designer" },
];

export const PROJECTS: Project[] = [
  { id: "p1", name: "Video Angkatan — SMA Nusantara", client: "SMA Nusantara", service: "Videography", pkg: "Cinematic", status: "POST_PRODUCTION", deadline: "12 Sep 2026", value: 15000000, paid: 7500000, workers: ["w1", "w2", "w4", "w5", "w6"] },
  { id: "p2", name: "Wedding Film — Ayudia & Dimas", client: "Ayudia Pratiwi", service: "Videography", pkg: "Premium", status: "CLIENT_REVIEW", deadline: "05 Sep 2026", value: 22000000, paid: 11000000, workers: ["w1", "w2", "w5"] },
  { id: "p3", name: "Company Profile — PT Aruna", client: "PT Aruna Karya", service: "Film Production", pkg: "Custom", status: "PRODUCTION", deadline: "28 Sep 2026", value: 35000000, paid: 17500000, workers: ["w1", "w2", "w4"] },
  { id: "p4", name: "Video Angkatan — SMP 5", client: "SMP Negeri 5", service: "Videography", pkg: "Basic", status: "PRE_PRODUCTION", deadline: "20 Okt 2026", value: 9000000, paid: 0, workers: ["w2"] },
  { id: "p5", name: "Music Video — Senja", client: "Nada Records", service: "Film Production", pkg: "Cinematic", status: "REVISION", deadline: "08 Sep 2026", value: 18000000, paid: 9000000, workers: ["w1", "w5", "w7"] },
  { id: "p6", name: "Wedding Photo — Rania & Galih", client: "Rania Salsabila", service: "Photography", pkg: "Premium", status: "COMPLETED", deadline: "15 Agu 2026", value: 12000000, paid: 12000000, workers: ["w3"] },
  { id: "p7", name: "Commercial — Kopi Lokal", client: "Kopi Lokal Co.", service: "Videography", pkg: "Cinematic", status: "FINAL_DELIVERY", deadline: "30 Agu 2026", value: 14000000, paid: 7000000, workers: ["w2", "w5"] },
  { id: "p8", name: "Photo Wisuda — SMA Nusantara", client: "SMA Nusantara", service: "Photography", pkg: "Basic", status: "COMPLETED", deadline: "10 Agu 2026", value: 5000000, paid: 5000000, workers: ["w3"] },
];

export const INVOICES: Invoice[] = [
  { id: "inv1", num: "INV-2026-081", proj: "p1", label: "DP 50%", amt: 7500000, status: "PAID", due: "14 Agu 2026" },
  { id: "inv2", num: "INV-2026-092", proj: "p1", label: "Pelunasan", amt: 7500000, status: "ISSUED", due: "10 Sep 2026" },
  { id: "inv3", num: "INV-2026-083", proj: "p2", label: "DP 50%", amt: 11000000, status: "PAID", due: "10 Agu 2026" },
  { id: "inv4", num: "INV-2026-094", proj: "p2", label: "Pelunasan", amt: 11000000, status: "ISSUED", due: "03 Sep 2026" },
  { id: "inv5", num: "INV-2026-085", proj: "p3", label: "DP 50%", amt: 17500000, status: "PAID", due: "12 Agu 2026" },
  { id: "inv6", num: "INV-2026-096", proj: "p4", label: "DP 50%", amt: 4500000, status: "OVERDUE", due: "25 Agu 2026" },
  { id: "inv7", num: "INV-2026-097", proj: "p5", label: "Progress 50%", amt: 4500000, status: "ISSUED", due: "01 Sep 2026" },
  { id: "inv8", num: "INV-2026-088", proj: "p6", label: "Pelunasan", amt: 6000000, status: "PAID", due: "15 Agu 2026" },
];

export const TASKS: Task[] = [
  { id: "t1", proj: "p1", title: "Import Footage", ass: "w5", status: "DONE", due: "24 Agu" },
  { id: "t2", proj: "p1", title: "Rough Cut", ass: "w5", status: "DONE", due: "26 Agu" },
  { id: "t3", proj: "p1", title: "Sound Design", ass: "w5", status: "IN_PROGRESS", due: "Hari ini" },
  { id: "t4", proj: "p1", title: "Color Grade", ass: "w6", status: "TODO", due: "05 Sep" },
  { id: "t5", proj: "p1", title: "Motion Graphics", ass: "w7", status: "TODO", due: "07 Sep" },
  { id: "t6", proj: "p1", title: "Final Export", ass: "w5", status: "TODO", due: "10 Sep" },
  { id: "t7", proj: "p2", title: "Revision 01 — Color", ass: "w5", status: "IN_PROGRESS", due: "27 Agu", overdue: true },
  { id: "t8", proj: "p5", title: "Revision 02 — VFX cleanup", ass: "w7", status: "REVIEW", due: "29 Agu" },
  { id: "t9", proj: "p3", title: "Import & Sync Footage", ass: "w2", status: "TODO", due: "30 Agu" },
];

export const REVISIONS: Revision[] = [
  { id: "r1", proj: "p2", round: 1, status: "READY_FOR_CLIENT", ass: "w5", due: "29 Agu", items: [{ txt: "Perbaiki color scene garden", done: true }, { txt: "Trim intro lebih singkat", done: true }, { txt: "Ganti musik bridge", done: true }] },
  { id: "r2", proj: "p5", round: 2, status: "IN_PROGRESS", ass: "w7", due: "Hari ini", items: [{ txt: "Hapus rig di frame 214", done: true }, { txt: "Smooth transisi neon", done: true }, { txt: "Grade ulang scene akhir", done: false }, { txt: "Sync lyric line 2", done: false }] },
  { id: "r3", proj: "p1", round: 1, status: "REQUESTED", ass: "w5", due: "06 Sep", items: [{ txt: "Nama guru di 05:02 salah", done: false }, { txt: "Ganti shot aula dengan drone", done: false }] },
];

export const EVENTS: ScheduleEvent[] = [
  { day: 0, type: "MEETING", title: "Pre-Production Meeting — SMP 5", proj: "p4", time: "10:00" },
  { day: 1, type: "SHOOT", title: "Shooting Company Profile", proj: "p3", time: "08:00" },
  { day: 2, type: "EDITING", title: "Editing Block — Wedding Ayudia", proj: "p2", time: "13:00" },
  { day: 3, type: "REVIEW", title: "Client Review — Wedding Ayudia", proj: "p2", time: "16:00" },
  { day: 4, type: "EDITING", title: "Color Grade — Video Angkatan", proj: "p1", time: "09:00" },
  { day: 5, type: "SHOOT", title: "Shooting Music Video Senja", proj: "p5", time: "07:00" },
  { day: 6, type: "DELIVERY", title: "Final Delivery — Kopi Lokal", proj: "p7", time: "15:00" },
];

export const ACTIVITY: Activity[] = [
  { t: "14:02", who: "Admin", txt: "mengubah status project", detail: "POST_PRODUCTION → CLIENT_REVIEW", c: "orange" },
  { t: "13:46", who: "Fajar", txt: "mengunggah file", detail: "Wedding-V3.mp4", c: "yellow" },
  { t: "12:30", who: "Client", txt: "mengirim revisi", detail: "Revision #2 — Music Video Senja", c: "red" },
  { t: "10:15", who: "System", txt: "status pembayaran", detail: "PENDING → PAID (INV-2026-085)", c: "green" },
  { t: "09:12", who: "Dhea", txt: "mengirim quotation", detail: "QT-2026-013 → Glow Skincare", c: "blue" },
  { t: "08:40", who: "Rizky", txt: "mengisi availability", detail: "Sabtu: AVAILABLE", c: "teal" },
];

export const NOTIFICATIONS: Record<Role, Notification[]> = {
  admin: [
    { ico: "📥", txt: "Inquiry baru dari Universitas Pelita", time: "5 mnt lalu", unread: true },
    { ico: "🧾", txt: "QT-2026-013 dilihat oleh Glow Skincare", time: "32 mnt lalu", unread: true },
    { ico: "⚠️", txt: "Invoice INV-2026-096 jatuh tempo", time: "1 jam lalu", unread: true },
    { ico: "🎬", txt: "Shoot hari ini: Company Profile PT Aruna", time: "2 jam lalu", unread: false },
  ],
  worker: [
    { ico: "✅", txt: "Task \"Sound Design\" due hari ini", time: "10 mnt lalu", unread: true },
    { ico: "🔁", txt: "Revision #1 Video Angkatan ditugaskan", time: "1 jam lalu", unread: true },
    { ico: "🎬", txt: "Shoot Music Video — Sabtu 07:00", time: "3 jam lalu", unread: false },
  ],
  client: [
    { ico: "🎞️", txt: "Preview V2 Wedding Film siap direview", time: "20 mnt lalu", unread: true },
    { ico: "💳", txt: "Invoice pelunasan telah terbit", time: "1 jam lalu", unread: true },
    { ico: "📅", txt: "Reminder: color grade session Jumat", time: "4 jam lalu", unread: false },
  ],
};

// ---------- helpers ----------
export function rp(n: number): string {
  return "Rp " + n.toLocaleString("id-ID");
}

export function rps(n: number): string {
  return n >= 1e6
    ? "Rp " + (n / 1e6).toLocaleString("id-ID", { maximumFractionDigits: 1 }) + " jt"
    : "Rp " + (n / 1e3).toLocaleString("id-ID") + " rb";
}

export function pById(id: string): Project {
  return PROJECTS.find((p) => p.id === id)!;
}

export function wById(id: string): Worker {
  return WORKERS.find((w) => w.id === id)!;
}

const AVATAR_GRAD = ["g1", "g2", "g3", "g4", "g5"] as const;
export function avatarClass(i: number): string {
  return AVATAR_GRAD[i % AVATAR_GRAD.length];
}

export function initial(name: string): string {
  return name.charAt(0);
}

export const STAGES = ["PRE-PROD", "SHOOTING", "EDITING", "REVIEW", "FINAL"] as const;

const STAGE_MAP: Record<string, number> = {
  PRE_PRODUCTION: 0,
  PRODUCTION: 1,
  POST_PRODUCTION: 2,
  INTERNAL_REVIEW: 2,
  CLIENT_REVIEW: 3,
  REVISION: 3,
  FINAL_APPROVAL: 4,
  FINAL_DELIVERY: 4,
  COMPLETED: 5,
  ARCHIVED: 5,
};

export function stageIdx(status: string): number {
  const v = STAGE_MAP[status];
  return v === undefined ? 0 : v;
}

export function stepsHtml(idx: number): string {
  return STAGES.map((s, i) => {
    return `<span class="step ${i < idx ? "done" : i === idx ? "now" : ""}"><i></i>${s}</span>`;
  }).join("");
}
