"use client";

import { useEffect, useState, use } from "react";
import { useRouter } from "next/navigation";

interface BriefAttachment {
  id: string;
  original_name: string;
  filename: string;
  size_bytes: number;
  attachment_type: string;
  url?: string;
}

interface ServiceOrder {
  id: string;
  order_number: string;
  status: string;
  review_type: string;
  source: string;
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
  attachments?: BriefAttachment[];
}

export default function OrderBriefWizardPage({
  params: paramsPromise,
}: {
  params: Promise<{ id: string }>;
}) {
  const params = use(paramsPromise);
  const router = useRouter();
  const [order, setOrder] = useState<ServiceOrder | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [step, setStep] = useState(1);
  const [uploading, setUploading] = useState(false);

  // Form State
  const [eventName, setEventName] = useState("");
  const [eventDate, setEventDate] = useState("");
  const [venueName, setVenueName] = useState("");
  const [venueCount, setVenueCount] = useState(1);
  const [city, setCity] = useState("Surabaya");
  const [isOutsideBaseArea, setIsOutsideBaseArea] = useState(false);
  const [rundownNotes, setRundownNotes] = useState("");
  const [coupleNames, setCoupleNames] = useState("");
  const [stylePreference, setStylePreference] = useState("");
  const [mustHaveMoments, setMustHaveMoments] = useState("");
  const [brandName, setBrandName] = useState("");
  const [deliverablesNeeded, setDeliverablesNeeded] = useState("");
  const [customDescription, setCustomDescription] = useState("");
  const [customBudgetTarget, setCustomBudgetTarget] = useState<number | "">("");
  const [customReferenceLinks, setCustomReferenceLinks] = useState("");
  const [specialRequests, setSpecialRequests] = useState("");
  const [attachments, setAttachments] = useState<BriefAttachment[]>([]);

  useEffect(() => {
    async function loadOrder() {
      try {
        const res = await fetch(`/api/v1/client/service-orders/${params.id}`, {
          credentials: "include",
          headers: { Accept: "application/json" },
        });

        if (res.ok) {
          const json = await res.json();
          const ord: ServiceOrder = json.data;
          setOrder(ord);

          if (ord.brief) {
            setEventName(ord.brief.event_name || "");
            setEventDate(ord.brief.event_date || "");
            setVenueName(ord.brief.venue_name || "");
            setVenueCount(ord.brief.venue_count || 1);
            setCity(ord.brief.city || "Surabaya");
            setIsOutsideBaseArea(ord.brief.is_outside_base_area || false);
            setRundownNotes(ord.brief.rundown_notes || "");
            setCoupleNames(ord.brief.wedding_couple_names || "");
            setStylePreference(ord.brief.wedding_style_preference || "");
            setMustHaveMoments(ord.brief.wedding_must_have_moments || "");
            setBrandName(ord.brief.commercial_brand_name || "");
            setDeliverablesNeeded(ord.brief.commercial_deliverables_needed || "");
            setCustomDescription(ord.brief.custom_description || "");
            setCustomBudgetTarget(ord.brief.custom_budget_target || "");
            setCustomReferenceLinks(ord.brief.custom_reference_links || "");
            setSpecialRequests(ord.brief.special_requests || "");
          }
          if (ord.attachments) {
            setAttachments(ord.attachments);
          }
        } else {
          router.push("/client/orders");
        }
      } catch (e) {
        console.error("Failed to load order:", e);
      } finally {
        setLoading(false);
      }
    }
    loadOrder();
  }, [params.id, router]);

  const saveBriefData = async (notify = false) => {
    setSaving(true);
    try {
      const res = await fetch(`/api/v1/client/service-orders/${params.id}/brief`, {
        method: "PATCH",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          event_name: eventName,
          event_date: eventDate || null,
          venue_name: venueName,
          venue_count: venueCount,
          city: city,
          is_outside_base_area: isOutsideBaseArea,
          rundown_notes: rundownNotes,
          wedding_couple_names: coupleNames,
          wedding_style_preference: stylePreference,
          wedding_must_have_moments: mustHaveMoments,
          commercial_brand_name: brandName,
          commercial_deliverables_needed: deliverablesNeeded,
          custom_description: customDescription,
          custom_budget_target: customBudgetTarget === "" ? null : Number(customBudgetTarget),
          custom_reference_links: customReferenceLinks,
          special_requests: specialRequests,
        }),
      });

      if (res.ok) {
        const json = await res.json();
        setOrder(json.data);
        if (notify) alert("Draft brief berhasil disimpan.");
      }
    } catch (e) {
      console.error("Failed to save brief:", e);
    } finally {
      setSaving(false);
    }
  };

  const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files || files.length === 0) return;

    setUploading(true);
    const file = files[0];
    const formData = new FormData();
    formData.append("file", file);
    formData.append("attachment_type", "MOODBOARD");

    try {
      const res = await fetch(`/api/v1/client/service-orders/${params.id}/attachments`, {
        method: "POST",
        credentials: "include",
        headers: {
          Accept: "application/json",
        },
        body: formData,
      });

      if (res.ok) {
        const json = await res.json();
        setAttachments((prev) => [...prev, json.data]);
      } else {
        alert("Gagal mengunggah file. Pastikan ukuran file < 20MB.");
      }
    } catch (err) {
      console.error(err);
      alert("Terjadi kesalahan saat unggah.");
    } finally {
      setUploading(false);
    }
  };

  const handleSubmitOrder = async () => {
    if (!eventName || !eventDate || !venueName) {
      alert("Harap lengkapi Nama Acara, Tanggal, dan Lokasi Venue sebelum submit.");
      return;
    }

    setSubmitting(true);
    await saveBriefData(false);

    try {
      const res = await fetch(`/api/v1/client/service-orders/${params.id}/submit`, {
        method: "POST",
        credentials: "include",
        headers: { Accept: "application/json" },
      });

      if (res.ok) {
        router.push(`/client/orders/${params.id}`);
      } else {
        const err = await res.json();
        alert(err.message || "Gagal submit order.");
      }
    } catch (e) {
      console.error(e);
      alert("Terjadi kesalahan koneksi saat submit.");
    } finally {
      setSubmitting(false);
    }
  };

  // Determine estimated pathway
  const isCustom = order?.source === "CUSTOM_STORY" || !order?.package?.price;
  const isJalurB = isOutsideBaseArea || venueCount > 1 || isCustom;

  if (loading) {
    return (
      <div className="py-24 text-center">
        <div className="w-8 h-8 border-2 border-amber-400 border-t-transparent rounded-full animate-spin mx-auto mb-3" />
        <p className="text-zinc-500 text-sm">Memuat wizard brief pesanan...</p>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto space-y-8 pb-20">
      {/* Header Bar */}
      <div className="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-800/80 pb-6">
        <div>
          <div className="flex items-center gap-2">
            <button
              onClick={() => router.push("/client/orders")}
              className="text-xs text-zinc-400 hover:text-white transition-colors"
            >
              ← Pesanan Saya
            </button>
            <span className="text-zinc-600">/</span>
            <span className="text-xs font-mono text-amber-400">{order?.order_number}</span>
          </div>
          <h1 className="text-2xl font-light text-white mt-1">
            Isi Brief Kreatif: <span className="font-medium text-amber-200">{order?.package?.name || order?.service?.name || "Kustom Project"}</span>
          </h1>
        </div>

        <div className="flex items-center gap-3">
          <button
            onClick={() => saveBriefData(true)}
            disabled={saving}
            className="px-4 py-2 rounded-xl text-xs font-medium bg-zinc-900 hover:bg-zinc-800 text-zinc-300 border border-zinc-700 transition-colors"
          >
            {saving ? "Menyimpan..." : "💾 Simpan Draft"}
          </button>
        </div>
      </div>

      {/* Pathway Alert Indicator */}
      <div
        className={`p-4 rounded-xl border flex items-start gap-3 transition-all ${
          isJalurB
            ? "bg-amber-950/20 border-amber-500/30 text-amber-200"
            : "bg-emerald-950/20 border-emerald-500/30 text-emerald-200"
        }`}
      >
        <span className="text-lg">{isJalurB ? "🧭" : "⚡"}</span>
        <div className="space-y-0.5 text-xs">
          <p className="font-semibold uppercase tracking-wider">
            {isJalurB ? "Jalur B / C: Review Tim Admin & Penawaran Kustom" : "Jalur A: Instant Checkout DP & Konfirmasi Otomatis"}
          </p>
          <p className="text-zinc-300">
            {isJalurB
              ? "Karena lokasi di luar area dasar Surabaya atau paket bersifat kustom, tim BDJG akan mereview brief Anda dan menerbitkan surat penawaran (Quotation) dalam 1x24 jam."
              : "Paket standar dalam area Surabaya. Setelah brief dikonfirmasi, Anda dapat langsung melakukan pembayaran DP 30% untuk mengunci tanggal jadwal."}
          </p>
        </div>
      </div>

      {/* Step Navigation Tabs */}
      <div className="grid grid-cols-5 gap-2 border-b border-zinc-800 pb-4">
        {[
          { num: 1, title: "1. Acara & Waktu" },
          { num: 2, title: "2. Lokasi & Venue" },
          { num: 3, title: "3. Konsep & Style" },
          { num: 4, title: "4. Moodboard & File" },
          { num: 5, title: "5. Konfirmasi" },
        ].map((s) => (
          <button
            key={s.num}
            onClick={() => {
              saveBriefData(false);
              setStep(s.num);
            }}
            className={`py-2 px-2 text-center rounded-xl text-xs font-medium transition-all ${
              step === s.num
                ? "bg-amber-400 text-zinc-950 font-semibold shadow-md shadow-amber-400/10"
                : step > s.num
                ? "bg-zinc-800/80 text-zinc-300 hover:bg-zinc-700"
                : "bg-zinc-900/40 text-zinc-500 hover:text-zinc-400"
            }`}
          >
            {s.title}
          </button>
        ))}
      </div>

      {/* Form Container */}
      <div
        className="p-6 sm:p-8 rounded-2xl border space-y-6"
        style={{
          background: "linear-gradient(180deg, rgba(24, 24, 28, 0.7), rgba(16, 16, 20, 0.9))",
          borderColor: "rgba(255, 255, 255, 0.08)",
        }}
      >
        {/* STEP 1: Acara & Waktu */}
        {step === 1 && (
          <div className="space-y-6">
            <div>
              <h2 className="text-lg font-medium text-white">Langkah 1: Detail Acara & Tanggal</h2>
              <p className="text-xs text-zinc-400">Tentukan nama momen dan estimasi tanggal peliputan.</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-2">
                <label className="text-xs font-medium text-zinc-300">Nama Acara / Judul Project *</label>
                <input
                  type="text"
                  value={eventName}
                  onChange={(e) => setEventName(e.target.value)}
                  placeholder="Contoh: Wedding of Alex & Brenda"
                  className="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-zinc-700/70 text-sm text-white focus:outline-none focus:border-amber-400"
                />
              </div>

              <div className="space-y-2">
                <label className="text-xs font-medium text-zinc-300">Tanggal Peliputan (Shoot Date) *</label>
                <input
                  type="date"
                  value={eventDate}
                  onChange={(e) => setEventDate(e.target.value)}
                  className="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-zinc-700/70 text-sm text-white focus:outline-none focus:border-amber-400"
                />
              </div>
            </div>

            <div className="space-y-2">
              <label className="text-xs font-medium text-zinc-300">Catatan Jadwal / Estimasi Rundown (Opsional)</label>
              <textarea
                value={rundownNotes}
                onChange={(e) => setRundownNotes(e.target.value)}
                rows={3}
                placeholder="Misal: Akad nikah mulai pukul 07.00 WIB, Resepsi malam pukul 18.30 WIB..."
                className="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-zinc-700/70 text-sm text-white focus:outline-none focus:border-amber-400"
              />
            </div>
          </div>
        )}

        {/* STEP 2: Lokasi & Venue */}
        {step === 2 && (
          <div className="space-y-6">
            <div>
              <h2 className="text-lg font-medium text-white">Langkah 2: Lokasi & Tempat Pelaksanaan</h2>
              <p className="text-xs text-zinc-400">Informasi venue untuk kesiapan akomodasi & crew.</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-2">
                <label className="text-xs font-medium text-zinc-300">Nama Venue / Gedung / Lokasi *</label>
                <input
                  type="text"
                  value={venueName}
                  onChange={(e) => setVenueName(e.target.value)}
                  placeholder="Contoh: Grand Ballroom Hotel Majapahit"
                  className="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-zinc-700/70 text-sm text-white focus:outline-none focus:border-amber-400"
                />
              </div>

              <div className="space-y-2">
                <label className="text-xs font-medium text-zinc-300">Kota / Kabupaten *</label>
                <input
                  type="text"
                  value={city}
                  onChange={(e) => setCity(e.target.value)}
                  placeholder="Contoh: Surabaya, Sidoarjo, Bali, Malang..."
                  className="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-zinc-700/70 text-sm text-white focus:outline-none focus:border-amber-400"
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
              <div className="space-y-2">
                <label className="text-xs font-medium text-zinc-300">Jumlah Titik Venue</label>
                <select
                  value={venueCount}
                  onChange={(e) => setVenueCount(Number(e.target.value))}
                  className="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-zinc-700/70 text-sm text-white focus:outline-none focus:border-amber-400"
                >
                  <option value={1}>1 Venue (Tunggal)</option>
                  <option value={2}>2 Venue (Multi-Lokasi)</option>
                  <option value={3}>3+ Venue (Kompleks)</option>
                </select>
              </div>

              <div className="flex items-center gap-3 pt-6">
                <input
                  type="checkbox"
                  id="outsideBase"
                  checked={isOutsideBaseArea}
                  onChange={(e) => setIsOutsideBaseArea(e.target.checked)}
                  className="w-5 h-5 rounded bg-zinc-900 border-zinc-700 text-amber-500 focus:ring-0"
                />
                <label htmlFor="outsideBase" className="text-xs text-zinc-300 cursor-pointer">
                  Lokasi di Luar Area Surabaya / Luar Kota (Akomodasi Crew Berlaku)
                </label>
              </div>
            </div>
          </div>
        )}

        {/* STEP 3: Konsep & Style */}
        {step === 3 && (
          <div className="space-y-6">
            <div>
              <h2 className="text-lg font-medium text-white">Langkah 3: Preferensi Gaya & Konsep Kreatif</h2>
              <p className="text-xs text-zinc-400">Bantu sutradara dan tim editor memahami tone visual yang Anda sukai.</p>
            </div>

            <div className="space-y-2">
              <label className="text-xs font-medium text-zinc-300">Nama Pasangan / Brand Name</label>
              <input
                type="text"
                value={coupleNames || brandName}
                onChange={(e) => {
                  setCoupleNames(e.target.value);
                  setBrandName(e.target.value);
                }}
                placeholder="Nama Calon Pengantin atau Nama Brand / Institusi"
                className="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-zinc-700/70 text-sm text-white focus:outline-none focus:border-amber-400"
              />
            </div>

            <div className="space-y-2">
              <label className="text-xs font-medium text-zinc-300">Preferensi Gaya Visual (Mood & Tone)</label>
              <input
                type="text"
                value={stylePreference}
                onChange={(e) => setStylePreference(e.target.value)}
                placeholder="Contoh: Cinematic Warm & Moody, Clean Editorial, Emosional & Story-Driven..."
                className="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-zinc-700/70 text-sm text-white focus:outline-none focus:border-amber-400"
              />
            </div>

            <div className="space-y-2">
              <label className="text-xs font-medium text-zinc-300">Momen Penting yang Wajib Diambil (Must-have Moments)</label>
              <textarea
                value={mustHaveMoments || deliverablesNeeded}
                onChange={(e) => {
                  setMustHaveMoments(e.target.value);
                  setDeliverablesNeeded(e.target.value);
                }}
                rows={3}
                placeholder="Contoh: First look pengantin, suap-suapan keluarga, kembang api saat midnight, drone shoot gedung..."
                className="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-zinc-700/70 text-sm text-white focus:outline-none focus:border-amber-400"
              />
            </div>

            {isCustom && (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div className="space-y-2">
                  <label className="text-xs font-medium text-zinc-300">Target Budget (Opsional)</label>
                  <input
                    type="number"
                    value={customBudgetTarget}
                    onChange={(e) => setCustomBudgetTarget(e.target.value ? Number(e.target.value) : "")}
                    placeholder="Contoh: 25000000"
                    className="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-zinc-700/70 text-sm text-white focus:outline-none focus:border-amber-400"
                  />
                </div>

                <div className="space-y-2">
                  <label className="text-xs font-medium text-zinc-300">Link Referensi Video / Moodboard Eksternal</label>
                  <input
                    type="text"
                    value={customReferenceLinks}
                    onChange={(e) => setCustomReferenceLinks(e.target.value)}
                    placeholder="https://pinterest.com/... atau https://youtube.com/..."
                    className="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-zinc-700/70 text-sm text-white focus:outline-none focus:border-amber-400"
                  />
                </div>
              </div>
            )}
          </div>
        )}

        {/* STEP 4: Unggah Moodboard */}
        {step === 4 && (
          <div className="space-y-6">
            <div>
              <h2 className="text-lg font-medium text-white">Langkah 4: Unggah Moodboard & File Referensi</h2>
              <p className="text-xs text-zinc-400">Unggah file rundown PDF, layout venue, atau gambar inspirasi mood visual.</p>
            </div>

            {/* Upload Area */}
            <div className="p-8 rounded-2xl border-2 border-dashed border-zinc-700 hover:border-amber-400/50 bg-zinc-900/40 text-center space-y-3 transition-colors">
              <div className="text-3xl">📁</div>
              <div>
                <p className="text-sm font-medium text-white">Pilih file atau seret ke sini</p>
                <p className="text-xs text-zinc-500">Mendukung PDF, PNG, JPG (Maks 20MB)</p>
              </div>
              <input
                type="file"
                id="fileUpload"
                onChange={handleFileUpload}
                disabled={uploading}
                className="hidden"
                accept=".pdf,.png,.jpg,.jpeg,.zip"
              />
              <label
                htmlFor="fileUpload"
                className="inline-block px-4 py-2 rounded-xl text-xs font-semibold bg-amber-400 text-zinc-950 hover:bg-amber-300 cursor-pointer transition-colors"
              >
                {uploading ? "Mengunggah..." : "Pilih File dari Komputer"}
              </label>
            </div>

            {/* Uploaded List */}
            {attachments.length > 0 && (
              <div className="space-y-2">
                <h4 className="text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                  File Terunggah ({attachments.length})
                </h4>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  {attachments.map((att) => (
                    <div
                      key={att.id}
                      className="p-3 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-between text-xs"
                    >
                      <div className="flex items-center gap-2 overflow-hidden">
                        <span className="text-amber-400">📄</span>
                        <span className="truncate text-zinc-200">{att.original_name}</span>
                      </div>
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

        {/* STEP 5: Konfirmasi */}
        {step === 5 && (
          <div className="space-y-6">
            <div>
              <h2 className="text-lg font-medium text-white">Langkah 5: Ringkasan & Konfirmasi Brief</h2>
              <p className="text-xs text-zinc-400">Periksa kembali rincian brief sebelum mengirimkannya ke tim BDJG.</p>
            </div>

            <div className="p-5 rounded-xl bg-zinc-900/80 border border-zinc-800 space-y-4 text-xs">
              <div className="grid grid-cols-2 gap-4 pb-3 border-b border-zinc-800">
                <div>
                  <span className="text-zinc-500 block">Nama Acara:</span>
                  <span className="text-white font-medium text-sm">{eventName || "-"}</span>
                </div>
                <div>
                  <span className="text-zinc-500 block">Tanggal Peliputan:</span>
                  <span className="text-white font-medium text-sm">{eventDate || "-"}</span>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4 pb-3 border-b border-zinc-800">
                <div>
                  <span className="text-zinc-500 block">Venue & Kota:</span>
                  <span className="text-white font-medium">{venueName || "-"} ({city})</span>
                </div>
                <div>
                  <span className="text-zinc-500 block">Jalur Pemrosesan:</span>
                  <span className={`font-semibold ${isJalurB ? "text-amber-300" : "text-emerald-300"}`}>
                    {isJalurB ? "Jalur B (Review & Penawaran Kustom)" : "Jalur A (Auto-Checkout DP)"}
                  </span>
                </div>
              </div>

              <div>
                <span className="text-zinc-500 block">Preferensi Visual:</span>
                <span className="text-zinc-200">{stylePreference || "Standar Sinematik BDJG"}</span>
              </div>

              <div>
                <span className="text-zinc-500 block">Catatan Tambahan:</span>
                <span className="text-zinc-200">{specialRequests || rundownNotes || "Tidak ada catatan khusus."}</span>
              </div>

              <div>
                <span className="text-zinc-500 block">Lampiran File:</span>
                <span className="text-zinc-300">{attachments.length} file terlampir</span>
              </div>
            </div>
          </div>
        )}

        {/* Wizard Footer Buttons */}
        <div className="pt-6 border-t border-zinc-800 flex items-center justify-between">
          <button
            onClick={() => setStep((s) => Math.max(1, s - 1))}
            disabled={step === 1}
            className="px-5 py-2.5 rounded-xl text-xs font-medium bg-zinc-900 hover:bg-zinc-800 text-zinc-400 hover:text-white border border-zinc-800 disabled:opacity-30 disabled:pointer-events-none transition-colors"
          >
            ← Sebelumnya
          </button>

          {step < 5 ? (
            <button
              onClick={() => {
                saveBriefData(false);
                setStep((s) => Math.min(5, s + 1));
              }}
              className="px-6 py-2.5 rounded-xl text-xs font-semibold bg-amber-400 text-zinc-950 hover:bg-amber-300 transition-all shadow-md shadow-amber-400/10"
            >
              Lanjutkan →
            </button>
          ) : (
            <button
              onClick={handleSubmitOrder}
              disabled={submitting}
              className="px-8 py-2.5 rounded-xl text-xs font-semibold bg-gradient-to-r from-amber-400 to-amber-300 text-zinc-950 hover:from-amber-300 hover:to-amber-200 transition-all shadow-lg shadow-amber-400/20 disabled:opacity-50"
            >
              {submitting ? "Memproses Submit..." : "🚀 Konfirmasi & Kirim Pesanan"}
            </button>
          )}
        </div>
      </div>
    </div>
  );
}
