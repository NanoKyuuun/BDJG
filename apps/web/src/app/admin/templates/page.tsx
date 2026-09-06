"use client";

import { useState } from "react";

interface TemplateItem {
  id: string;
  name: string;
  category: "QUOTATION_TERMS" | "EMAIL_NOTICE" | "WHATSAPP_FOLLOWUP" | "INVOICE_NOTES";
  content: string;
  lastUpdated: string;
}

const INITIAL_TEMPLATES: TemplateItem[] = [
  {
    id: "tpl-1",
    name: "Standar Ketentuan Komersial Quotation Studio",
    category: "QUOTATION_TERMS",
    content: "1. Uang Muka (DP) sebesar 50% wajib diselesaikan untuk mengunci jadwal produksi dan kru.\n2. Pelunasan 50% dilakukan maksimal 3 hari setelah penyerahan draft preview video.\n3. Hak cipta master karya dialihkan penuh kepada Klien setelah pelunasan invoice.",
    lastUpdated: "12 Agu 2026",
  },
  {
    id: "tpl-2",
    name: "Notifikasi Penerbitan DP Invoice",
    category: "EMAIL_NOTICE",
    content: "Halo {{client_name}},\n\nQuotation Anda untuk proyek {{project_name}} telah disetujui. Invoice DP No. {{invoice_number}} sebesar {{amount}} telah diterbitkan dan dapat dibayarkan melalui portal pembayaran Duitku Sandbox.",
    lastUpdated: "15 Agu 2026",
  },
  {
    id: "tpl-3",
    name: "Follow-up WhatsApp Lead Baru",
    category: "WHATSAPP_FOLLOWUP",
    content: "Halo Kak {{client_name}}, salam sinema dari BDJG Creative Studio! Terima kasih telah mengajukan inquiry {{inquiry_ref}}. Produser kami ingin menanyakan detail rundown tanggal {{preferred_date}}.",
    lastUpdated: "18 Agu 2026",
  },
  {
    id: "tpl-4",
    name: "Catatan Kaki Invoice Pelunasan & Serah Terima File",
    category: "INVOICE_NOTES",
    content: "File master resolusi 4K ProRes 422HQ dan paket digital akan diserahkan melalui token download aman pada Client Portal setelah pembayaran dikonfirmasi sistem.",
    lastUpdated: "20 Agu 2026",
  },
];

