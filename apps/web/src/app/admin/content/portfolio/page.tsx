"use client";

import { useState } from "react";

interface PortfolioItem {
  id: string;
  title: string;
  client: string;
  category: "COMMERCIAL" | "WEDDING" | "DOCUMENTARY" | "MUSIC_VIDEO";
  year: string;
  tag: string;
  director: string;
  dop: string;
  location: string;
  status: "PUBLISHED" | "DRAFT" | "ARCHIVED";
  image: string;
  synopsis: string;
}

const INITIAL_PORTFOLIO: PortfolioItem[] = [
  {
    id: "danang-ayu",
    title: "The Royal Heritage Ceremony",
    client: "Raden Mas Danang & Ayu",
    category: "WEDDING",
    year: "2026",
    tag: "Wedding Cinema / 4K Master",
    director: "Budi J. Gunawan",
    dop: "Golden Videographer",
    location: "Grand Ballroom Hotel Mulia, Jakarta",
    status: "PUBLISHED",
    image: "https://images.unsplash.com/photo-1519741497674-611481863552?q=80&w=1200&auto=format&fit=crop",
    synopsis: "A two-day royal traditional Javanese wedding documented across 5 camera angles with live multicam mixing and FPV drone entrance.",
  },
  {
    id: "savanna-whisper",
    title: "Savanna Whisper Brand Film",
    client: "Lumina Apparel Tokyo",
    category: "COMMERCIAL",
    year: "2026",
    tag: "Brand Film / ARRI Alexa Mini",
    director: "Budi J. Gunawan",
    dop: "Ahmad Faris",
    location: "Sumba Island & Padang Studio",
    status: "PUBLISHED",
    image: "https://images.unsplash.com/photo-1469334031218-e382a71b716b?q=80&w=1200&auto=format&fit=crop",
    synopsis: "High-fashion summer campaign capturing untamed landscapes and silk garment movement in 200fps high-speed cinematography.",
  },
  {
    id: "mentawai-rhythm",
    title: "Guardians of Siberut",
    client: "National Geographic Creative",
    category: "DOCUMENTARY",
    year: "2025",
    tag: "Cultural Doc / 6K Raw",
    director: "Fikri Ramadhan",
    dop: "Budi J. Gunawan",
    location: "Mentawai Archipelago",
    status: "PUBLISHED",
    image: "https://images.unsplash.com/photo-1506744038136-46273834b3fb?q=80&w=1200&auto=format&fit=crop",
    synopsis: "An intimate look at the indigenous Sikerei shamans of the Mentawai rain forest and their sacred relationship with island flora.",
  },
  {
    id: "velvet-hour",
    title: "Velvet Hour: Midnight Echoes",
    client: "Sony Music Entertainment",
    category: "MUSIC_VIDEO",
    year: "2026",
    tag: "Music Film / Anamorphic Cooke",
    director: "Ayu Lestari",
    dop: "Golden Videographer",
    location: "Padang Coastal Docks",
    status: "PUBLISHED",
    image: "https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=1200&auto=format&fit=crop",
    synopsis: "Neo-noir music video utilizing vintage Cooke Anamorphic lenses and heavy haze atmospheric lighting for an evocative visual rhythm.",
  },
];

