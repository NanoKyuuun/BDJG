"use client";

import { useState } from "react";
import Link from "next/link";
import { PublicNav } from "@/components/public-nav";
import { PublicFooter } from "@/components/public-footer";

export default function WorksPage() {
  const [activeCategory, setActiveCategory] = useState("ALL");
  const projects = [
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
      image: "https://images.unsplash.com/photo-1519741497674-611481863552?q=80&w=1200&auto=format&fit=crop",
      synopsis: "A two-day royal traditional Javanese wedding documented across 5 camera angles with live multicam mixing, FPV drone entrance coverage, and full 4K HDR delivery.",
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
      image: "https://images.unsplash.com/photo-1506744038136-46273834b3fb?q=80&w=1200&auto=format&fit=crop",
      synopsis: "An intimate look at the indigenous Sikerei shamans of the Mentawai rain forest and their sacred relationship with island flora and ancestral spirits.",
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
      image: "https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=1200&auto=format&fit=crop",
      synopsis: "Neo-noir music video utilizing vintage Cooke Anamorphic lenses and heavy haze atmospheric lighting for an evocative visual rhythm.",
    },
    {
      id: "sumatra-coffee",
      title: "Origins: The Highland Harvest",
      client: "Kopi Solok Radjo",
      category: "COMMERCIAL",
      year: "2025",
      tag: "Commercial / Documentary Hybrid",
      director: "Budi J. Gunawan",
      dop: "Fikri Ramadhan",
      location: "Solok Highlands, West Sumatra",
      image: "https://images.unsplash.com/photo-1447933601403-0c6688de566e?q=80&w=1200&auto=format&fit=crop",
      synopsis: "Documenting third-generation specialty coffee farmers in the volcanic soil of Mount Talang, bringing specialty Arabica bean journeys to global cafes.",
    },
    {
      id: "araya-timeless",
      title: "Araya & Dimas: Coastal Vows",
      client: "Araya & Dimas",
      category: "WEDDING",
      year: "2026",
      tag: "Wedding Film / Sunset Master",
      director: "Ayu Lestari",
      dop: "Golden Videographer",
      location: "Mandeh Bay, West Sumatra",
      image: "https://images.unsplash.com/photo-1583939003579-730e3918a45a?q=80&w=1200&auto=format&fit=crop",
      synopsis: "Intimate coastal destination wedding captured entirely in golden hour natural light with bespoke acoustic score and custom color palette.",
    },
  ];

  const [selectedProject, setSelectedProject] = useState<(typeof projects)[number] | null>(null);
  const categories = ["ALL", "COMMERCIAL", "WEDDING", "DOCUMENTARY", "MUSIC_VIDEO"];

  const filtered =
    activeCategory === "ALL"
      ? projects
      : projects.filter((p) => p.category === activeCategory);

  return (
    <div className="min-h-screen bg-public-canvas text-public-ink selection:bg-public-accent selection:text-black font-sans">
      <PublicNav />

      {/* Header */}
      <section className="pt-36 pb-16 px-6 sm:px-12 border-b border-public-line max-w-7xl mx-auto">
        <div className="flex flex-col md:flex-row md:items-end justify-between gap-6">
          <div>
            <div className="font-mono text-xs text-public-accent uppercase tracking-widest mb-2">Portfolio Directory</div>
            <h1 className="text-4xl sm:text-6xl font-extrabold tracking-tight text-public-ink">
              Selected Works & Archives
            </h1>
          </div>

          <div className="flex flex-wrap gap-2 font-mono text-[11px] uppercase">
            {categories.map((cat) => (
              <button
                key={cat}
                type="button"
                onClick={() => setActiveCategory(cat)}
                className={`px-4 py-2 rounded-full border transition-colors ${
                  activeCategory === cat
                    ? "bg-public-ink text-black border-public-ink font-bold"
                    : "border-public-line text-public-subtle hover:border-public-control hover:text-public-ink"
                }`}
              >
                {cat}
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* Grid */}
      <section className="py-20 px-6 sm:px-12 max-w-7xl mx-auto">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {filtered.map((proj) => (
            <div
              key={proj.id}
              onClick={() => setSelectedProject(proj)}
              className="group cursor-pointer border border-public-line bg-public-panel rounded-xl overflow-hidden hover:border-public-accent/50 transition-all duration-300"
            >
              <div className="relative aspect-[16/10] overflow-hidden bg-public-raised">
                <div
                  className="w-full h-full bg-cover bg-center transition-transform duration-700 group-hover:scale-105 opacity-85 group-hover:opacity-100"
                  style={{ backgroundImage: `url(${proj.image})` }}
                />
                <div className="absolute top-3 left-3 font-mono text-[9px] uppercase tracking-widest bg-black/80 px-2.5 py-1 rounded text-public-accent border border-public-control">
                  {proj.category}
                </div>
                <div className="absolute bottom-3 right-3 font-mono text-[10px] bg-black/80 px-2.5 py-0.5 rounded text-public-muted">
                  {proj.year}
                </div>
              </div>

              <div className="p-6">
                <h3 className="font-bold text-lg text-public-ink group-hover:text-public-accent transition-colors mb-1">
                  {proj.title}
                </h3>
                <p className="text-xs font-mono text-public-subtle mb-3">Client: {proj.client}</p>
                <p className="text-xs text-public-muted line-clamp-2 leading-relaxed">
                  {proj.synopsis}
                </p>
                <div className="mt-4 pt-3 border-t border-public-line flex items-center justify-between font-mono text-[10px] text-public-subtle">
                  <span>{proj.location}</span>
                  <span className="text-public-accent group-hover:translate-x-0.5 transition-transform">Read Story ↗</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* Project Modal */}
      {selectedProject && (
        <div
          className="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-4 sm:p-8"
          onClick={() => setSelectedProject(null)}
        >
          <div
            className="bg-public-panel border border-public-control rounded-2xl max-w-3xl w-full overflow-hidden shadow-2xl"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="relative aspect-video bg-public-raised">
              <div
                className="w-full h-full bg-cover bg-center"
                style={{ backgroundImage: `url(${selectedProject.image})` }}
              />
              <button
                type="button"
                onClick={() => setSelectedProject(null)}
                className="absolute top-4 right-4 bg-black/70 hover:bg-black text-public-ink p-2 rounded-full border border-public-control transition-colors"
              >
                ✕
              </button>
            </div>
            <div className="p-8 space-y-6">
              <div>
                <div className="font-mono text-xs text-public-accent uppercase tracking-widest mb-1">
                  {selectedProject.tag} — {selectedProject.year}
                </div>
                <h2 className="text-2xl sm:text-3xl font-bold text-public-ink">{selectedProject.title}</h2>
                <div className="text-xs font-mono text-public-subtle mt-1">Client: {selectedProject.client}</div>
              </div>

              <p className="text-sm text-public-muted leading-relaxed">
                {selectedProject.synopsis}
              </p>

              <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-lg bg-public-raised border border-public-line font-mono text-xs">
                <div>
                  <div className="text-public-subtle uppercase text-[10px]">Director</div>
                  <div className="font-semibold text-public-ink">{selectedProject.director}</div>
                </div>
                <div>
                  <div className="text-public-subtle uppercase text-[10px]">Cinematography</div>
                  <div className="font-semibold text-public-ink">{selectedProject.dop}</div>
                </div>
                <div>
                  <div className="text-public-subtle uppercase text-[10px]">Location</div>
                  <div className="font-semibold text-public-ink">{selectedProject.location}</div>
                </div>
                <div>
                  <div className="text-public-subtle uppercase text-[10px]">Master Format</div>
                  <div className="font-semibold text-public-accent">4K UHD / DCI</div>
                </div>
              </div>

              <div className="flex items-center justify-between pt-4 border-t border-public-line">
                <button
                  type="button"
                  onClick={() => setSelectedProject(null)}
                  className="font-mono text-xs uppercase px-4 py-2 border border-public-control rounded text-public-muted hover:text-public-ink"
                >
                  Close
                </button>
                <Link
                  href="/book"
                  className="font-mono text-xs font-bold uppercase px-6 py-2.5 bg-public-accent text-public-inset rounded hover:bg-public-accent-hover"
                >
                  Commission Similar Project ↗
                </Link>
              </div>
            </div>
          </div>
        </div>
      )}

      <PublicFooter />
    </div>
  );
}