export default function AdminTemplatesPage() {
  const [templates, setTemplates] = useState<TemplateItem[]>(INITIAL_TEMPLATES);
  const [activeCategory, setActiveCategory] = useState<string>("ALL");
  const [modalOpen, setModalOpen] = useState(false);
  const [editingTpl, setEditingTpl] = useState<TemplateItem | null>(null);

  const [form, setForm] = useState({
    name: "",
    category: "QUOTATION_TERMS" as TemplateItem["category"],
    content: "",
  });

  const filtered =
    activeCategory === "ALL"
      ? templates
      : templates.filter((t) => t.category === activeCategory);

  const handleOpenModal = (tpl?: TemplateItem) => {
    if (tpl) {
      setEditingTpl(tpl);
      setForm({
        name: tpl.name,
        category: tpl.category,
        content: tpl.content,
      });
    } else {
      setEditingTpl(null);
      setForm({
        name: "",
        category: "QUOTATION_TERMS",
        content: "",
      });
    }
    setModalOpen(true);
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingTpl) {
      setTemplates((prev) =>
        prev.map((t) =>
          t.id === editingTpl.id
            ? { ...t, name: form.name, category: form.category, content: form.content, lastUpdated: "Hari ini" }
            : t
        )
      );
    } else {
      const newTpl: TemplateItem = {
        id: "tpl-" + Date.now(),
        name: form.name,
        category: form.category,
        content: form.content,
        lastUpdated: "Hari ini",
      };
      setTemplates((prev) => [newTpl, ...prev]);
    }
    setModalOpen(false);
  };

  const handleDelete = (id: string) => {
    if (!confirm("Hapus template ini?")) return;
    setTemplates((prev) => prev.filter((t) => t.id !== id));
  };

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>📨 Operational & Commercial Templates</h1>
          <p>Kelola draf syarat komersial quotation, klausul invoice, draf email otomatis, dan pesan follow-up</p>
        </div>
        <button className="btn btn-primary" onClick={() => handleOpenModal()}>
          + Tambah Template Baru
        </button>
      </div>

      <div className="filter-bar" style={{ marginBottom: 20 }}>
        {["ALL", "QUOTATION_TERMS", "EMAIL_NOTICE", "WHATSAPP_FOLLOWUP", "INVOICE_NOTES"].map((cat) => (
          <button
            key={cat}
            className={`btn ${activeCategory === cat ? "btn-primary" : "btn-secondary"}`}
            onClick={() => setActiveCategory(cat)}
          >
            {cat.replace("_", " ")}
          </button>
        ))}
      </div>

      <div className="card">
        <div className="chead">
          <h3>Daftar Template Aktif ({filtered.length})</h3>
        </div>
        <div className="cbody" style={{ padding: 0 }}>
          <div style={{ overflowX: "auto" }}>
            <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
              <thead>
                <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                  <th style={{ padding: "12px 16px" }}>Nama Template</th>
                  <th style={{ padding: "12px 16px" }}>Kategori</th>
                  <th style={{ padding: "12px 16px" }}>Isi Teks / Klausul</th>
                  <th style={{ padding: "12px 16px" }}>Terakhir Diperbarui</th>
                  <th style={{ padding: "12px 16px", textAlign: "right" }}>Aksi</th>
                </tr>
              </thead>
              <tbody>
                {filtered.map((tpl) => (
                  <tr key={tpl.id} style={{ borderBottom: "1px solid rgba(255,255,255,0.04)" }}>
                    <td style={{ padding: "12px 16px", fontWeight: 700, color: "#FFFFFF" }}>
                      {tpl.name}
                    </td>
                    <td style={{ padding: "12px 16px" }}>
                      <span className="chip c-blue" style={{ fontSize: 10 }}>
                        {tpl.category}
                      </span>
                    </td>
                    <td style={{ padding: "12px 16px", maxWidth: 450 }}>
                      <pre
                        style={{
                          margin: 0,
                          fontSize: 11,
                          fontFamily: "var(--font-mono, monospace)",
                          color: "#cccccc",
                          whiteSpace: "pre-wrap",
                          background: "rgba(0,0,0,0.3)",
                          padding: "8px 10px",
                          borderRadius: 4,
                          border: "1px solid rgba(255,255,255,0.05)",
                        }}
                      >
                        {tpl.content}
                      </pre>
                    </td>
                    <td style={{ padding: "12px 16px", fontSize: 12, color: "var(--muted)" }}>
                      {tpl.lastUpdated}
                    </td>
                    <td style={{ padding: "12px 16px", textAlign: "right" }}>
                      <div style={{ display: "inline-flex", gap: 6 }}>
                        <button
                          className="btn btn-secondary"
                          style={{ padding: "4px 10px", fontSize: 11 }}
                          onClick={() => handleOpenModal(tpl)}
                        >
                          ✏️ Edit
                        </button>
                        <button
                          className="btn btn-secondary"
                          style={{ padding: "4px 10px", fontSize: 11, color: "#ff8b8b" }}
                          onClick={() => handleDelete(tpl.id)}
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
        </div>
      </div>

      {modalOpen && (
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
          onClick={() => setModalOpen(false)}
        >
          <div
            className="card"
            style={{ maxWidth: 550, width: "100%" }}
            onClick={(e) => e.stopPropagation()}
          >
            <div className="chead">
              <h3>{editingTpl ? `Edit Template: ${editingTpl.name}` : "Tambah Template Baru"}</h3>
              <button className="iconbtn" onClick={() => setModalOpen(false)}>✕</button>
            </div>
            <form onSubmit={handleSave} className="cbody" style={{ display: "flex", flexDirection: "column", gap: 14 }}>
              <div>
                <label className="form-label">Nama / Judul Template *</label>
                <input
                  type="text"
                  className="form-control"
                  value={form.name}
                  onChange={(e) => setForm({ ...form, name: e.target.value })}
                  placeholder="e.g. Syarat & Ketentuan Wedding 2026"
                  required
                />
              </div>

              <div>
                <label className="form-label">Kategori Template</label>
                <select
                  className="form-control"
                  value={form.category}
                  onChange={(e) => setForm({ ...form, category: e.target.value as any })}
                >
                  <option value="QUOTATION_TERMS">Quotation Commercial Terms</option>
                  <option value="EMAIL_NOTICE">Email Notification</option>
                  <option value="WHATSAPP_FOLLOWUP">WhatsApp Follow-up</option>
                  <option value="INVOICE_NOTES">Invoice Footnote</option>
                </select>
              </div>

              <div>
                <label className="form-label">Isi Teks Template *</label>
                <textarea
                  className="form-control"
                  rows={6}
                  value={form.content}
                  onChange={(e) => setForm({ ...form, content: e.target.value })}
                  placeholder="Tulis teks atau klausul... Anda dapat menggunakan placeholder {{client_name}}, {{amount}}, dsb."
                  required
                  style={{ fontFamily: "monospace", fontSize: 12 }}
                />
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: 10, marginTop: 12 }}>
                <button type="button" className="btn btn-secondary" onClick={() => setModalOpen(false)}>
                  Batal
                </button>
                <button type="submit" className="btn btn-primary">
                  Simpan Template
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  );
}