export default function AdminPortfolioPage() {
  const [items, setItems] = useState<PortfolioItem[]>(INITIAL_PORTFOLIO);
  const [selectedCategory, setSelectedCategory] = useState<string>("ALL");
  const [modalOpen, setModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState<PortfolioItem | null>(null);

  const [form, setForm] = useState({
    title: "",
    client: "",
    category: "COMMERCIAL" as PortfolioItem["category"],
    year: "2026",
    tag: "",
    director: "",
    dop: "",
    location: "",
    status: "PUBLISHED" as PortfolioItem["status"],
    image: "",
    synopsis: "",
  });

  const filtered =
    selectedCategory === "ALL"
      ? items
      : items.filter((i) => i.category === selectedCategory);

  const handleOpenModal = (item?: PortfolioItem) => {
    if (item) {
      setEditingItem(item);
      setForm({ ...item });
    } else {
      setEditingItem(null);
      setForm({
        title: "",
        client: "",
        category: "COMMERCIAL",
        year: "2026",
        tag: "Brand Film / 4K Master",
        director: "Budi J. Gunawan",
        dop: "Golden Videographer",
        location: "Padang Studio",
        status: "PUBLISHED",
        image: "https://images.unsplash.com/photo-1519741497674-611481863552?q=80&w=1200&auto=format&fit=crop",
        synopsis: "",
      });
    }
    setModalOpen(true);
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingItem) {
      setItems((prev) =>
        prev.map((i) => (i.id === editingItem.id ? { ...form, id: editingItem.id } : i))
      );
    } else {
      const newItem: PortfolioItem = {
        ...form,
        id: "proj-" + Date.now(),
      };
      setItems((prev) => [newItem, ...prev]);
    }
    setModalOpen(false);
  };

  const handleDelete = (id: string) => {
    if (!confirm("Hapus proyek ini dari showcase publik?")) return;
    setItems((prev) => prev.filter((i) => i.id !== id));
  };

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>🖼️ Portfolio & Works CMS</h1>
          <p>Kelola etalase karya publik studio BDJG, atur featured highlight, poster film, dan kredit kru</p>
        </div>
        <button className="btn btn-primary" onClick={() => handleOpenModal()}>
          + Tambah Karya Portofolio
        </button>
      </div>

      <div className="filter-bar" style={{ marginBottom: 20 }}>
        {["ALL", "COMMERCIAL", "WEDDING", "DOCUMENTARY", "MUSIC_VIDEO"].map((cat) => (
          <button
            key={cat}
            className={`btn ${selectedCategory === cat ? "btn-primary" : "btn-secondary"}`}
            onClick={() => setSelectedCategory(cat)}
          >
            {cat}
          </button>
        ))}
      </div>

      <div className="card">
        <div className="chead">
          <h3>Daftar Karya Tayang ({filtered.length})</h3>
        </div>
        <div className="cbody" style={{ padding: 0 }}>
          <div style={{ overflowX: "auto" }}>
            <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
              <thead>
                <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                  <th style={{ padding: "12px 16px" }}>Karya / Cover</th>
                  <th style={{ padding: "12px 16px" }}>Klien</th>
                  <th style={{ padding: "12px 16px" }}>Kategori</th>
                  <th style={{ padding: "12px 16px" }}>Tahun / Lokasi</th>
                  <th style={{ padding: "12px 16px" }}>Status Publik</th>
                  <th style={{ padding: "12px 16px", textAlign: "right" }}>Aksi</th>
                </tr>
              </thead>
              <tbody>
                {filtered.map((item) => (
                  <tr
                    key={item.id}
                    style={{ borderBottom: "1px solid rgba(255,255,255,0.04)" }}
                  >
                    <td style={{ padding: "12px 16px" }}>
                      <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
                        <div
                          style={{
                            width: 60,
                            height: 38,
                            borderRadius: 4,
                            backgroundImage: `url(${item.image})`,
                            backgroundSize: "cover",
                            backgroundPosition: "center",
                            border: "1px solid rgba(255,255,255,0.1)",
                            flexShrink: 0,
                          }}
                        />
                        <div>
                          <div style={{ fontWeight: 700, color: "#FFFFFF" }}>{item.title}</div>
                          <div style={{ fontSize: 11, color: "var(--muted)" }}>{item.tag}</div>
                        </div>
                      </div>
                    </td>
                    <td style={{ padding: "12px 16px" }}>{item.client}</td>
                    <td style={{ padding: "12px 16px" }}>
                      <span className="chip c-blue" style={{ fontSize: 10 }}>{item.category}</span>
                    </td>
                    <td style={{ padding: "12px 16px", fontSize: 12 }}>
                      {item.year} • <span style={{ color: "var(--muted)" }}>{item.location}</span>
                    </td>
                    <td style={{ padding: "12px 16px" }}>
                      <span className={`chip ${item.status === "PUBLISHED" ? "c-green" : "c-black"}`} style={{ fontSize: 10 }}>
                        {item.status}
                      </span>
                    </td>
                    <td style={{ padding: "12px 16px", textAlign: "right" }}>
                      <div style={{ display: "inline-flex", gap: 6 }}>
                        <button
                          className="btn btn-secondary"
                          style={{ padding: "4px 10px", fontSize: 11 }}
                          onClick={() => handleOpenModal(item)}
                        >
                          ✏️ Edit
                        </button>
                        <button
                          className="btn btn-secondary"
                          style={{ padding: "4px 10px", fontSize: 11, color: "#ff8b8b" }}
                          onClick={() => handleDelete(item.id)}
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

      {/* Modal */}
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
            style={{ maxWidth: 600, width: "100%", maxHeight: "90vh", overflowY: "auto" }}
            onClick={(e) => e.stopPropagation()}
          >
            <div className="chead">
              <h3>{editingItem ? `Edit Karya: ${editingItem.title}` : "Tambah Karya Portofolio"}</h3>
              <button className="iconbtn" onClick={() => setModalOpen(false)}>✕</button>
            </div>
            <form onSubmit={handleSave} className="cbody" style={{ display: "flex", flexDirection: "column", gap: 14 }}>
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                <div>
                  <label className="form-label">Judul Film / Proyek *</label>
                  <input
                    type="text"
                    className="form-control"
                    value={form.title}
                    onChange={(e) => setForm({ ...form, title: e.target.value })}
                    required
                  />
                </div>
                <div>
                  <label className="form-label">Nama Klien / Brand *</label>
                  <input
                    type="text"
                    className="form-control"
                    value={form.client}
                    onChange={(e) => setForm({ ...form, client: e.target.value })}
                    required
                  />
                </div>
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 12 }}>
                <div>
                  <label className="form-label">Kategori</label>
                  <select
                    className="form-control"
                    value={form.category}
                    onChange={(e) => setForm({ ...form, category: e.target.value as any })}
                  >
                    <option value="COMMERCIAL">Commercial Film</option>
                    <option value="WEDDING">Wedding Cinema</option>
                    <option value="DOCUMENTARY">Documentary</option>
                    <option value="MUSIC_VIDEO">Music Video</option>
                  </select>
                </div>
                <div>
                  <label className="form-label">Tahun</label>
                  <input
                    type="text"
                    className="form-control"
                    value={form.year}
                    onChange={(e) => setForm({ ...form, year: e.target.value })}
                  />
                </div>
                <div>
                  <label className="form-label">Status Publik</label>
                  <select
                    className="form-control"
                    value={form.status}
                    onChange={(e) => setForm({ ...form, status: e.target.value as any })}
                  >
                    <option value="PUBLISHED">Published (Aktif)</option>
                    <option value="DRAFT">Draft</option>
                    <option value="ARCHIVED">Archived</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="form-label">Image URL / Thumbnail Cover</label>
                <input
                  type="url"
                  className="form-control"
                  value={form.image}
                  onChange={(e) => setForm({ ...form, image: e.target.value })}
                  required
                />
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                <div>
                  <label className="form-label">Director</label>
                  <input
                    type="text"
                    className="form-control"
                    value={form.director}
                    onChange={(e) => setForm({ ...form, director: e.target.value })}
                  />
                </div>
                <div>
                  <label className="form-label">Director of Photography (DOP)</label>
                  <input
                    type="text"
                    className="form-control"
                    value={form.dop}
                    onChange={(e) => setForm({ ...form, dop: e.target.value })}
                  />
                </div>
              </div>

              <div>
                <label className="form-label">Lokasi Produksi</label>
                <input
                  type="text"
                  className="form-control"
                  value={form.location}
                  onChange={(e) => setForm({ ...form, location: e.target.value })}
                  placeholder="e.g. Sumba Island & Padang Studio"
                />
              </div>

              <div>
                <label className="form-label">Sinopsis & Cerita Proyek</label>
                <textarea
                  className="form-control"
                  rows={3}
                  value={form.synopsis}
                  onChange={(e) => setForm({ ...form, synopsis: e.target.value })}
                  placeholder="Ringkasan narasi visual yang ditampilkan di modal portofolio..."
                />
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: 10, marginTop: 12 }}>
                <button type="button" className="btn btn-secondary" onClick={() => setModalOpen(false)}>
                  Batal
                </button>
                <button type="submit" className="btn btn-primary">
                  Simpan Karya
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  );
}
