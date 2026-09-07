"use client";

import { useEffect, useState } from "react";
import Link from "next/link";

interface BriefAttachment {
  id: string;
  original_name: string;
  filename: string;
  size_bytes: number;
  attachment_type: string;
}

interface OrderMessage {
  id: string;
  sender_user_id: string;
  message: string;
  created_at: string;
  sender?: {
    id: string;
    name: string;
    roles?: { name: string }[];
  };
}

interface AdminServiceOrder {
  id: string;
  order_number: string;
  status: string;
  review_type: string;
  source: string;
  created_at: string;
  client?: {
    id: string;
    name: string;
    email: string;
    phone: string;
  };
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
    id: string;
    category: string;
    event_name: string | null;
    event_date: string | null;
    venue_name: string | null;
    venue_count: number;
    city: string | null;
    is_outside_base_area: boolean;
    rundown_notes: string | null;
    wedding_couple_names: string | null;
    wedding_style_preference: string | null;
    wedding_must_have_moments: string | null;
    commercial_brand_name: string | null;
    commercial_deliverables_needed: string | null;
    custom_description: string | null;
    custom_budget_target: number | null;
    custom_reference_links: string | null;
    special_requests: string | null;
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
  attachments?: BriefAttachment[];
  messages?: OrderMessage[];
}

