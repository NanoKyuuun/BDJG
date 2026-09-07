"use client";

import { useEffect, useState, use } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";

interface OrderMessage {
  id: string;
  sender_user_id: string;
  message: string;
  is_internal_note: boolean;
  created_at: string;
  sender?: {
    id: string;
    name: string;
    roles?: { name: string }[];
  };
}

interface QuotationItem {
  id: string;
  name: string;
  quantity: number;
  unit_price: number;
  total_price: number;
  item_type: string;
}

interface QuotationVersion {
  id: string;
  version_number: number;
  project_name: string;
  subtotal: number;
  grand_total: number;
  terms?: string;
  items?: QuotationItem[];
}

interface ServiceOrderDetail {
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
    id: string;
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
    special_requests: string | null;
  };
  quotation?: {
    id: string;
    quotation_number: string;
    status: string;
    valid_until: string;
    current_version?: QuotationVersion;
    accepted_version?: QuotationVersion;
  };
  invoice?: {
    id: string;
    invoice_number: string;
    status: string;
    invoice_type: string;
    amount: number;
    paid_amount: number;
    due_date: string;
  };
  project?: {
    id: string;
    project_number: string;
    name: string;
    status: string;
    shoot_date: string;
  };
  messages?: OrderMessage[];
}

export default function ClientOrderDetailHubPage({
  params: paramsPromise,
}: {
  params: Promise<{ id: string }>;
}) {
  const params = use(paramsPromise);
  const router = useRouter();
  const [order, setOrder] = useState<ServiceOrderDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState<"OVERVIEW" | "MESSAGES" | "QUOTATION" | "INVOICE">("OVERVIEW");
  const [newMessage, setNewMessage] = useState("");
  const [sendingMsg, setSendingMsg] = useState(false);
  const [actionLoading, setActionLoading] = useState(false);
  const [payingLoading, setPayingLoading] = useState(false);

  const loadOrderDetail = async () => {
    try {
      const res = await fetch(`/api/v1/client/service-orders/${params.id}`, {
        credentials: "include",
        headers: { Accept: "application/json" },
      });
      if (res.ok) {
        const json = await res.json();
        setOrder(json.data);
      } else {
        router.push("/client/orders");
      }
    } catch (e) {
      console.error("Failed to load order:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadOrderDetail();
  }, [params.id]);

  const handleSendMessage = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newMessage.trim()) return;

    setSendingMsg(true);
    try {
      const res = await fetch(`/api/v1/client/service-orders/${params.id}/messages`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({ message: newMessage }),
      });

      if (res.ok) {
        setNewMessage("");
        await loadOrderDetail();
      } else {
        alert("Gagal mengirim pesan.");
      }
    } catch (err) {
      console.error(err);
      alert("Terjadi kesalahan.");
    } finally {
      setSendingMsg(false);
    }
  };

  const handleAcceptQuotation = async () => {
    if (!confirm("Apakah Anda yakin ingin menyetujui surat penawaran ini? Invoice DP akan diterbitkan secara otomatis.")) return;
    setActionLoading(true);
    try {
      const res = await fetch(`/api/v1/client/service-orders/${params.id}/accept-quotation`, {
        method: "POST",
        credentials: "include",
        headers: { Accept: "application/json" },
      });
      if (res.ok) {
        await loadOrderDetail();
      } else {
        const err = await res.json();
        alert(err.message || "Gagal menyetujui penawaran.");
      }
    } catch (e) {
      console.error(e);
      alert("Terjadi kesalahan.");
    } finally {
      setActionLoading(false);
    }
  };

  const handleRequestRevision = async () => {
    const notes = prompt("Masukkan catatan bagian mana dari penawaran yang ingin Anda sesuaikan:");
    if (!notes) return;

    setActionLoading(true);
    try {
      const res = await fetch(`/api/v1/client/service-orders/${params.id}/request-quotation-revision`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({ notes }),
      });
      if (res.ok) {
        alert("Permintaan revisi telah dikirim ke tim admin.");
        await loadOrderDetail();
      } else {
        const err = await res.json();
        alert(err.message || "Gagal mengirim permintaan revisi.");
      }
    } catch (e) {
      console.error(e);
      alert("Terjadi kesalahan.");
    } finally {
      setActionLoading(false);
    }
  };

  const handlePayInvoice = async () => {
    if (!order?.invoice) return;
    setPayingLoading(true);
    try {
      const res = await fetch(`/api/v1/client/invoices/${order.invoice.id}/pay`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          payment_method: "BCA_VA",
        }),
      });

      if (res.ok) {
        const data = await res.json();
        const paymentUrl = data?.data?.payment_url;
        if (paymentUrl) {
          window.open(paymentUrl, "_blank");
        } else {
          alert("Pembayaran diproses. Periksa status transaksi Anda.");
        }
      } else {
        const err = await res.json();
        alert(err.message || "Gagal memproses pembayaran Duitku.");
      }
    } catch (e) {
      console.error(e);
      alert("Terjadi kesalahan saat memproses pembayaran.");
    } finally {
      setPayingLoading(false);
    }
  };

  const formatRupiah = (val: number | null | undefined) => {
    if (!val) return "Rp 0";
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      maximumFractionDigits: 0,
    }).format(val);
  };

  if (loading || !order) {
    return (
      <div className="py-24 text-center">
        <div className="w-8 h-8 border-2 border-amber-400 border-t-transparent rounded-full animate-spin mx-auto mb-3" />
        <p className="text-zinc-500 text-sm">Memuat Order Hub...</p>
      </div>
    );
  }

  // Steps Progress State
  const isStep1Done = true;
  const isStep2Done = ["SUBMITTED", "QUOTATION_SENT", "AWAITING_PAYMENT", "PAID", "PROJECT_CREATED"].includes(order.status);
  const isStep3Done = ["PAID", "PROJECT_CREATED"].includes(order.status);
  const isStep4Done = order.status === "PROJECT_CREATED";

  const activeQuotationVersion = order.quotation?.accepted_version || order.quotation?.current_version;

  return (
    <div className="max-w-5xl mx-auto space-y-8 pb-20">
      {/* Top Breadcrumb & Actions */}
      <div className="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-800 pb-6">
        <div>
          <div className="flex items-center gap-2">
            <Link href="/client/orders" className="text-xs text-zinc-400 hover:text-white transition-colors">
              ← Kembali ke Pesanan Saya
            </Link>
            <span className="text-zinc-600">/</span>
            <span className="text-xs font-mono text-amber-400">{order.order_number}</span>
          </div>
          <h1 className="text-2xl sm:text-3xl font-light text-white mt-1">
            Order Hub: <span className="font-medium text-amber-200">{order.brief?.event_name || order.package?.name || "Order Layanan"}</span>
          </h1>
        </div>

        {order.status === "DRAFT" && (
          <Link
            href={`/client/orders/${order.id}/brief`}
            className="px-5 py-2.5 rounded-xl text-xs font-semibold bg-amber-400 text-zinc-950 hover:bg-amber-300 transition-all shadow-md"
          >
            Lanjutkan Brief →
          </Link>
        )}

        {order.project && (
          <Link
            href={`/client/projects/${order.project.id}`}
            className="px-5 py-2.5 rounded-xl text-xs font-semibold bg-emerald-500 text-zinc-950 hover:bg-emerald-400 transition-all shadow-md shadow-emerald-500/10 flex items-center gap-2"
          >
            <span>🎬</span> Buka Project Terjadwal
          </Link>
        )}
      </div>

      {/* Progress Timeline Stepper */}
      <div
        className="p-6 rounded-2xl border space-y-4"
        style={{
          background: "linear-gradient(180deg, rgba(24, 24, 28, 0.7), rgba(16, 16, 20, 0.9))",
          borderColor: "rgba(255, 255, 255, 0.08)",
        }}
      >
        <h3 className="text-xs font-semibold text-zinc-400 uppercase tracking-wider">
          Alur & Status Produksi
        </h3>
        <div className="grid grid-cols-1 sm:grid-cols-4 gap-4 pt-2">
          {[
            { step: "1", title: "Brief Diterima", done: isStep1Done, active: order.status === "DRAFT" },
            {
              step: "2",
              title: order.review_type === "AUTO_CHECKOUT" ? "Auto-Checkout" : "Review & Penawaran",
              done: isStep2Done,
              active: ["SUBMITTED", "QUOTATION_SENT"].includes(order.status),
            },
            { step: "3", title: "Pembayaran DP", done: isStep3Done, active: order.status === "AWAITING_PAYMENT" },
            { step: "4", title: "Aktivasi Project & Crew", done: isStep4Done, active: order.status === "PROJECT_CREATED" },
          ].map((s, idx) => (
            <div
              key={idx}
              className={`p-3.5 rounded-xl border flex items-center gap-3 ${
                s.done
                  ? "bg-emerald-950/30 border-emerald-500/40 text-emerald-300"
                  : s.active
                  ? "bg-amber-950/30 border-amber-500/40 text-amber-300 animate-pulse"
                  : "bg-zinc-900/40 border-zinc-800 text-zinc-500"
              }`}
            >
              <div
                className={`w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold ${
                  s.done ? "bg-emerald-500 text-zinc-950" : s.active ? "bg-amber-400 text-zinc-950" : "bg-zinc-800 text-zinc-500"
                }`}
              >
                {s.done ? "✓" : s.step}
              </div>
              <span className="text-xs font-medium">{s.title}</span>
            </div>
          ))}
        </div>
      </div>

      {/* Navigation Tabs */}
      <div className="flex flex-wrap items-center gap-2 border-b border-zinc-800 pb-2">
        <button
          onClick={() => setActiveTab("OVERVIEW")}
          className={`px-4 py-2 rounded-xl text-xs font-medium transition-all ${
            activeTab === "OVERVIEW"
              ? "bg-zinc-800 text-amber-300 font-semibold border border-amber-500/30"
              : "text-zinc-400 hover:text-white"
          }`}
        >
          Ringkasan Brief & Info
        </button>

        {order.quotation && (
          <button
            onClick={() => setActiveTab("QUOTATION")}
            className={`px-4 py-2 rounded-xl text-xs font-medium transition-all ${
              activeTab === "QUOTATION"
                ? "bg-zinc-800 text-amber-300 font-semibold border border-amber-500/30"
                : "text-zinc-400 hover:text-white"
            }`}
          >
            Surat Penawaran (Quotation) 📄
          </button>
        )}

        {order.invoice && (
          <button
            onClick={() => setActiveTab("INVOICE")}
            className={`px-4 py-2 rounded-xl text-xs font-medium transition-all ${
              activeTab === "INVOICE"
                ? "bg-zinc-800 text-amber-300 font-semibold border border-amber-500/30"
                : "text-zinc-400 hover:text-white"
            }`}
          >
            Invoice & Pembayaran DP 💳
          </button>
        )}

        <button
          onClick={() => setActiveTab("MESSAGES")}
          className={`px-4 py-2 rounded-xl text-xs font-medium transition-all flex items-center gap-1.5 ${
            activeTab === "MESSAGES"
              ? "bg-zinc-800 text-amber-300 font-semibold border border-amber-500/30"
              : "text-zinc-400 hover:text-white"
          }`}
        >
          <span>Diskusi & Catatan</span>
          {order.messages && order.messages.length > 0 && (
            <span className="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-500/20 text-amber-300">
              {order.messages.length}
            </span>
          )}
        </button>
      </div>

      {/* TAB 1: OVERVIEW */}
      {activeTab === "OVERVIEW" && (
        <div className="space-y-6">
          {/* Action Callout depending on status */}
          {order.status === "QUOTATION_SENT" && (
            <div className="p-5 rounded-2xl border border-blue-500/40 bg-blue-950/20 flex flex-wrap items-center justify-between gap-4">
              <div className="space-y-1 text-xs">
                <span className="font-semibold text-blue-300 uppercase tracking-wider block">
                  📄 Penawaran Resmi Telah Diterbitkan
                </span>
                <p className="text-zinc-300">
                  Tim BDJG telah menyusun rincian penawaran sesuai dengan brief Anda. Silakan review dan setujui untuk melanjutkan ke pembayaran DP.
                </p>
              </div>
              <button
                onClick={() => setActiveTab("QUOTATION")}
                className="px-5 py-2.5 rounded-xl text-xs font-semibold bg-blue-500 text-white hover:bg-blue-400 transition-colors shadow-md"
              >
                Review Penawaran Sekarang →
              </button>
            </div>
          )}

          {order.status === "AWAITING_PAYMENT" && order.invoice && (
            <div className="p-5 rounded-2xl border border-amber-500/40 bg-amber-950/20 flex flex-wrap items-center justify-between gap-4">
              <div className="space-y-1 text-xs">
                <span className="font-semibold text-amber-300 uppercase tracking-wider block">
                  ⚡ Menunggu Pembayaran Uang Muka (DP 30%)
                </span>
                <p className="text-zinc-300">
                  Jumlah DP yang perlu dibayarkan: <strong className="text-white text-sm">{formatRupiah(order.invoice.amount)}</strong>. Pembayaran terverifikasi otomatis melalui Duitku.
                </p>
              </div>
              <button
                onClick={handlePayInvoice}
                disabled={payingLoading}
                className="px-6 py-2.5 rounded-xl text-xs font-semibold bg-gradient-to-r from-amber-400 to-amber-300 text-zinc-950 hover:from-amber-300 hover:to-amber-200 transition-all shadow-md shadow-amber-400/20"
              >
                {payingLoading ? "Membuka Gateway..." : "💳 Bayar DP Sekarang"}
              </button>
            </div>
          )}

          {/* Details Card */}
          <div
            className="p-6 sm:p-8 rounded-2xl border space-y-6"
            style={{
              background: "linear-gradient(180deg, rgba(24, 24, 28, 0.7), rgba(16, 16, 20, 0.9))",
              borderColor: "rgba(255, 255, 255, 0.08)",
            }}
          >
            <h3 className="text-base font-medium text-white">Rincian Brief Project</h3>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-6 text-xs border-b border-zinc-800/80 pb-6">
              <div>
                <span className="text-zinc-500 block">Nama Acara:</span>
                <span className="text-white font-medium text-sm mt-0.5">{order.brief?.event_name || "-"}</span>
              </div>
              <div>
                <span className="text-zinc-500 block">Tanggal Peliputan:</span>
                <span className="text-white font-medium text-sm mt-0.5">
                  {order.brief?.event_date
                    ? new Date(order.brief.event_date).toLocaleDateString("id-ID", { dateStyle: "full" })
                    : "Belum ditentukan"}
                </span>
              </div>
              <div>
                <span className="text-zinc-500 block">Paket Terpilih:</span>
                <span className="text-amber-300 font-medium text-sm mt-0.5">
                  {order.package?.name || order.service?.name || "Kustom Story"}
                </span>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-6 text-xs border-b border-zinc-800/80 pb-6">
              <div>
                <span className="text-zinc-500 block">Lokasi Venue:</span>
                <span className="text-white font-medium mt-0.5">
                  {order.brief?.venue_name || "-"} ({order.brief?.city || "-"})
                </span>
              </div>
              <div>
                <span className="text-zinc-500 block">Area Pelaksanaan:</span>
                <span className="text-zinc-200 mt-0.5">
                  {order.brief?.is_outside_base_area ? "Luar Kota Surabaya" : "Dalam Kota Surabaya"}
                </span>
              </div>
              <div>
                <span className="text-zinc-500 block">Jumlah Titik Lokasi:</span>
                <span className="text-zinc-200 mt-0.5">{order.brief?.venue_count || 1} Lokasi</span>
              </div>
            </div>

            <div className="space-y-4 text-xs">
              <div>
                <span className="text-zinc-500 block">Preferensi Gaya & Konsep:</span>
                <p className="text-zinc-200 mt-1 leading-relaxed">
                  {order.brief?.wedding_style_preference || "Standar Gaya Sinematik BDJG"}
                </p>
              </div>

              {order.brief?.wedding_must_have_moments && (
                <div>
                  <span className="text-zinc-500 block">Momen Wajib (Must-have Moments):</span>
                  <p className="text-zinc-200 mt-1 leading-relaxed">{order.brief.wedding_must_have_moments}</p>
                </div>
              )}

              {order.brief?.rundown_notes && (
                <div>
                  <span className="text-zinc-500 block">Catatan Jadwal / Rundown:</span>
                  <p className="text-zinc-200 mt-1 leading-relaxed">{order.brief.rundown_notes}</p>
                </div>
              )}
            </div>
          </div>
        </div>
      )}

      {/* TAB 2: QUOTATION */}
      {activeTab === "QUOTATION" && order.quotation && (
        <div
          className="p-6 sm:p-8 rounded-2xl border space-y-6"
          style={{
            background: "linear-gradient(180deg, rgba(24, 24, 28, 0.7), rgba(16, 16, 20, 0.9))",
            borderColor: "rgba(255, 255, 255, 0.08)",
          }}
        >
          <div className="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-800 pb-4">
            <div>
              <span className="text-xs font-mono text-amber-400">
                {order.quotation.quotation_number} (v{activeQuotationVersion?.version_number || 1})
              </span>
              <h3 className="text-xl font-medium text-white mt-0.5">
                {activeQuotationVersion?.project_name || "Surat Penawaran Resmi"}
              </h3>
            </div>

            <div className="text-right text-xs">
              <span className="text-zinc-500 block">Berlaku Hingga:</span>
              <span className="text-white font-medium">
                {new Date(order.quotation.valid_until).toLocaleDateString("id-ID", { dateStyle: "medium" })}
              </span>
            </div>
          </div>

          {/* Items breakdown table */}
          {activeQuotationVersion?.items && activeQuotationVersion.items.length > 0 && (
            <div className="space-y-3">
              <h4 className="text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                Rincian Layanan & Deliverables
              </h4>
              <div className="rounded-xl border border-zinc-800 overflow-hidden">
                <table className="w-full text-left text-xs">
                  <thead className="bg-zinc-900/80 text-zinc-400 border-b border-zinc-800">
                    <tr>
                      <th className="p-3">Item Layanan</th>
                      <th className="p-3 text-center">Qty</th>
                      <th className="p-3 text-right">Harga Satuan</th>
                      <th className="p-3 text-right">Total</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-zinc-800/60 text-zinc-200">
                    {activeQuotationVersion.items.map((it) => (
                      <tr key={it.id}>
                        <td className="p-3 font-medium">{it.name}</td>
                        <td className="p-3 text-center">{it.quantity}</td>
                        <td className="p-3 text-right">{formatRupiah(it.unit_price)}</td>
                        <td className="p-3 text-right font-semibold">{formatRupiah(it.total_price)}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {/* Grand Total */}
          <div className="p-4 rounded-xl bg-zinc-900 border border-zinc-800 flex justify-between items-center text-sm">
            <span className="font-medium text-zinc-300">Total Nilai Kontrak:</span>
            <span className="text-xl font-bold text-amber-300">
              {formatRupiah(activeQuotationVersion?.grand_total)}
            </span>
          </div>

          {/* Terms */}
          {activeQuotationVersion?.terms && (
            <div className="space-y-1.5 text-xs text-zinc-400">
              <span className="font-semibold text-zinc-300 uppercase tracking-wider block">Ketentuan Layanan:</span>
              <p className="whitespace-pre-line leading-relaxed">{activeQuotationVersion.terms}</p>
            </div>
          )}

          {/* Quotation Action Buttons */}
          {order.status === "QUOTATION_SENT" && (
            <div className="pt-4 border-t border-zinc-800 flex flex-wrap items-center justify-end gap-3">
              <button
                onClick={handleRequestRevision}
                disabled={actionLoading}
                className="px-4 py-2.5 rounded-xl text-xs font-medium bg-zinc-900 hover:bg-zinc-800 text-amber-300 border border-amber-500/30 transition-colors"
              >
                🔁 Minta Penyesuaian / Revisi
              </button>
              <button
                onClick={handleAcceptQuotation}
                disabled={actionLoading}
                className="px-6 py-2.5 rounded-xl text-xs font-semibold bg-gradient-to-r from-emerald-500 to-emerald-400 text-zinc-950 hover:from-emerald-400 hover:to-emerald-300 transition-all shadow-md shadow-emerald-500/20"
              >
                {actionLoading ? "Memproses..." : "✓ Setujui Penawaran & Lanjut ke DP"}
              </button>
            </div>
          )}
        </div>
      )}

      {/* TAB 3: INVOICE */}
      {activeTab === "INVOICE" && order.invoice && (
        <div
          className="p-6 sm:p-8 rounded-2xl border space-y-6"
          style={{
            background: "linear-gradient(180deg, rgba(24, 24, 28, 0.7), rgba(16, 16, 20, 0.9))",
            borderColor: "rgba(255, 255, 255, 0.08)",
          }}
        >
          <div className="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-800 pb-4">
            <div>
              <span className="text-xs font-mono text-amber-400">{order.invoice.invoice_number}</span>
              <h3 className="text-xl font-medium text-white mt-0.5">Invoice Uang Muka (DP 30%)</h3>
            </div>
            <div className="text-right text-xs">
              <span className="text-zinc-500 block">Jatuh Tempo:</span>
              <span className="text-white font-medium">
                {new Date(order.invoice.due_date).toLocaleDateString("id-ID", { dateStyle: "medium" })}
              </span>
            </div>
          </div>

          <div className="p-6 rounded-2xl bg-zinc-900/80 border border-zinc-800 flex flex-wrap items-center justify-between gap-4">
            <div>
              <span className="text-xs text-zinc-400 block">Nominal Tagihan DP:</span>
              <span className="text-2xl font-bold text-white mt-1 block">
                {formatRupiah(order.invoice.amount)}
              </span>
            </div>

            <div className="flex items-center gap-3">
              {order.invoice.status === "PAID" ? (
                <span className="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                  ✓ Lunas
                </span>
              ) : (
                <button
                  onClick={handlePayInvoice}
                  disabled={payingLoading}
                  className="px-6 py-2.5 rounded-xl text-xs font-semibold bg-amber-400 text-zinc-950 hover:bg-amber-300 transition-all shadow-md shadow-amber-400/20"
                >
                  {payingLoading ? "Membuka Duitku..." : "Bayar via Duitku Virtual Account"}
                </button>
              )}
            </div>
          </div>
        </div>
      )}

      {/* TAB 4: MESSAGES & DISCUSSION */}
      {activeTab === "MESSAGES" && (
        <div
          className="p-6 sm:p-8 rounded-2xl border space-y-6"
          style={{
            background: "linear-gradient(180deg, rgba(24, 24, 28, 0.7), rgba(16, 16, 20, 0.9))",
            borderColor: "rgba(255, 255, 255, 0.08)",
          }}
        >
          <div className="border-b border-zinc-800 pb-3">
            <h3 className="text-base font-medium text-white">Diskusi & Catatan Pesanan</h3>
            <p className="text-xs text-zinc-400">
              Komunikasi langsung dengan tim BDJG seputar detail brief, penyesuaian jadwal, atau negosiasi konsep.
            </p>
          </div>

          {/* Messages list */}
          <div className="space-y-4 max-h-96 overflow-y-auto pr-2">
            {order.messages && order.messages.length > 0 ? (
              order.messages.map((msg) => {
                const isAdmin = msg.sender?.roles?.some((r) => ["OWNER", "ADMIN"].includes(r.name));
                return (
                  <div
                    key={msg.id}
                    className={`p-4 rounded-xl border text-xs space-y-1.5 ${
                      isAdmin
                        ? "bg-amber-950/20 border-amber-500/30 mr-8"
                        : "bg-zinc-900 border-zinc-800 ml-8 text-right"
                    }`}
                  >
                    <div className={`flex items-center gap-2 ${isAdmin ? "" : "justify-end"}`}>
                      <span className="font-semibold text-zinc-200">
                        {msg.sender?.name || "Client"}
                      </span>
                      {isAdmin && (
                        <span className="px-1.5 py-0.2 rounded text-[10px] bg-amber-400 text-zinc-950 font-bold">
                          BDJG Team
                        </span>
                      )}
                      <span className="text-zinc-500 text-[10px]">
                        {new Date(msg.created_at).toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" })}
                      </span>
                    </div>
                    <p className="text-zinc-300 leading-relaxed whitespace-pre-line text-left">
                      {msg.message}
                    </p>
                  </div>
                );
              })
            ) : (
              <div className="py-8 text-center text-xs text-zinc-500">
                Belum ada pesan diskusi pada pesanan ini. Anda dapat menulis pertanyaan atau catatan di bawah.
              </div>
            )}
          </div>

          {/* Message input */}
          <form onSubmit={handleSendMessage} className="pt-4 border-t border-zinc-800 flex gap-3">
            <input
              type="text"
              value={newMessage}
              onChange={(e) => setNewMessage(e.target.value)}
              placeholder="Tulis pesan atau pertanyaan ke tim BDJG..."
              className="flex-1 px-4 py-3 rounded-xl bg-zinc-900 border border-zinc-700/70 text-xs text-white focus:outline-none focus:border-amber-400"
            />
            <button
              type="submit"
              disabled={sendingMsg || !newMessage.trim()}
              className="px-5 py-3 rounded-xl text-xs font-semibold bg-amber-400 text-zinc-950 hover:bg-amber-300 disabled:opacity-50 transition-colors"
            >
              {sendingMsg ? "Mengirim..." : "Kirim 💬"}
            </button>
          </form>
        </div>
      )}
    </div>
  );
}
