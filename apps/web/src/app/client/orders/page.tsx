"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";

interface ServiceOrderListItem {
  id: string;
  order_number: string;
  status: string;
  review_type: string;
  source: string;
  created_at: string;
  package?: {
    id: string;
    name: string;
    price: number | null;
  };
  service?: {
    id: string;
    name: string;
    category: string;
  };
  brief?: {
    event_name: string | null;
    event_date: string | null;
    venue_name: string | null;
    city: string | null;
  };
  quotation?: {
    id: string;
    quotation_number: string;
    status: string;
  };
  invoice?: {
    id: string;
    invoice_number: string;
    status: string;
    amount: number;
  };
  project?: {
    id: string;
    project_number: string;
    name: string;
  };
}

export default function ClientOrdersListPage() {
  const router = useRouter();
  const [orders, setOrders] = useState<ServiceOrderListItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [filterTab, setFilterTab] = useState("ALL");

  useEffect(() => {
    async function loadOrders() {
      try {
        const res = await fetch("/api/v1/client/service-orders", {
          credentials: "include",
          headers: { Accept: "application/json" },
        });
        if (res.ok) {
          const json = await res.json();
          setOrders(json.data || []);
        }
      } catch (e) {
        console.error("Failed to load orders:", e);
      } finally {
        setLoading(false);
      }
    }
    loadOrders();
  }, []);

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "DRAFT":
        return { label: "Draft / Lengkapi Brief", color: "bg-zinc-800 text-zinc-300 border-zinc-700" };
      case "SUBMITTED":
        return { label: "Menunggu Review Admin", color: "bg-amber-950/40 text-amber-300 border-amber-500/30" };
      case "INFORMATION_REQUESTED":
        return { label: "Perlu Info Tambahan", color: "bg-purple-950/40 text-purple-300 border-purple-500/30" };
      case "QUOTATION_SENT":
        return { label: "Surat Penawaran Diterbitkan", color: "bg-blue-950/40 text-blue-300 border-blue-500/30" };
      case "AWAITING_PAYMENT":
        return { label: "Menunggu Pembayaran DP", color: "bg-orange-950/40 text-orange-300 border-orange-500/30" };
      case "PAID":
        return { label: "DP Lunas", color: "bg-emerald-950/40 text-emerald-300 border-emerald-500/30" };
      case "PROJECT_CREATED":
        return { label: "Project Aktif", color: "bg-emerald-500/10 text-emerald-400 border-emerald-500/30 font-semibold" };
      case "REVISION_REQUESTED":
        return { label: "Revisi Penawaran", color: "bg-amber-950/40 text-amber-300 border-amber-500/30" };
      case "DECLINED":
      case "CANCELLED":
        return { label: "Dibatalkan", color: "bg-red-950/40 text-red-400 border-red-500/30" };
      default:
        return { label: status, color: "bg-zinc-800 text-zinc-300 border-zinc-700" };
    }
  };

  const filteredOrders = orders.filter((o) => {
    if (filterTab === "ALL") return true;
    if (filterTab === "DRAFT") return o.status === "DRAFT";
    if (filterTab === "REVIEW") return ["SUBMITTED", "INFORMATION_REQUESTED", "QUOTATION_SENT", "REVISION_REQUESTED"].includes(o.status);
    if (filterTab === "PAYMENT") return o.status === "AWAITING_PAYMENT";
    if (filterTab === "ACTIVE") return ["PAID", "PROJECT_CREATED"].includes(o.status);
    return true;
  });

  return (
    <div className="space-y-8 pb-16">
      {/* Header */}
      <div className="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-800 pb-6">
        <div>
          <h1 className="text-3xl font-light text-white tracking-tight">
            Pesanan <span className="font-serif italic text-amber-200">Layanan Saya</span>
          </h1>
          <p className="text-sm text-zinc-400 mt-1">
            Pantau status brief, penawaran resmi, pembayaran DP, hingga aktivasi jadwal produksi sinematik Anda.
          </p>
        </div>

        <Link
          href="/client/catalog"
          className="px-5 py-2.5 rounded-xl text-xs font-semibold bg-amber-400 text-zinc-950 hover:bg-amber-300 transition-all shadow-md shadow-amber-400/10 flex items-center gap-2"
        >
          <span>✨</span> Pesan Layanan Baru
        </Link>
      </div>

      {/* Tabs Filter */}
      <div className="flex flex-wrap items-center gap-2 border-b border-zinc-800/80 pb-3">
        {[
          { key: "ALL", label: "Semua Pesanan" },
          { key: "DRAFT", label: "Draft Brief" },
          { key: "REVIEW", label: "Proses Review & Penawaran" },
          { key: "PAYMENT", label: "Menunggu Bayar DP" },
          { key: "ACTIVE", label: "Project Aktif" },
        ].map((tab) => (
          <button
            key={tab.key}
            onClick={() => setFilterTab(tab.key)}
            className={`px-4 py-2 rounded-xl text-xs font-medium transition-all ${
              filterTab === tab.key
                ? "bg-zinc-800 text-amber-300 font-semibold border border-amber-500/30"
                : "bg-zinc-900/40 text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800/60 border border-zinc-800/60"
            }`}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {/* Orders List */}
      {loading ? (
        <div className="py-24 text-center">
          <div className="w-8 h-8 border-2 border-amber-400 border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p className="text-zinc-500 text-sm">Memuat daftar pesanan...</p>
        </div>
      ) : filteredOrders.length === 0 ? (
        <div className="py-20 text-center rounded-2xl border border-zinc-800/60 bg-zinc-900/30 space-y-4">
          <p className="text-zinc-400 text-sm">Belum ada pesanan pada kategori ini.</p>
          <Link
            href="/client/catalog"
            className="inline-block px-4 py-2 rounded-xl text-xs font-medium bg-zinc-800 hover:bg-zinc-700 text-amber-300"
          >
            Buka Katalog Layanan →
          </Link>
        </div>
      ) : (
        <div className="space-y-4">
          {filteredOrders.map((ord) => {
            const badge = getStatusBadge(ord.status);
            return (
              <div
                key={ord.id}
                className="p-6 rounded-2xl border transition-all duration-200 hover:border-zinc-700 hover:bg-zinc-900/70 space-y-4"
                style={{
                  background: "linear-gradient(180deg, rgba(22, 22, 26, 0.8), rgba(14, 14, 18, 0.9))",
                  borderColor: "rgba(255, 255, 255, 0.08)",
                }}
              >
                <div className="flex flex-wrap items-start justify-between gap-4">
                  <div className="space-y-1">
                    <div className="flex items-center gap-3">
                      <span className="text-xs font-mono font-semibold text-amber-400">
                        {ord.order_number}
                      </span>
                      <span className={`px-2.5 py-0.5 rounded-full text-[11px] border ${badge.color}`}>
                        {badge.label}
                      </span>
                      {ord.source === "REORDER" && (
                        <span className="px-2 py-0.5 rounded-full text-[10px] bg-purple-500/10 text-purple-300 border border-purple-500/20">
                          Re-order
                        </span>
                      )}
                    </div>
                    <h3 className="text-lg font-medium text-white">
                      {ord.brief?.event_name || ord.package?.name || ord.service?.name || "Order Layanan"}
                    </h3>
                  </div>

                  <div className="text-right text-xs text-zinc-400">
                    <p>Dibuat pada: {new Date(ord.created_at).toLocaleDateString("id-ID", { dateStyle: "medium" })}</p>
                  </div>
                </div>

                {/* Info grid */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-xl bg-zinc-900/50 border border-zinc-800/60 text-xs text-zinc-300">
                  <div>
                    <span className="text-zinc-500 block">Paket:</span>
                    <span className="font-medium text-white">{ord.package?.name || ord.service?.name || "Kustom Story"}</span>
                  </div>
                  <div>
                    <span className="text-zinc-500 block">Tanggal Acara:</span>
                    <span className="font-medium text-white">
                      {ord.brief?.event_date
                        ? new Date(ord.brief.event_date).toLocaleDateString("id-ID", { dateStyle: "long" })
                        : "Belum ditentukan"}
                    </span>
                  </div>
                  <div>
                    <span className="text-zinc-500 block">Lokasi Venue:</span>
                    <span className="font-medium text-white">
                      {ord.brief?.venue_name ? `${ord.brief.venue_name} (${ord.brief.city})` : "Belum diisi"}
                    </span>
                  </div>
                </div>

                {/* Actions Footer */}
                <div className="flex flex-wrap items-center justify-between gap-3 pt-2">
                  <div className="text-xs text-zinc-400">
                    {ord.status === "AWAITING_PAYMENT" && (
                      <span className="text-amber-300 font-medium">⚡ DP Invoice siap dibayar</span>
                    )}
                    {ord.status === "QUOTATION_SENT" && (
                      <span className="text-blue-300 font-medium">📄 Penawaran resmi menunggu persetujuan Anda</span>
                    )}
                    {ord.project && (
                      <span className="text-emerald-400 font-medium">🎬 Project #{ord.project.project_number} sedang berjalan</span>
                    )}
                  </div>

                  <div className="flex items-center gap-3">
                    {ord.status === "DRAFT" ? (
                      <button
                        onClick={() => router.push(`/client/orders/${ord.id}/brief`)}
                        className="px-4 py-2 rounded-xl text-xs font-semibold bg-amber-400 text-zinc-950 hover:bg-amber-300 transition-colors"
                      >
                        Lanjutkan Isi Brief →
                      </button>
                    ) : (
                      <button
                        onClick={() => router.push(`/client/orders/${ord.id}`)}
                        className="px-4 py-2 rounded-xl text-xs font-medium bg-zinc-800 hover:bg-zinc-700 text-white border border-zinc-700 transition-colors"
                      >
                        Buka Order Hub & Detail →
                      </button>
                    )}

                    {ord.project && (
                      <Link
                        href={`/client/projects/${ord.project.id}`}
                        className="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-500 text-zinc-950 hover:bg-emerald-400 transition-colors"
                      >
                        Lihat Project 🎬
                      </Link>
                    )}
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