export default function AdminSalesOrdersPage() {
  const [orders, setOrders] = useState<AdminServiceOrder[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedOrder, setSelectedOrder] = useState<AdminServiceOrder | null>(null);
  const [filterTab, setFilterTab] = useState("ALL");
  const [searchQuery, setSearchQuery] = useState("");
  const [activeDrawerTab, setActiveDrawerTab] = useState<"BRIEF" | "MESSAGES" | "ACTIONS">("BRIEF");
  const [newMsg, setNewMsg] = useState("");
  const [sendingMsg, setSendingMsg] = useState(false);
  const [actionLoading, setActionLoading] = useState(false);

  // Form states for admin actions
  const [quotationIdInput, setQuotationIdInput] = useState("");
  const [quotationMsgInput, setQuotationMsgInput] = useState("");
  const [reqInfoMsgInput, setReqInfoMsgInput] = useState("");

  const loadOrders = async () => {
    try {
      const res = await fetch("/api/v1/admin/service-orders", {
        credentials: "include",
        headers: { Accept: "application/json" },
      });
      if (res.ok) {
        const json = await res.json();
        setOrders(json.data || []);
      }
    } catch (e) {
      console.error("Failed to load admin service orders:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadOrders();
  }, []);

  const refreshSelectedOrder = async (orderId: string) => {
    try {
      const res = await fetch(`/api/v1/admin/service-orders/${orderId}`, {
        credentials: "include",
        headers: { Accept: "application/json" },
      });
      if (res.ok) {
        const json = await res.json();
        setSelectedOrder(json.data);
      }
    } catch (e) {
      console.error(e);
    }
  };

  const handleSendMessage = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedOrder || !newMsg.trim()) return;

    setSendingMsg(true);
    try {
      const res = await fetch(`/api/v1/admin/service-orders/${selectedOrder.id}/messages`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({ message: newMsg }),
      });

      if (res.ok) {
        setNewMsg("");
        await refreshSelectedOrder(selectedOrder.id);
        await loadOrders();
      } else {
        alert("Gagal mengirim pesan.");
      }
    } catch (e) {
      console.error(e);
      alert("Terjadi kesalahan.");
    } finally {
      setSendingMsg(false);
    }
  };

  const handleAttachQuotation = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedOrder || !quotationIdInput.trim()) return;

    setActionLoading(true);
    try {
      const res = await fetch(`/api/v1/admin/service-orders/${selectedOrder.id}/attach-quotation`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          quotation_id: quotationIdInput,
          message: quotationMsgInput || undefined,
        }),
      });

      if (res.ok) {
        alert("Quotation berhasil dilampirkan ke pesanan client.");
        setQuotationIdInput("");
        setQuotationMsgInput("");
        await refreshSelectedOrder(selectedOrder.id);
        await loadOrders();
      } else {
        const err = await res.json();
        alert(err.message || "Gagal melampirkan quotation.");
      }
    } catch (e) {
      console.error(e);
      alert("Terjadi kesalahan.");
    } finally {
      setActionLoading(false);
    }
  };

  const handleRequestInformation = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedOrder || !reqInfoMsgInput.trim()) return;

    setActionLoading(true);
    try {
      const res = await fetch(`/api/v1/admin/service-orders/${selectedOrder.id}/request-information`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          message: reqInfoMsgInput,
        }),
      });

      if (res.ok) {
        alert("Permintaan informasi tambahan telah dikirim ke client.");
        setReqInfoMsgInput("");
        await refreshSelectedOrder(selectedOrder.id);
        await loadOrders();
      } else {
        const err = await res.json();
        alert(err.message || "Gagal meminta info tambahan.");
      }
    } catch (e) {
      console.error(e);
      alert("Terjadi kesalahan.");
    } finally {
      setActionLoading(false);
    }
  };

  const handleConfirmAvailability = async () => {
    if (!selectedOrder) return;
    if (!confirm("Konfirmasi ketersediaan jadwal slot produksi untuk tanggal ini?")) return;

    setActionLoading(true);
    try {
      const res = await fetch(`/api/v1/admin/service-orders/${selectedOrder.id}/confirm-availability`, {
        method: "POST",
        credentials: "include",
        headers: { Accept: "application/json" },
      });

      if (res.ok) {
        alert("Jadwal slot telah dikonfirmasi.");
        await refreshSelectedOrder(selectedOrder.id);
        await loadOrders();
      } else {
        const err = await res.json();
        alert(err.message || "Gagal konfirmasi ketersediaan.");
      }
    } catch (e) {
      console.error(e);
      alert("Terjadi kesalahan.");
    } finally {
      setActionLoading(false);
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "DRAFT":
        return { label: "Draft Brief", color: "bg-zinc-800 text-zinc-300 border-zinc-700" };
      case "SUBMITTED":
        return { label: "Perlu Review Admin", color: "bg-amber-950/40 text-amber-300 border-amber-500/40 font-semibold" };
      case "INFORMATION_REQUESTED":
        return { label: "Info Diminta", color: "bg-purple-950/40 text-purple-300 border-purple-500/30" };
      case "QUOTATION_SENT":
        return { label: "Quotation Terkirim", color: "bg-blue-950/40 text-blue-300 border-blue-500/30" };
      case "AWAITING_PAYMENT":
        return { label: "Menunggu DP", color: "bg-orange-950/40 text-orange-300 border-orange-500/30" };
      case "PAID":
        return { label: "DP Lunas", color: "bg-emerald-950/40 text-emerald-300 border-emerald-500/30" };
      case "PROJECT_CREATED":
        return { label: "Project Aktif", color: "bg-emerald-500/10 text-emerald-400 border-emerald-500/30 font-semibold" };
      default:
        return { label: status, color: "bg-zinc-800 text-zinc-300 border-zinc-700" };
    }
  };

  const filteredOrders = orders.filter((o) => {
    const matchesSearch =
      searchQuery === "" ||
      o.order_number.toLowerCase().includes(searchQuery.toLowerCase()) ||
      (o.client?.name && o.client.name.toLowerCase().includes(searchQuery.toLowerCase())) ||
      (o.brief?.event_name && o.brief.event_name.toLowerCase().includes(searchQuery.toLowerCase()));

    if (!matchesSearch) return false;

    if (filterTab === "ALL") return true;
    if (filterTab === "SUBMITTED") return ["SUBMITTED", "REVISION_REQUESTED"].includes(o.status);
    if (filterTab === "QUOTATION") return o.status === "QUOTATION_SENT";
    if (filterTab === "PAYMENT") return o.status === "AWAITING_PAYMENT";
    if (filterTab === "PROJECT") return ["PAID", "PROJECT_CREATED"].includes(o.status);
    return true;
  });

  return (
    <div className="space-y-8 pb-16">
      {/* Header */}
      <div className="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-800 pb-6">
        <div>
          <h1 className="text-3xl font-light text-white tracking-tight">
            Inbox Pesanan <span className="font-serif italic text-amber-200">Client Orders</span>
          </h1>
          <p className="text-sm text-zinc-400 mt-1">
            Manajemen self-service brief client, penerbitan surat penawaran (Quotation), dan persetujuan jadwal produksi.
          </p>
        </div>
      </div>

      {/* Filter and Search Bar */}
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div className="flex flex-wrap items-center gap-2">
          {[
            { key: "ALL", label: "Semua Orders" },
            { key: "SUBMITTED", label: "⚠️ Perlu Review" },
            { key: "QUOTATION", label: "Quotation Sent" },
            { key: "PAYMENT", label: "Menunggu DP" },
            { key: "PROJECT", label: "Project Aktif" },
          ].map((tab) => (
            <button
              key={tab.key}
              onClick={() => setFilterTab(tab.key)}
              className={`px-4 py-2 rounded-xl text-xs font-medium transition-all ${
                filterTab === tab.key
                  ? "bg-amber-400 text-zinc-950 font-semibold shadow-md shadow-amber-400/10"
                  : "bg-zinc-900/60 text-zinc-400 hover:text-white border border-zinc-800"
              }`}
            >
              {tab.label}
            </button>
          ))}
        </div>

        <input
          type="text"
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          placeholder="Cari order #, nama client, acara..."
          className="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-700/70 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-amber-400 w-64"
        />
      </div>

      {/* Orders Table */}
      {loading ? (
        <div className="py-24 text-center">
          <div className="w-8 h-8 border-2 border-amber-400 border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p className="text-zinc-500 text-sm">Memuat daftar pesanan masuk...</p>
        </div>
      ) : filteredOrders.length === 0 ? (
        <div className="py-20 text-center rounded-2xl border border-zinc-800/60 bg-zinc-900/30">
          <p className="text-zinc-400 text-sm">Tidak ada pesanan yang sesuai dengan filter.</p>
        </div>
      ) : (
        <div className="rounded-2xl border border-zinc-800 overflow-hidden bg-zinc-900/40">
          <table className="w-full text-left text-xs">
            <thead className="bg-zinc-900/80 text-zinc-400 border-b border-zinc-800">
              <tr>
                <th className="p-4">Order ID & Tanggal</th>
                <th className="p-4">Client</th>
                <th className="p-4">Layanan & Acara</th>
                <th className="p-4">Jalur & Status</th>
                <th className="p-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-zinc-800/60 text-zinc-300">
              {filteredOrders.map((ord) => {
                const badge = getStatusBadge(ord.status);
                return (
                  <tr key={ord.id} className="hover:bg-zinc-800/30 transition-colors">
                    <td className="p-4 space-y-0.5">
                      <span className="font-mono font-semibold text-amber-400 block">{ord.order_number}</span>
                      <span className="text-[11px] text-zinc-500">
                        {new Date(ord.created_at).toLocaleDateString("id-ID", { dateStyle: "short" })}
                      </span>
                    </td>
                    <td className="p-4 space-y-0.5">
                      <span className="font-medium text-white block">{ord.client?.name || "Client"}</span>
                      <span className="text-[11px] text-zinc-500">{ord.client?.phone || ord.client?.email || "-"}</span>
                    </td>
                    <td className="p-4 space-y-0.5">
                      <span className="font-medium text-white block">
                        {ord.brief?.event_name || ord.package?.name || ord.service?.name || "Order"}
                      </span>
                      <span className="text-[11px] text-zinc-400">
                        {ord.brief?.event_date
                          ? new Date(ord.brief.event_date).toLocaleDateString("id-ID", { dateStyle: "medium" })
                          : "Tanggal TBD"}{" "}
                        • {ord.brief?.city || "Surabaya"}
                      </span>
                    </td>
                    <td className="p-4 space-y-1">
                      <div className="flex items-center gap-2">
                        <span className={`px-2.5 py-0.5 rounded-full text-[10px] border ${badge.color}`}>
                          {badge.label}
                        </span>
                        <span className="text-[10px] text-zinc-500">
                          {ord.review_type === "AUTO_CHECKOUT" ? "⚡ Jalur A" : "🧭 Jalur B"}
                        </span>
                      </div>
                    </td>
                    <td className="p-4 text-right">
                      <button
                        onClick={() => {
                          setSelectedOrder(ord);
                          setActiveDrawerTab("BRIEF");
                        }}
                        className="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-zinc-800 hover:bg-zinc-700 text-amber-300 border border-zinc-700 transition-colors"
                      >
                        Review Order →
                      </button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      )}

      {/* Admin Order Drawer Modal */}
      {selectedOrder && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
          <div
            className="w-full max-w-3xl rounded-2xl border p-6 sm:p-8 space-y-6 max-h-[90vh] overflow-y-auto"
            style={{
              background: "linear-gradient(180deg, #18181c, #111114)",
              borderColor: "rgba(255, 255, 255, 0.12)",
            }}
          >
            {/* Header */}
            <div className="flex items-start justify-between border-b border-zinc-800 pb-4">
              <div>
                <div className="flex items-center gap-2">
                  <span className="font-mono text-sm font-semibold text-amber-400">
                    {selectedOrder.order_number}
                  </span>
                  <span className={`px-2 py-0.5 rounded-full text-[10px] border ${getStatusBadge(selectedOrder.status).color}`}>
                    {getStatusBadge(selectedOrder.status).label}
                  </span>
                </div>
                <h3 className="text-xl font-light text-white mt-1">
                  {selectedOrder.brief?.event_name || selectedOrder.package?.name || "Order Layanan"}
                </h3>
                <p className="text-xs text-zinc-400">
                  Client: <strong className="text-white">{selectedOrder.client?.name}</strong> • Phone: {selectedOrder.client?.phone || "-"}
                </p>
              </div>

              <button
                onClick={() => setSelectedOrder(null)}
                className="w-8 h-8 rounded-full bg-zinc-800 hover:bg-zinc-700 text-zinc-400 hover:text-white flex items-center justify-center text-sm"
              >
                ✕
              </button>
            </div>

            {/* Drawer Tabs */}
            <div className="flex items-center gap-2 border-b border-zinc-800 pb-2">
              <button
                onClick={() => setActiveDrawerTab("BRIEF")}
                className={`px-4 py-1.5 rounded-xl text-xs font-medium transition-all ${
                  activeDrawerTab === "BRIEF"
                    ? "bg-zinc-800 text-amber-300 font-semibold border border-amber-500/30"
                    : "text-zinc-400 hover:text-white"
                }`}
              >
                Rincian Brief & Moodboard
              </button>
              <button
                onClick={() => setActiveDrawerTab("ACTIONS")}
                className={`px-4 py-1.5 rounded-xl text-xs font-medium transition-all ${
                  activeDrawerTab === "ACTIONS"
                    ? "bg-zinc-800 text-amber-300 font-semibold border border-amber-500/30"
                    : "text-zinc-400 hover:text-white"
                }`}
              >
                Tindakan & Penawaran (Quotation) ⚡
              </button>
              <button
                onClick={() => setActiveDrawerTab("MESSAGES")}
                className={`px-4 py-1.5 rounded-xl text-xs font-medium transition-all flex items-center gap-1.5 ${
                  activeDrawerTab === "MESSAGES"
                    ? "bg-zinc-800 text-amber-300 font-semibold border border-amber-500/30"
                    : "text-zinc-400 hover:text-white"
                }`}
              >
                <span>Chat Client</span>
                {selectedOrder.messages && selectedOrder.messages.length > 0 && (
                  <span className="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-500/20 text-amber-300">
                    {selectedOrder.messages.length}
                  </span>
                )}
              </button>
            </div>

            {/* TAB: BRIEF */}
            {activeDrawerTab === "BRIEF" && (
              <div className="space-y-6 text-xs">
                <div className="grid grid-cols-2 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-zinc-900 border border-zinc-800">
                  <div>
                    <span className="text-zinc-500 block">Tanggal Acara:</span>
                    <span className="text-white font-medium">
                      {selectedOrder.brief?.event_date
                        ? new Date(selectedOrder.brief.event_date).toLocaleDateString("id-ID", { dateStyle: "long" })
                        : "-"}
                    </span>
                  </div>
                  <div>
                    <span className="text-zinc-500 block">Lokasi & Kota:</span>
                    <span className="text-white font-medium">
                      {selectedOrder.brief?.venue_name || "-"} ({selectedOrder.brief?.city || "-"})
                    </span>
                  </div>
                  <div>
                    <span className="text-zinc-500 block">Luar Kota Base Area:</span>
                    <span className="text-white font-medium">
                      {selectedOrder.brief?.is_outside_base_area ? "Ya (Luar Kota)" : "Tidak (Surabaya)"}
                    </span>
                  </div>
                </div>

                <div className="space-y-3 p-4 rounded-xl bg-zinc-900 border border-zinc-800">
                  <div>
                    <span className="text-zinc-500 block font-semibold">Preferensi Visual / Mood:</span>
                    <p className="text-zinc-200 mt-0.5">{selectedOrder.brief?.wedding_style_preference || "-"}</p>
                  </div>
                  {selectedOrder.brief?.wedding_must_have_moments && (
                    <div>
                      <span className="text-zinc-500 block font-semibold">Must-Have Moments:</span>
                      <p className="text-zinc-200 mt-0.5">{selectedOrder.brief.wedding_must_have_moments}</p>
                    </div>
                  )}
                  {selectedOrder.brief?.rundown_notes && (
                    <div>
                      <span className="text-zinc-500 block font-semibold">Catatan Rundown:</span>
                      <p className="text-zinc-200 mt-0.5">{selectedOrder.brief.rundown_notes}</p>
                    </div>
                  )}
                </div>

                {/* Attachments */}
                {selectedOrder.attachments && selectedOrder.attachments.length > 0 && (
                  <div className="space-y-2">
                    <span className="font-semibold text-zinc-400 uppercase tracking-wider block">
                      Lampiran File Brief ({selectedOrder.attachments.length})
                    </span>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                      {selectedOrder.attachments.map((att) => (
                        <div
                          key={att.id}
                          className="p-3 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-between text-xs"
                        >
                          <span className="truncate text-zinc-300">📄 {att.original_name}</span>
                          <span className="text-zinc-500 text-[10px]">
                            {(att.size_bytes / (1024 * 1024)).toFixed(1)} MB
                          </span>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            )}

            {/* TAB: ACTIONS */}
            {activeDrawerTab === "ACTIONS" && (
              <div className="space-y-6 text-xs">
                {/* Confirm availability */}
                <div className="p-4 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-between gap-4">
                  <div>
                    <h4 className="font-semibold text-white">Konfirmasi Ketersediaan Jadwal</h4>
                    <p className="text-zinc-400 mt-0.5">
                      Verifikasi slot crew untuk tanggal peliputan {selectedOrder.brief?.event_date || "ini"}.
                    </p>
                  </div>
                  <button
                    onClick={handleConfirmAvailability}
                    disabled={actionLoading}
                    className="px-4 py-2 rounded-xl font-semibold bg-emerald-500 text-zinc-950 hover:bg-emerald-400 transition-colors"
                  >
                    Konfirmasi Slot ✓
                  </button>
                </div>

                {/* Attach Quotation */}
                <form onSubmit={handleAttachQuotation} className="p-4 rounded-xl bg-zinc-900 border border-zinc-800 space-y-4">
                  <div>
                    <h4 className="font-semibold text-white">Lampirkan Surat Penawaran (Quotation)</h4>
                    <p className="text-zinc-400 mt-0.5">
                      Kaitkan draft / final quotation ID untuk pesanan ini agar client dapat meninjau rincian harga.
                    </p>
                  </div>

                  <div className="space-y-2">
                    <label className="text-zinc-400 block font-medium">ID Quotation (UUID atau Number) *</label>
                    <input
                      type="text"
                      value={quotationIdInput}
                      onChange={(e) => setQuotationIdInput(e.target.value)}
                      placeholder="Masukkan UUID atau nomor quotation..."
                      className="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-700 text-white focus:outline-none focus:border-amber-400"
                    />
                  </div>

                  <div className="space-y-2">
                    <label className="text-zinc-400 block font-medium">Pesan Pengantar untuk Client (Opsional)</label>
                    <textarea
                      value={quotationMsgInput}
                      onChange={(e) => setQuotationMsgInput(e.target.value)}
                      rows={2}
                      placeholder="Contoh: Halo Kak, ini penawaran penyesuaian biaya akomodasi crew luar kota..."
                      className="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-700 text-white focus:outline-none focus:border-amber-400"
                    />
                  </div>

                  <div className="flex justify-end">
                    <button
                      type="submit"
                      disabled={actionLoading || !quotationIdInput.trim()}
                      className="px-5 py-2.5 rounded-xl font-semibold bg-amber-400 text-zinc-950 hover:bg-amber-300 disabled:opacity-50 transition-colors"
                    >
                      {actionLoading ? "Memproses..." : "Terbitkan & Kirim ke Client 🚀"}
                    </button>
                  </div>
                </form>

                {/* Request Additional Info */}
                <form onSubmit={handleRequestInformation} className="p-4 rounded-xl bg-zinc-900 border border-zinc-800 space-y-4">
                  <div>
                    <h4 className="font-semibold text-white">Minta Informasi Tambahan dari Client</h4>
                    <p className="text-zinc-400 mt-0.5">
                      Kirimkan pertanyaan klarifikasi jika detail brief atau venue belum lengkap.
                    </p>
                  </div>

                  <div className="space-y-2">
                    <textarea
                      value={reqInfoMsgInput}
                      onChange={(e) => setReqInfoMsgInput(e.target.value)}
                      rows={2}
                      placeholder="Tuliskan bagian mana yang perlu dilengkapi client..."
                      className="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-700 text-white focus:outline-none focus:border-amber-400"
                    />
                  </div>

                  <div className="flex justify-end">
                    <button
                      type="submit"
                      disabled={actionLoading || !reqInfoMsgInput.trim()}
                      className="px-5 py-2.5 rounded-xl font-medium bg-zinc-800 hover:bg-zinc-700 text-purple-300 border border-purple-500/30 disabled:opacity-50 transition-colors"
                    >
                      Kirim Permintaan Klarifikasi
                    </button>
                  </div>
                </form>
              </div>
            )}

            {/* TAB: MESSAGES */}
            {activeDrawerTab === "MESSAGES" && (
              <div className="space-y-4 text-xs">
                <div className="space-y-3 max-h-80 overflow-y-auto pr-2">
                  {selectedOrder.messages && selectedOrder.messages.length > 0 ? (
                    selectedOrder.messages.map((m) => {
                      const isAdmin = m.sender?.roles?.some((r) => ["OWNER", "ADMIN"].includes(r.name));
                      return (
                        <div
                          key={m.id}
                          className={`p-3.5 rounded-xl border space-y-1 ${
                            isAdmin
                              ? "bg-amber-950/20 border-amber-500/30 ml-8 text-right"
                              : "bg-zinc-900 border-zinc-800 mr-8 text-left"
                          }`}
                        >
                          <div className={`flex items-center gap-2 ${isAdmin ? "justify-end" : ""}`}>
                            <span className="font-semibold text-white">{m.sender?.name || "User"}</span>
                            <span className="text-zinc-500 text-[10px]">
                              {new Date(m.created_at).toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" })}
                            </span>
                          </div>
                          <p className="text-zinc-300 whitespace-pre-line text-left">{m.message}</p>
                        </div>
                      );
                    })
                  ) : (
                    <div className="py-8 text-center text-zinc-500">Belum ada pesan diskusi.</div>
                  )}
                </div>

                <form onSubmit={handleSendMessage} className="pt-3 border-t border-zinc-800 flex gap-2">
                  <input
                    type="text"
                    value={newMsg}
                    onChange={(e) => setNewMsg(e.target.value)}
                    placeholder="Balas pesan client..."
                    className="flex-1 px-4 py-2.5 rounded-xl bg-zinc-900 border border-zinc-700 text-white focus:outline-none focus:border-amber-400"
                  />
                  <button
                    type="submit"
                    disabled={sendingMsg || !newMsg.trim()}
                    className="px-4 py-2.5 rounded-xl font-semibold bg-amber-400 text-zinc-950 hover:bg-amber-300 disabled:opacity-50"
                  >
                    Kirim
                  </button>
                </form>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
