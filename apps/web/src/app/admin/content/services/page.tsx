"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface PackageItem {
  id: number;
  service_id: number;
  name: string;
  base_price: number;
  currency?: string;
  default_dp_type?: string;
  default_dp_value?: number;
  description_internal?: string;
  status?: string;
}

interface AddOnItem {
  id: number;
  service_id: number;
  name: string;
  base_price: number;
  unit_type?: string;
  status?: string;
  service?: {
    id: number;
    name: string;
  };
}

interface ServiceItem {
  id: number;
  name: string;
  slug: string;
  description_external?: string;
  packages?: PackageItem[];
  add_ons?: AddOnItem[];
}

function formatRupiah(amount: number): string {
  return "Rp " + (amount || 0).toLocaleString("id-ID");
}

export default function AdminServicesCatalogPage() {
  const [activeTab, setActiveTab] = useState<"packages" | "services" | "addons">("packages");
  const [services, setServices] = useState<ServiceItem[]>([]);
  const [packages, setPackages] = useState<PackageItem[]>([]);
  const [addons, setAddons] = useState<AddOnItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [toast, setToast] = useState<{ msg: string; type: "success" | "error" } | null>(null);

  // Modal States
  const [pkgModalOpen, setPkgModalOpen] = useState(false);
  const [editingPkg, setEditingPkg] = useState<PackageItem | null>(null);
  const [pkgForm, setPkgForm] = useState({
    service_id: 1,
    name: "",
    base_price: 25000000,
    default_dp_type: "PERCENTAGE",
    default_dp_value: 50,
    description_internal: "",
  });

  const [svcModalOpen, setSvcModalOpen] = useState(false);
  const [editingSvc, setEditingSvc] = useState<ServiceItem | null>(null);
  const [svcForm, setSvcForm] = useState({
    name: "",
    slug: "",
    description_external: "",
  });

  const [addonModalOpen, setAddonModalOpen] = useState(false);
  const [editingAddon, setEditingAddon] = useState<AddOnItem | null>(null);
  const [addonForm, setAddonForm] = useState({
    service_id: 1,
    name: "",
    base_price: 3500000,
    unit_type: "FLAT",
  });

  const [saving, setSaving] = useState(false);

  const showToast = (msg: string, type: "success" | "error" = "success") => {
    setToast({ msg, type });
    setTimeout(() => setToast(null), 4000);
  };

  const loadData = async () => {
    setLoading(true);
    try {
      const [svcRes, pkgRes, addRes] = await Promise.all([
        fetchApi<{ data: ServiceItem[] }>("/api/v1/admin/catalog/services"),
        fetchApi<{ data: PackageItem[] }>("/api/v1/admin/catalog/packages"),
        fetchApi<{ data: AddOnItem[] }>("/api/v1/admin/catalog/add-ons"),
      ]);
      setServices(svcRes.data || []);
      setPackages(pkgRes.data || []);
      setAddons(addRes.data || []);
    } catch (err) {
      console.error("Failed to load catalog data:", err);
      showToast("Gagal memuat data katalog dari server.", "error");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  // --- Package Actions ---
  const handleOpenPkgModal = (pkg?: PackageItem) => {
    if (pkg) {
      setEditingPkg(pkg);
      setPkgForm({
        service_id: pkg.service_id,
        name: pkg.name,
        base_price: pkg.base_price,
        default_dp_type: pkg.default_dp_type || "PERCENTAGE",
        default_dp_value: pkg.default_dp_value || 50,
        description_internal: pkg.description_internal || "",
      });
    } else {
      setEditingPkg(null);
      setPkgForm({
        service_id: services[0]?.id || 1,
        name: "",
        base_price: 20000000,
        default_dp_type: "PERCENTAGE",
        default_dp_value: 50,
        description_internal: "",
      });
    }
    setPkgModalOpen(true);
  };

  const handleSavePackage = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      if (editingPkg) {
        // Update package price & details
        await fetchApi(`/api/v1/admin/catalog/packages/${editingPkg.id}`, {
          method: "PUT",
          body: JSON.stringify(pkgForm),
        });
        showToast(`Harga & paket "${pkgForm.name}" berhasil diperbarui!`);
      } else {
        // Create new package
        await fetchApi("/api/v1/admin/catalog/packages", {
          method: "POST",
          body: JSON.stringify(pkgForm),
        });
        showToast(`Paket baru "${pkgForm.name}" berhasil ditambahkan ke katalog!`);
      }
      setPkgModalOpen(false);
      loadData();
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Gagal menyimpan paket.";
      showToast(msg, "error");
    } finally {
      setSaving(false);
    }
  };

  const handleDeletePackage = async (pkg: PackageItem) => {
    if (!confirm(`Hapus paket "${pkg.name}" (${formatRupiah(pkg.base_price)}) dari katalog?`)) return;
    try {
      await fetchApi(`/api/v1/admin/catalog/packages/${pkg.id}`, { method: "DELETE" });
      showToast(`Paket "${pkg.name}" berhasil dihapus.`);
      loadData();
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Gagal menghapus paket.";
      showToast(msg, "error");
    }
  };

  // --- Service Actions ---
  const handleOpenSvcModal = (svc?: ServiceItem) => {
    if (svc) {
      setEditingSvc(svc);
      setSvcForm({
        name: svc.name,
        slug: svc.slug,
        description_external: svc.description_external || "",
      });
    } else {
      setEditingSvc(null);
      setSvcForm({
        name: "",
        slug: "",
        description_external: "",
      });
    }
    setSvcModalOpen(true);
  };

  const handleSaveService = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      if (editingSvc) {
        await fetchApi(`/api/v1/admin/catalog/services/${editingSvc.id}`, {
          method: "PUT",
          body: JSON.stringify(svcForm),
        });
        showToast(`Layanan "${svcForm.name}" berhasil diperbarui!`);
      } else {
        await fetchApi("/api/v1/admin/catalog/services", {
          method: "POST",
          body: JSON.stringify(svcForm),
        });
        showToast(`Layanan baru "${svcForm.name}" berhasil ditambahkan!`);
      }
      setSvcModalOpen(false);
      loadData();
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Gagal menyimpan layanan.";
      showToast(msg, "error");
    } finally {
      setSaving(false);
    }
  };

  const handleDeleteService = async (svc: ServiceItem) => {
    if (!confirm(`Hapus layanan "${svc.name}" beserta seluruh paket di dalamnya?`)) return;
    try {
      await fetchApi(`/api/v1/admin/catalog/services/${svc.id}`, { method: "DELETE" });
      showToast(`Layanan "${svc.name}" dihapus.`);
      loadData();
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Gagal menghapus layanan.";
      showToast(msg, "error");
    }
  };

  // --- Addon Actions ---
  const handleOpenAddonModal = (addon?: AddOnItem) => {
    if (addon) {
      setEditingAddon(addon);
      setAddonForm({
        service_id: addon.service_id,
        name: addon.name,
        base_price: addon.base_price,
        unit_type: addon.unit_type || "FLAT",
      });
    } else {
      setEditingAddon(null);
      setAddonForm({
        service_id: services[0]?.id || 1,
        name: "",
        base_price: 2500000,
        unit_type: "FLAT",
      });
    }
    setAddonModalOpen(true);
  };

  const handleSaveAddon = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      if (editingAddon) {
        await fetchApi(`/api/v1/admin/catalog/add-ons/${editingAddon.id}`, {
          method: "PUT",
          body: JSON.stringify(addonForm),
        });
        showToast(`Add-on "${addonForm.name}" berhasil diperbarui!`);
      } else {
        await fetchApi("/api/v1/admin/catalog/add-ons", {
          method: "POST",
          body: JSON.stringify(addonForm),
        });
        showToast(`Add-on baru "${addonForm.name}" ditambahkan!`);
      }
      setAddonModalOpen(false);
      loadData();
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Gagal menyimpan add-on.";
      showToast(msg, "error");
    } finally {
      setSaving(false);
    }
  };

  const handleDeleteAddon = async (addon: AddOnItem) => {
    if (!confirm(`Hapus add-on "${addon.name}"?`)) return;
    try {
      await fetchApi(`/api/v1/admin/catalog/add-ons/${addon.id}`, { method: "DELETE" });
      showToast(`Add-on "${addon.name}" dihapus.`);
      loadData();
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Gagal menghapus add-on.";
      showToast(msg, "error");
    }
  };

  const getServiceName = (id: number) => {
    return services.find((s) => s.id === id)?.name || `Service #${id}`;
  };

  return (
    <>
      {/* Toast Notification */}
      {toast && (
        <div
          style={{
            position: "fixed",
            bottom: 24,
            right: 24,
            zIndex: 9999,
            padding: "12px 20px",
            borderRadius: 8,
            fontSize: 13,
            fontWeight: 600,
            background: toast.type === "success" ? "#1b4332" : "#5c1d1d",
            color: toast.type === "success" ? "#80ed99" : "#ffb4b4",
            border: `1px solid ${toast.type === "success" ? "#2d6a4f" : "#802020"}`,
            boxShadow: "0 8px 24px rgba(0,0,0,0.5)",
          }}
        >
          {toast.type === "success" ? "✓ " : "⚠ "}
          {toast.msg}
        </div>
      )}

      <div className="pagehead">
        <div>
          <h1>📦 Services, Packages & Pricing Catalog</h1>
          <p>Kelola master disiplin studio, sesuaikan harga paket (Base Price), nominal DP, dan add-on komersial</p>
        </div>
        <div style={{ display: "flex", gap: 10 }}>
          {activeTab === "packages" && (
            <button className="btn btn-primary" onClick={() => handleOpenPkgModal()}>
              + Tambah Paket Baru
            </button>
          )}
          {activeTab === "services" && (
            <button className="btn btn-primary" onClick={() => handleOpenSvcModal()}>
              + Tambah Layanan
            </button>
          )}
          {activeTab === "addons" && (
            <button className="btn btn-primary" onClick={() => handleOpenAddonModal()}>
              + Tambah Add-on
            </button>
          )}
        </div>
      </div>

      {/* Tabs */}
      <div className="filter-bar" style={{ marginBottom: 20 }}>
        <button
          className={`btn ${activeTab === "packages" ? "btn-primary" : "btn-secondary"}`}
          onClick={() => setActiveTab("packages")}
        >
          💰 Paket & Harga ({packages.length})
        </button>
        <button
          className={`btn ${activeTab === "services" ? "btn-primary" : "btn-secondary"}`}
          onClick={() => setActiveTab("services")}
        >
          🎬 Disiplin Layanan ({services.length})
        </button>
        <button
          className={`btn ${activeTab === "addons" ? "btn-primary" : "btn-secondary"}`}
          onClick={() => setActiveTab("addons")}
        >
          ⚡ Add-ons Matrix ({addons.length})
        </button>
      </div>

      {/* TAB 1: PACKAGES & PRICING */}
      {activeTab === "packages" && (
        <div className="card">
          <div className="chead">
            <h3>Daftar Paket & Penetapan Harga ({packages.length})</h3>
            <span style={{ fontSize: 12, color: "var(--muted)" }}>
              Klik &ldquo;Edit Harga&rdquo; pada paket untuk mengubah Base Price atau rasio Down Payment (DP).
            </span>
          </div>
          <div className="cbody" style={{ padding: 0 }}>
            {loading ? (
              <div className="empty">Memuat daftar paket & harga...</div>
            ) : packages.length === 0 ? (
              <div className="empty">Belum ada paket terdaftar. Klik &ldquo;+ Tambah Paket Baru&rdquo; di atas.</div>
            ) : (
              <div style={{ overflowX: "auto" }}>
                <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
                  <thead>
                    <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                      <th style={{ padding: "12px 16px" }}>Nama Paket</th>
                      <th style={{ padding: "12px 16px" }}>Layanan Induk</th>
                      <th style={{ padding: "12px 16px" }}>Harga Dasar (Base Price)</th>
                      <th style={{ padding: "12px 16px" }}>Default DP</th>
                      <th style={{ padding: "12px 16px" }}>Status</th>
                      <th style={{ padding: "12px 16px", textAlign: "right" }}>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    {packages.map((pkg) => (
                      <tr
                        key={pkg.id}
                        style={{
                          borderBottom: "1px solid rgba(255,255,255,0.04)",
                          transition: "background 0.2s",
                        }}
                      >
                        <td style={{ padding: "12px 16px" }}>
                          <div style={{ fontWeight: 700, color: "#FFFFFF" }}>{pkg.name}</div>
                          {pkg.description_internal && (
                            <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 2 }}>
                              {pkg.description_internal}
                            </div>
                          )}
                        </td>
                        <td style={{ padding: "12px 16px", color: "var(--accent)" }}>
                          {getServiceName(pkg.service_id)}
                        </td>
                        <td style={{ padding: "12px 16px" }}>
                          <span
                            style={{
                              fontWeight: 800,
                              color: "#80ed99",
                              background: "rgba(128, 237, 153, 0.1)",
                              padding: "4px 10px",
                              borderRadius: 4,
                              fontFamily: "var(--font-mono, monospace)",
                            }}
                          >
                            {formatRupiah(pkg.base_price)}
                          </span>
                        </td>
                        <td style={{ padding: "12px 16px", fontFamily: "var(--font-mono, monospace)" }}>
                          {pkg.default_dp_type === "PERCENTAGE"
                            ? `${pkg.default_dp_value}% (${formatRupiah((pkg.base_price * (pkg.default_dp_value || 50)) / 100)})`
                            : formatRupiah(pkg.default_dp_value || 0)}
                        </td>
                        <td style={{ padding: "12px 16px" }}>
                          <span className="chip c-green" style={{ fontSize: 10 }}>
                            {pkg.status || "ACTIVE"}
                          </span>
                        </td>
                        <td style={{ padding: "12px 16px", textAlign: "right" }}>
                          <div style={{ display: "inline-flex", gap: 6 }}>
                            <button
                              className="btn btn-secondary"
                              style={{ padding: "4px 10px", fontSize: 11 }}
                              onClick={() => handleOpenPkgModal(pkg)}
                            >
                              ✏️ Edit Harga
                            </button>
                            <button
                              className="btn btn-secondary"
                              style={{ padding: "4px 10px", fontSize: 11, color: "#ff8b8b" }}
                              onClick={() => handleDeletePackage(pkg)}
                            >
                              🗑️
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      )}

      {/* TAB 2: SERVICES */}
      {activeTab === "services" && (
        <div className="card">
          <div className="chead">
            <h3>Disiplin Layanan Studio ({services.length})</h3>
          </div>
          <div className="cbody" style={{ padding: 0 }}>
            {loading ? (
              <div className="empty">Memuat layanan...</div>
            ) : services.length === 0 ? (
              <div className="empty">Belum ada layanan terdaftar.</div>
            ) : (
              <div style={{ overflowX: "auto" }}>
                <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
                  <thead>
                    <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                      <th style={{ padding: "12px 16px" }}>Nama Layanan</th>
                      <th style={{ padding: "12px 16px" }}>Slug URL</th>
                      <th style={{ padding: "12px 16px" }}>Total Paket Terdaftar</th>
                      <th style={{ padding: "12px 16px", textAlign: "right" }}>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    {services.map((s) => (
                      <tr
                        key={s.id}
                        style={{ borderBottom: "1px solid rgba(255,255,255,0.04)" }}
                      >
                        <td style={{ padding: "12px 16px", fontWeight: 700, color: "#FFFFFF" }}>
                          {s.name}
                        </td>
                        <td style={{ padding: "12px 16px", color: "var(--muted)", fontFamily: "monospace" }}>
                          /{s.slug}
                        </td>
                        <td style={{ padding: "12px 16px" }}>
                          <span className="chip c-black">
                            {packages.filter((p) => p.service_id === s.id).length} Paket Aktif
                          </span>
                        </td>
                        <td style={{ padding: "12px 16px", textAlign: "right" }}>
                          <div style={{ display: "inline-flex", gap: 6 }}>
                            <button
                              className="btn btn-secondary"
                              style={{ padding: "4px 10px", fontSize: 11 }}
                              onClick={() => handleOpenSvcModal(s)}
                            >
                              ✏️ Edit
                            </button>
                            <button
                              className="btn btn-secondary"
                              style={{ padding: "4px 10px", fontSize: 11, color: "#ff8b8b" }}
                              onClick={() => handleDeleteService(s)}
                            >
                              🗑️
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      )}

      {/* TAB 3: ADD-ONS */}
      {activeTab === "addons" && (
        <div className="card">
          <div className="chead">
            <h3>Add-ons & Enhancement Options ({addons.length})</h3>
            <span style={{ fontSize: 12, color: "var(--muted)" }}>
              Opsi penambahan seperti Drone FPV, Same Day Edit, atau Hard Drive Handover
            </span>
          </div>
          <div className="cbody" style={{ padding: 0 }}>
            {loading ? (
              <div className="empty">Memuat data add-on...</div>
            ) : addons.length === 0 ? (
              <div className="empty">Belum ada add-on terdaftar.</div>
            ) : (
              <div style={{ overflowX: "auto" }}>
                <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
                  <thead>
                    <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                      <th style={{ padding: "12px 16px" }}>Nama Add-on</th>
                      <th style={{ padding: "12px 16px" }}>Layanan Terkait</th>
                      <th style={{ padding: "12px 16px" }}>Harga Tambahan</th>
                      <th style={{ padding: "12px 16px" }}>Tipe Unit</th>
                      <th style={{ padding: "12px 16px", textAlign: "right" }}>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    {addons.map((add) => (
                      <tr
                        key={add.id}
                        style={{ borderBottom: "1px solid rgba(255,255,255,0.04)" }}
                      >
                        <td style={{ padding: "12px 16px", fontWeight: 700, color: "#FFFFFF" }}>
                          {add.name}
                        </td>
                        <td style={{ padding: "12px 16px", color: "var(--accent)" }}>
                          {add.service?.name || getServiceName(add.service_id)}
                        </td>
                        <td style={{ padding: "12px 16px" }}>
                          <span
                            style={{
                              fontWeight: 700,
                              color: "#80ed99",
                              fontFamily: "var(--font-mono, monospace)",
                            }}
                          >
                            + {formatRupiah(add.base_price)}
                          </span>
                        </td>
                        <td style={{ padding: "12px 16px" }}>
                          <span className="chip c-blue" style={{ fontSize: 10 }}>
                            {add.unit_type || "FLAT"}
                          </span>
                        </td>
                        <td style={{ padding: "12px 16px", textAlign: "right" }}>
                          <div style={{ display: "inline-flex", gap: 6 }}>
                            <button
                              className="btn btn-secondary"
                              style={{ padding: "4px 10px", fontSize: 11 }}
                              onClick={() => handleOpenAddonModal(add)}
                            >
                              ✏️ Edit Harga
                            </button>
                            <button
                              className="btn btn-secondary"
                              style={{ padding: "4px 10px", fontSize: 11, color: "#ff8b8b" }}
                              onClick={() => handleDeleteAddon(add)}
                            >
                              🗑️
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      )}

      {/* MODAL: EDIT / CREATE PACKAGE */}
      {pkgModalOpen && (
        <div
          style={{
            position: "fixed",
            inset: 0,
            zIndex: 999,
            background: "rgba(0,0,0,0.8)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            padding: 16,
          }}
          onClick={() => setPkgModalOpen(false)}
        >
          <div
            className="card"
            style={{ maxWidth: 500, width: "100%", maxHeight: "90vh", overflowY: "auto" }}
            onClick={(e) => e.stopPropagation()}
          >
            <div className="chead">
              <h3>{editingPkg ? `✏️ Edit Harga Paket: ${editingPkg.name}` : "➕ Tambah Paket Baru"}</h3>
              <button className="iconbtn" onClick={() => setPkgModalOpen(false)}>✕</button>
            </div>
            <form onSubmit={handleSavePackage} className="cbody" style={{ display: "flex", flexDirection: "column", gap: 16 }}>
              <div>
                <label className="form-label">Layanan Induk *</label>
                <select
                  className="form-control"
                  value={pkgForm.service_id}
                  onChange={(e) => setPkgForm({ ...pkgForm, service_id: Number(e.target.value) })}
                  required
                >
                  {services.map((s) => (
                    <option key={s.id} value={s.id}>
                      {s.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="form-label">Nama Paket *</label>
                <input
                  type="text"
                  className="form-control"
                  value={pkgForm.name}
                  onChange={(e) => setPkgForm({ ...pkgForm, name: e.target.value })}
                  placeholder="e.g. Signature Wedding Master"
                  required
                />
              </div>

              <div>
                <label className="form-label">Harga Dasar (Base Price dalam IDR Rupiah) *</label>
                <input
                  type="number"
                  step="100000"
                  className="form-control"
                  value={pkgForm.base_price}
                  onChange={(e) => setPkgForm({ ...pkgForm, base_price: Number(e.target.value) })}
                  required
                  style={{ fontWeight: 700, color: "#80ed99", fontSize: 16 }}
                />
                <div style={{ fontSize: 12, color: "var(--muted)", marginTop: 4 }}>
                  Terbaca: <b>{formatRupiah(pkgForm.base_price)}</b>
                </div>
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                <div>
                  <label className="form-label">Tipe Down Payment (DP)</label>
                  <select
                    className="form-control"
                    value={pkgForm.default_dp_type}
                    onChange={(e) => setPkgForm({ ...pkgForm, default_dp_type: e.target.value })}
                  >
                    <option value="PERCENTAGE">Persentase (%)</option>
                    <option value="FIXED">Nominal Tetap (Rp)</option>
                  </select>
                </div>
                <div>
                  <label className="form-label">Nilai DP</label>
                  <input
                    type="number"
                    className="form-control"
                    value={pkgForm.default_dp_value}
                    onChange={(e) => setPkgForm({ ...pkgForm, default_dp_value: Number(e.target.value) })}
                    required
                  />
                  <div style={{ fontSize: 11, color: "var(--muted)", marginTop: 2 }}>
                    {pkgForm.default_dp_type === "PERCENTAGE"
                      ? `${pkgForm.default_dp_value}% = ${formatRupiah((pkgForm.base_price * pkgForm.default_dp_value) / 100)}`
                      : formatRupiah(pkgForm.default_dp_value)}
                  </div>
                </div>
              </div>

              <div>
                <label className="form-label">Deskripsi Deliverables & Ruang Lingkup</label>
                <textarea
                  className="form-control"
                  rows={3}
                  value={pkgForm.description_internal}
                  onChange={(e) => setPkgForm({ ...pkgForm, description_internal: e.target.value })}
                  placeholder="e.g. 2 Hari Shoot, 4K Master, Drone FPV, 2 Putaran Revisi..."
                />
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: 10, marginTop: 12 }}>
                <button
                  type="button"
                  className="btn btn-secondary"
                  disabled={saving}
                  onClick={() => setPkgModalOpen(false)}
                >
                  Batal
                </button>
                <button type="submit" className="btn btn-primary" disabled={saving}>
                  {saving ? "Menyimpan..." : editingPkg ? "Simpan Perubahan Harga" : "Tambah Paket"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL: EDIT / CREATE SERVICE */}
      {svcModalOpen && (
        <div
          style={{
            position: "fixed",
            inset: 0,
            zIndex: 999,
            background: "rgba(0,0,0,0.8)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            padding: 16,
          }}
          onClick={() => setSvcModalOpen(false)}
        >
          <div
            className="card"
            style={{ maxWidth: 450, width: "100%" }}
            onClick={(e) => e.stopPropagation()}
          >
            <div className="chead">
              <h3>{editingSvc ? `✏️ Edit Layanan: ${editingSvc.name}` : "➕ Tambah Layanan Baru"}</h3>
              <button className="iconbtn" onClick={() => setSvcModalOpen(false)}>✕</button>
            </div>
            <form onSubmit={handleSaveService} className="cbody" style={{ display: "flex", flexDirection: "column", gap: 16 }}>
              <div>
                <label className="form-label">Nama Layanan *</label>
                <input
                  type="text"
                  className="form-control"
                  value={svcForm.name}
                  onChange={(e) => {
                    const name = e.target.value;
                    const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
                    setSvcForm({ ...svcForm, name, slug: editingSvc ? svcForm.slug : slug });
                  }}
                  placeholder="e.g. Commercial Film"
                  required
                />
              </div>

              <div>
                <label className="form-label">Slug URL *</label>
                <input
                  type="text"
                  className="form-control"
                  value={svcForm.slug}
                  onChange={(e) => setSvcForm({ ...svcForm, slug: e.target.value })}
                  placeholder="e.g. commercial-film"
                  required
                />
              </div>

              <div>
                <label className="form-label">Deskripsi Ringkas</label>
                <textarea
                  className="form-control"
                  rows={3}
                  value={svcForm.description_external}
                  onChange={(e) => setSvcForm({ ...svcForm, description_external: e.target.value })}
                  placeholder="Deskripsi untuk portofolio dan estimasi publik..."
                />
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: 10, marginTop: 12 }}>
                <button
                  type="button"
                  className="btn btn-secondary"
                  disabled={saving}
                  onClick={() => setSvcModalOpen(false)}
                >
                  Batal
                </button>
                <button type="submit" className="btn btn-primary" disabled={saving}>
                  {saving ? "Menyimpan..." : "Simpan Layanan"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL: EDIT / CREATE ADDON */}
      {addonModalOpen && (
        <div
          style={{
            position: "fixed",
            inset: 0,
            zIndex: 999,
            background: "rgba(0,0,0,0.8)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            padding: 16,
          }}
          onClick={() => setAddonModalOpen(false)}
        >
          <div
            className="card"
            style={{ maxWidth: 450, width: "100%" }}
            onClick={(e) => e.stopPropagation()}
          >
            <div className="chead">
              <h3>{editingAddon ? `✏️ Edit Add-on: ${editingAddon.name}` : "➕ Tambah Add-on Baru"}</h3>
              <button className="iconbtn" onClick={() => setAddonModalOpen(false)}>✕</button>
            </div>
            <form onSubmit={handleSaveAddon} className="cbody" style={{ display: "flex", flexDirection: "column", gap: 16 }}>
              <div>
                <label className="form-label">Layanan Terkait *</label>
                <select
                  className="form-control"
                  value={addonForm.service_id}
                  onChange={(e) => setAddonForm({ ...addonForm, service_id: Number(e.target.value) })}
                  required
                >
                  {services.map((s) => (
                    <option key={s.id} value={s.id}>
                      {s.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="form-label">Nama Add-on *</label>
                <input
                  type="text"
                  className="form-control"
                  value={addonForm.name}
                  onChange={(e) => setAddonForm({ ...addonForm, name: e.target.value })}
                  placeholder="e.g. Cinematic FPV Drone Flight"
                  required
                />
              </div>

              <div>
                <label className="form-label">Harga Add-on (IDR Rupiah) *</label>
                <input
                  type="number"
                  step="50000"
                  className="form-control"
                  value={addonForm.base_price}
                  onChange={(e) => setAddonForm({ ...addonForm, base_price: Number(e.target.value) })}
                  required
                  style={{ fontWeight: 700, color: "#80ed99", fontSize: 16 }}
                />
                <div style={{ fontSize: 12, color: "var(--muted)", marginTop: 4 }}>
                  Terbaca: <b>{formatRupiah(addonForm.base_price)}</b>
                </div>
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: 10, marginTop: 12 }}>
                <button
                  type="button"
                  className="btn btn-secondary"
                  disabled={saving}
                  onClick={() => setAddonModalOpen(false)}
                >
                  Batal
                </button>
                <button type="submit" className="btn btn-primary" disabled={saving}>
                  {saving ? "Menyimpan..." : "Simpan Add-on"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  );
}
