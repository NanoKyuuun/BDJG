"use client";

import { useState } from "react";

interface Testimonial {
  id: string;
  client_name: string;
  role_title: string;
  company_or_project: string;
  quote: string;
  rating: number;
  featured: boolean;
}

const INITIAL_TESTIMONIALS: Testimonial[] = [
  {
    id: "t1",
    client_name: "Raden Mas Danang",
    role_title: "Groom & Royal Patron",
    company_or_project: "Danang & Ayu Royal Wedding",
    quote: "BDJG menangkap momen sakral pernikahan kami dengan keanggunan sinematik yang luar biasa. Setiap sudut framing dan warna grading terasa seperti film layar lebar.",
    rating: 5,
    featured: true,
  },
  {
    id: "t2",
    client_name: "Kenji Takahashi",
    role_title: "Brand Director",
    company_or_project: "Lumina Apparel Tokyo",
    quote: "The visual discipline and rapid delivery from BDJG in capturing our Summer lookbook was world-class. Uncompromising 4K RAW quality.",
    rating: 5,
    featured: true,
  },
  {
    id: "t3",
    client_name: "Sarah Jenkins",
    role_title: "Executive Producer",
    company_or_project: "National Geographic Creative Expeditions",
    quote: "Filming in remote Mentawai rainforests requires extreme agility. The BDJG camera unit operated seamlessly with deep cultural respect.",
    rating: 5,
    featured: true,
  },
];

