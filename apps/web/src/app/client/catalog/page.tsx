"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";

interface PackageAddOn {
  id: string;
  name: string;
  price: number;
  description_internal?: string;
}

interface ServicePackage {
  id: string;
  name: string;
  slug: string;
  price: number | null;
  base_price?: number | null;
  description?: string;
  description_internal?: string;
  deliverables?: string[];
  is_popular?: boolean;
}

interface ServiceCategory {
  id: string;
  name: string;
  slug: string;
  category?: string;
  description?: string;
  packages: ServicePackage[];
  add_ons?: PackageAddOn[];
}

export default function ClientCatalogPage() {
  const router = useRouter();
  const [services, setServices] = useState<ServiceCategory[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedCategory, setSelectedCategory] = useState<string>("ALL");
  const [selectedPackage, setSelectedPackage] = useState<ServicePackage | null>(null);
  const [submitting, setSubmitting] = useState<string | null>(null);

  useEffect(() => {
    async function loadCatalog() {
      try {
        const res = await fetch("/api/v1/client/catalog/services", {
          credentials: "include",
          headers: { Accept: "application/json" },
        });
        if (res.ok) {
          const json = await res.json();
          setServices(json.data || []);
        }
      } catch (err) {
        console.error("Failed to load catalog:", err);
      } finally {
        setLoading(false);
      }
    }
    loadCatalog();
  }, []);

  const formatRupiah = (val: number | null | undefined) => {
    if (val === null || val === undefined || val === 0) return "Kustom / Review Admin";
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      maximumFractionDigits: 0,
    }).format(val);
  };

  const handleOrderPackage = async (service: ServiceCategory, pkg: ServicePackage) => {
    setSubmitting(pkg.id);
    try {
      const cat = (service.category || service.slug || "").toUpperCase();
      const briefCat =
        cat.includes("HAPPINESS") || cat.includes("WEDDING")
          ? "WEDDING"
          : cat.includes("PHOTO") || cat.includes("VIDEO")
          ? "WEDDING"
          : "CUSTOM";

      const res = await fetch("/api/v1/client/service-orders", {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          service_id: service.id,
          package_id: pkg.id,
          source: "CATALOG",
          event_name: `${pkg.name} Project`,
          brief_category: briefCat,
        }),
      });

      if (res.ok) {
        const data = await res.json();
        const orderId = data?.data?.id;
        router.push(`/client/orders/${orderId}/brief`);
      } else {
        const err = await res.json();
        alert(err.message || "Gagal membuat pesanan.");
      }
    } catch (e) {
      console.error(e);
      alert("Terjadi kesalahan koneksi saat memulai pesanan.");
    } finally {
      setSubmitting(null);
    }
  };

  const handleCustomStory = async () => {
    setSubmitting("custom");
    try {
      const res = await fetch("/api/v1/client/service-orders", {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          source: "CUSTOM_STORY",
          event_name: "Kustom Brief & Story Project",
          brief_category: "CUSTOM",
        }),
      });

      if (res.ok) {
        const data = await res.json();
        const orderId = data?.data?.id;
        router.push(`/client/orders/${orderId}/brief`);
      } else {
        const err = await res.json();
        alert(err.message || "Gagal membuat pesanan kustom.");
      }
    } catch (e) {
      console.error(e);
      alert("Terjadi kesalahan koneksi.");
    } finally {
      setSubmitting(null);
    }
  };

  const filteredServices = services.filter((s) => {
    if (selectedCategory === "ALL") return true;
    const cat = (s.category || s.slug || "").toUpperCase();
    return cat.includes(selectedCategory);
  });

  const getServiceVisualCover = (slug: string) => {
    switch (slug) {
      case "happiness-package":
        return {
          icon: "💍",
          tag: "Foto + Video Combo",
          gradient: "from-amber-950/50 via-zinc-900 to-zinc-950",
          accentColor: "border-amber-500/30",
        };
      case "photography-package":
        return {
          icon: "📸",
          tag: "Photography Only",
          gradient: "from-purple-950/50 via-zinc-900 to-zinc-950",
          accentColor: "border-purple-500/30",
        };
      case "videography-package":
        return {
          icon: "🎥",
          tag: "Cinematography Only",
          gradient: "from-blue-950/50 via-zinc-900 to-zinc-950",
          accentColor: "border-blue-500/30",
        };
      case "custom-your-story":
        return {
          icon: "🎨",
          tag: "Bespoke Production",
          gradient: "from-emerald-950/50 via-zinc-900 to-zinc-950",
          accentColor: "border-emerald-500/30",
        };
      default:
        return {
          icon: "✨",
          tag: "Exclusive Package",
          gradient: "from-zinc-900 to-zinc-950",
          accentColor: "border-zinc-800",
        };
    }
  };

  return (
    <div className="space-y-8 pb-16">
      {/* Header Banner */}
      <div
        className="p-8 rounded-2xl relative overflow-hidden border shadow-2xl"
        style={{
          background: "linear-gradient(135deg, rgba(20, 20, 24, 0.95), rgba(12, 12, 14, 0.98))",
          borderColor: "rgba(255, 255, 255, 0.08)",
        }}
      >
        <div className="absolute top-0 right-0 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none" />
        <div className="relative z-10 max-w-3xl space-y-3">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
            <span>✨</span> Self-Service Ordering Catalog
          </div>
          <h1 className="text-3xl sm:text-4xl font-light text-white tracking-tight">
            Katalog Layanan & <span className="font-serif italic text-amber-200">Paket Sinematik</span>
          </h1>
          <p className="text-sm sm:text-base text-zinc-400 leading-relaxed">
            Pilih paket dokumentasi yang sesuai dengan kebutuhan momen Anda, lengkapi brief kreatif secara
            langsung, dan dapatkan kepastian jadwal tanpa perlu berulang kali chat WhatsApp manual.
          </p>
        </div>
      </div>

      {/* Category Tabs & Custom Action */}
      <div className="flex flex-wrap items-center justify-between gap-4 border-b pb-4" style={{ borderColor: "rgba(255, 255, 255, 0.08)" }}>
        <div className="flex flex-wrap items-center gap-2">
          {[
            { key: "ALL", label: "Semua Layanan" },
            { key: "HAPPINESS", label: "💍 Happiness (Foto + Video)" },
            { key: "PHOTO", label: "📸 Photography (Foto Saja)" },
            { key: "VIDEO", label: "🎥 Videography (Video Saja)" },
            { key: "CUSTOM", label: "🎨 Custom Story" },
          ].map((cat) => (
            <button
              key={cat.key}
              onClick={() => setSelectedCategory(cat.key)}
              className={`px-4 py-2 rounded-xl text-xs sm:text-sm font-medium transition-all ${
                selectedCategory === cat.key
                  ? "bg-amber-400 text-zinc-950 shadow-md shadow-amber-400/20 font-semibold"
                  : "bg-zinc-900/60 text-zinc-400 hover:text-white hover:bg-zinc-800/80 border border-zinc-800"
              }`}
            >
              {cat.label}
            </button>
          ))}
        </div>

        <button
          onClick={handleCustomStory}
          disabled={submitting === "custom"}
          className="px-4 py-2 rounded-xl text-xs sm:text-sm font-medium bg-gradient-to-r from-zinc-800 to-zinc-900 text-amber-300 border border-amber-500/30 hover:border-amber-400/60 transition-all flex items-center gap-2"
        >
          <span>🎨</span>
          {submitting === "custom" ? "Menyiapkan..." : "Punya Konsep Sendiri? (Custom Story)"}
        </button>
      </div>

      {/* Loading state */}
      {loading ? (
        <div className="py-24 text-center">
          <div className="w-8 h-8 border-2 border-amber-400 border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p className="text-zinc-500 text-sm">Memuat katalog layanan...</p>
        </div>
      ) : filteredServices.length === 0 ? (
        <div className="py-20 text-center rounded-2xl border border-zinc-800/60 bg-zinc-900/30">
          <p className="text-zinc-400 text-sm">Belum ada paket aktif pada kategori ini.</p>
        </div>
      ) : (
        <div className="space-y-16">
          {filteredServices.map((service) => {
            const visual = getServiceVisualCover(service.slug);

            return (
              <div key={service.id} className="space-y-6">
                {/* Section Header Card */}
                <div
                  className={`p-6 rounded-2xl border bg-gradient-to-r ${visual.gradient} ${visual.accentColor} flex flex-wrap items-center justify-between gap-4`}
                >
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <span className="text-xl">{visual.icon}</span>
                      <h2 className="text-xl font-medium text-white tracking-wide">{service.name}</h2>
                    </div>
                    {service.description && (
                      <p className="text-xs text-zinc-400 max-w-2xl leading-relaxed">
                        {service.description}
                      </p>
                    )}
                  </div>
                  <span className="text-xs px-3 py-1 rounded-full bg-zinc-900/80 text-amber-300 border border-amber-500/20 font-medium">
                    {visual.tag}
                  </span>
                </div>

                {/* Package Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  {(service.packages || []).map((pkg) => {
                    const priceValue = pkg.price || pkg.base_price;
                    const isFixedPrice = priceValue !== null && priceValue !== undefined && priceValue > 0;
                    const isAutoCheckout =
                      isFixedPrice &&
                      !["loyalty", "cute", "sweet", "custom"].some((n) => pkg.name.toLowerCase().includes(n));

                    const deliverables =
                      pkg.deliverables && pkg.deliverables.length > 0
                        ? pkg.deliverables
                        : pkg.description_internal
                        ? pkg.description_internal.split(",").map((s) => s.trim())
                        : [];

                    return (
                      <div
                        key={pkg.id}
                        className="group relative rounded-2xl border flex flex-col justify-between transition-all duration-300 hover:border-amber-500/40 hover:shadow-xl hover:shadow-amber-500/5 overflow-hidden"
                        style={{
                          background: "linear-gradient(180deg, rgba(24, 24, 28, 0.7), rgba(16, 16, 20, 0.9))",
                          borderColor: "rgba(255, 255, 255, 0.08)",
                        }}
                      >
                        {/* Top Accent Visual */}
                        <div className="h-2 w-full bg-gradient-to-r from-amber-500/40 via-amber-400/20 to-transparent" />

                        <div className="p-6 space-y-4">
                          {/* Pathway indicator badge */}
                          <div className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium bg-zinc-800/80 text-zinc-300 border border-zinc-700/50">
                            {isAutoCheckout ? (
                              <>
                                <span className="w-1.5 h-1.5 rounded-full bg-emerald-400" />
                                Jalur A: Checkout Instan DP
                              </>
                            ) : (
                              <>
                                <span className="w-1.5 h-1.5 rounded-full bg-amber-400" />
                                Jalur B: Review & Penawaran Kustom
                              </>
                            )}
                          </div>

                          <div>
                            <h3 className="text-lg font-medium text-white group-hover:text-amber-200 transition-colors">
                              {pkg.name}
                            </h3>
                            <div className="mt-2 flex items-baseline gap-1">
                              <span className="text-2xl font-semibold text-white tracking-tight">
                                {formatRupiah(priceValue)}
                              </span>
                              {isFixedPrice && <span className="text-xs text-zinc-500">/ project</span>}
                            </div>
                          </div>

                          {/* Deliverables Preview */}
                          {deliverables.length > 0 && (
                            <div className="space-y-1.5 pt-2 border-t border-zinc-800/60">
                              <span className="text-[11px] font-medium text-zinc-400 uppercase tracking-wider">
                                Rincian Liputan & Output:
                              </span>
                              <ul className="space-y-1 text-xs text-zinc-300">
                                {deliverables.slice(0, 4).map((d, idx) => (
                                  <li key={idx} className="flex items-start gap-2">
                                    <span className="text-amber-400 text-xs mt-0.5">✓</span>
                                    <span className="line-clamp-1">{d}</span>
                                  </li>
                                ))}
                                {deliverables.length > 4 && (
                                  <li className="text-[11px] text-zinc-500 pl-4">
                                    +{deliverables.length - 4} item lainnya...
                                  </li>
                                )}
                              </ul>
                            </div>
                          )}
                        </div>

                        {/* Footer Actions */}
                        <div className="p-6 pt-0 mt-auto flex items-center gap-3">
                          <button
                            onClick={() => setSelectedPackage({ ...pkg, deliverables })}
                            className="flex-1 py-2.5 px-3 rounded-xl text-xs font-medium bg-zinc-900 text-zinc-300 hover:text-white hover:bg-zinc-800 border border-zinc-800 transition-colors"
                          >
                            Detail Paket
                          </button>
                          <button
                            onClick={() => handleOrderPackage(service, pkg)}
                            disabled={submitting === pkg.id}
                            className="flex-1 py-2.5 px-3 rounded-xl text-xs font-semibold bg-amber-400 text-zinc-950 hover:bg-amber-300 transition-all shadow-md shadow-amber-400/10 disabled:opacity-50"
                          >
                            {submitting === pkg.id ? "Membuat..." : "Pilih Paket"}
                          </button>
                        </div>
                      </div>
                    );
                  })}
                </div>

                {/* Add-ons showcase if available */}
                {service.add_ons && service.add_ons.length > 0 && (
                  <div className="p-4 rounded-xl bg-zinc-900/40 border border-zinc-800 space-y-2">
                    <span className="text-xs font-medium text-zinc-400 uppercase tracking-wider block">
                      💡 Add-on Tambahan yang Tersedia untuk Layanan Ini:
                    </span>
                    <div className="flex flex-wrap gap-2">
                      {service.add_ons.map((addon) => (
                        <div
                          key={addon.id}
                          className="px-3 py-1.5 rounded-lg bg-zinc-800/80 border border-zinc-700/60 text-xs text-zinc-300 flex items-center gap-2"
                        >
                          <span>{addon.name}</span>
                          <span className="text-amber-300 font-semibold">{formatRupiah(addon.price)}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}

      {/* Package Detail Modal */}
      {selectedPackage && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
          <div
            className="w-full max-w-xl rounded-2xl border p-6 sm:p-8 space-y-6 max-h-[90vh] overflow-y-auto"
            style={{
              background: "linear-gradient(180deg, #18181c, #111114)",
              borderColor: "rgba(255, 255, 255, 0.12)",
            }}
          >
            <div className="flex items-start justify-between border-b border-zinc-800 pb-4">
              <div>
                <span className="text-xs font-medium text-amber-400 uppercase tracking-wider">
                  Detail Paket Lengkap
                </span>
                <h3 className="text-2xl font-light text-white mt-1">{selectedPackage.name}</h3>
                <p className="text-xl font-semibold text-white mt-1">
                  {formatRupiah(selectedPackage.price || selectedPackage.base_price)}
                </p>
              </div>
              <button
                onClick={() => setSelectedPackage(null)}
                className="w-8 h-8 rounded-full bg-zinc-800 hover:bg-zinc-700 text-zinc-400 hover:text-white flex items-center justify-center text-sm"
              >
                ✕
              </button>
            </div>

            {selectedPackage.description_internal && (
              <p className="text-sm text-zinc-300 leading-relaxed">
                {selectedPackage.description_internal}
              </p>
            )}

            {selectedPackage.deliverables && selectedPackage.deliverables.length > 0 && (
              <div className="space-y-2">
                <h4 className="text-xs font-semibold text-zinc-400 uppercase tracking-wider">
                  Deliverables & Rincian Output:
                </h4>
                <div className="space-y-2 p-4 rounded-xl bg-zinc-900/60 border border-zinc-800">
                  {selectedPackage.deliverables.map((item, idx) => (
                    <div key={idx} className="flex items-start gap-2 text-xs text-zinc-200">
                      <span className="text-amber-400">✓</span>
                      <span>{item}</span>
                    </div>
                  ))}
                </div>
              </div>
            )}

            <div className="pt-4 border-t border-zinc-800 flex justify-end gap-3">
              <button
                onClick={() => setSelectedPackage(null)}
                className="px-5 py-2.5 rounded-xl text-xs font-medium bg-zinc-800 hover:bg-zinc-700 text-zinc-300"
              >
                Tutup
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