export default function AdminTestimonialsPage() {
  const [testimonials, setTestimonials] = useState<Testimonial[]>(INITIAL_TESTIMONIALS);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState<Testimonial | null>(null);

  const [form, setForm] = useState({
    client_name: "",
    role_title: "",
    company_or_project: "",
    quote: "",
    rating: 5,
    featured: true,
  });

  const handleOpenModal = (item?: Testimonial) => {
    if (item) {
      setEditingItem(item);
      setForm({ ...item });
    } else {
      setEditingItem(null);
      setForm({
        client_name: "",
        role_title: "",
        company_or_project: "",
        quote: "",
        rating: 5,
        featured: true,
      });
    }
    setModalOpen(true);
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingItem) {
      setTestimonials((prev) =>
        prev.map((t) => (t.id === editingItem.id ? { ...form, id: editingItem.id } : t))
      );
    } else {
      const newItem: Testimonial = {
        ...form,
        id: "t-" + Date.now(),
      };
      setTestimonials((prev) => [newItem, ...prev]);
    }
    setModalOpen(false);
  };

  const handleDelete = (id: string) => {
    if (!confirm("Hapus testimoni ini?")) return;
    setTestimonials((prev) => prev.filter((t) => t.id !== id));
  };

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>⭐ Client Reviews & Testimonials</h1>
          <p>Kelola testimoni resmi klien, ulasan brand, rating bintang, dan kurasi proof of trust untuk landing page publik</p>
        </div>
        <button className="btn btn-primary" onClick={() => handleOpenModal()}>
          + Tambah Testimoni Baru
        </button>
      </div>

      <div className="card">
        <div className="chead">
          <h3>Testimoni Terbit ({testimonials.length})</h3>
        </div>
        <div className="cbody" style={{ padding: 0 }}>
          <div style={{ overflowX: "auto" }}>
            <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
              <thead>
                <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                  <th style={{ padding: "12px 16px" }}>Klien / Proyek</th>
                  <th style={{ padding: "12px 16px" }}>Ulasan / Kutipan</th>
                  <th style={{ padding: "12px 16px" }}>Rating</th>
                  <th style={{ padding: "12px 16px" }}>Featured</th>
                  <th style={{ padding: "12px 16px", textAlign: "right" }}>Aksi</th>
                </tr>
              </thead>
              <tbody>
                {testimonials.map((t) => (
                  <tr
                    key={t.id}
                    style={{ borderBottom: "1px solid rgba(255,255,255,0.04)" }}
                  >
                    <td style={{ padding: "12px 16px" }}>
                      <div style={{ fontWeight: 700, color: "#FFFFFF" }}>{t.client_name}</div>
                      <div style={{ fontSize: 11, color: "var(--accent)" }}>{t.role_title} • {t.company_or_project}</div>
                    </td>
                    <td style={{ padding: "12px 16px", maxWidth: 400 }}>
                      <span style={{ fontStyle: "italic", color: "#cccccc" }}>
                        &ldquo;{t.quote}&rdquo;
                      </span>
                    </td>
                    <td style={{ padding: "12px 16px" }}>
                      <span style={{ color: "#ffd166" }}>
                        {"★".repeat(t.rating)}
                      </span>
                    </td>
                    <td style={{ padding: "12px 16px" }}>
                      {t.featured ? (
                        <span className="chip c-green" style={{ fontSize: 10 }}>Featured</span>
                      ) : (
                        <span className="chip c-black" style={{ fontSize: 10 }}>Standard</span>
                      )}
                    </td>
                    <td style={{ padding: "12px 16px", textAlign: "right" }}>
                      <div style={{ display: "inline-flex", gap: 6 }}>
                        <button
                          className="btn btn-secondary"
                          style={{ padding: "4px 10px", fontSize: 11 }}
                          onClick={() => handleOpenModal(t)}
                        >
                          ✏️ Edit
                        </button>
                        <button
                          className="btn btn-secondary"
                          style={{ padding: "4px 10px", fontSize: 11, color: "#ff8b8b" }}
                          onClick={() => handleDelete(t.id)}
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
            style={{ maxWidth: 500, width: "100%" }}
            onClick={(e) => e.stopPropagation()}
          >
            <div className="chead">
              <h3>{editingItem ? `Edit Testimoni: ${editingItem.client_name}` : "Tambah Testimoni"}</h3>
              <button className="iconbtn" onClick={() => setModalOpen(false)}>✕</button>
            </div>
            <form onSubmit={handleSave} className="cbody" style={{ display: "flex", flexDirection: "column", gap: 14 }}>
              <div>
                <label className="form-label">Nama Klien *</label>
                <input
                  type="text"
                  className="form-control"
                  value={form.client_name}
                  onChange={(e) => setForm({ ...form, client_name: e.target.value })}
                  placeholder="e.g. Raden Mas Danang"
                  required
                />
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                <div>
                  <label className="form-label">Jabatan / Relasi</label>
                  <input
                    type="text"
                    className="form-control"
                    value={form.role_title}
                    onChange={(e) => setForm({ ...form, role_title: e.target.value })}
                    placeholder="e.g. Groom / Brand Director"
                  />
                </div>
                <div>
                  <label className="form-label">Perusahaan / Nama Proyek</label>
                  <input
                    type="text"
                    className="form-control"
                    value={form.company_or_project}
                    onChange={(e) => setForm({ ...form, company_or_project: e.target.value })}
                    placeholder="e.g. Royal Wedding 2026"
                  />
                </div>
              </div>

              <div>
                <label className="form-label">Ulasan / Pernyataan Klien *</label>
                <textarea
                  className="form-control"
                  rows={4}
                  value={form.quote}
                  onChange={(e) => setForm({ ...form, quote: e.target.value })}
                  placeholder="Kutipan testimoni kepuasan hasil karya studio..."
                  required
                />
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                <div>
                  <label className="form-label">Rating Bintang</label>
                  <select
                    className="form-control"
                    value={form.rating}
                    onChange={(e) => setForm({ ...form, rating: Number(e.target.value) })}
                  >
                    <option value={5}>★★★★★ (5 Bintang)</option>
                    <option value={4}>★★★★☆ (4 Bintang)</option>
                    <option value={3}>★★★☆☆ (3 Bintang)</option>
                  </select>
                </div>
                <div style={{ display: "flex", alignItems: "center", gap: 8, marginTop: 24 }}>
                  <input
                    type="checkbox"
                    id="featured-toggle"
                    checked={form.featured}
                    onChange={(e) => setForm({ ...form, featured: e.target.checked })}
                  />
                  <label htmlFor="featured-toggle" style={{ fontSize: 13, color: "#f2f1ed" }}>
                    Tampilkan di Landing Page
                  </label>
                </div>
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: 10, marginTop: 12 }}>
                <button type="button" className="btn btn-secondary" onClick={() => setModalOpen(false)}>
                  Batal
                </button>
                <button type="submit" className="btn btn-primary">
                  Simpan Testimoni
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  );
}
