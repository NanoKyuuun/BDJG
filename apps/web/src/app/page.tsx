"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { PublicNav } from "@/components/public-nav";
import { PublicFooter } from "@/components/public-footer";
import { BookingWizard } from "@/components/booking-wizard";

export default function Home() {
  const [activeCategory, setActiveCategory] = useState("ALL");
  const [timecode, setTimecode] = useState("00:00:00:00");
  const [preloaderDone, setPreloaderDone] = useState(false);

  useEffect(() => {
    const startTime = performance.now();
    const interval = setInterval(() => {
      const elapsed = performance.now() - startTime;
      const f = Math.floor((elapsed / 1000) * 24) % 24;
      const s = Math.floor(elapsed / 1000) % 60;
      const m = Math.floor(elapsed / 60000) % 60;
      const h = Math.floor(elapsed / 3600000) % 24;
      const p = (n: number) => (n < 10 ? "0" : "") + n;
      setTimecode(`${p(h)}:${p(m)}:${p(s)}:${p(f)}`);
    }, 41);

    const timer = setTimeout(() => {
      setPreloaderDone(true);
    }, 1200);

    return () => {
      clearInterval(interval);
      clearTimeout(timer);
    };
  }, []);

  const works = [
    {
      id: "danang-ayu",
      title: "The Royal Heritage Ceremony",
      client: "Raden Mas Danang & Ayu",
      category: "WEDDING",
      year: "2026",
      tag: "Wedding Cinema / 4K Master",
      image: "https://images.unsplash.com/photo-1519741497674-611481863552?q=80&w=1200&auto=format&fit=crop",
      aspect: "aspect-[16/10]",
    },
    {
      id: "savanna-whisper",
      title: "Savanna Whisper Campaign",
      client: "Lumina Apparel Tokyo",
      category: "COMMERCIAL",
      year: "2026",
      tag: "Brand Film / ARRI Alexa Mini",
      image: "https://images.unsplash.com/photo-1469334031218-e382a71b716b?q=80&w=1200&auto=format&fit=crop",
      aspect: "aspect-[4/3]",
    },
    {
      id: "mentawai-rhythm",
      title: "Guardians of Siberut",
      client: "National Geographic Creative",
      category: "DOCUMENTARY",
      year: "2025",
      tag: "Cultural Doc / 6K Raw",
      image: "https://images.unsplash.com/photo-1506744038136-46273834b3fb?q=80&w=1200&auto=format&fit=crop",
      aspect: "aspect-[16/10]",
    },
    {
      id: "velvet-hour",
      title: "Velvet Hour: Midnight Echoes",
      client: "Sony Music Entertainment",
      category: "MUSIC_VIDEO",
      year: "2026",
      tag: "Music Film / Anamorphic Cooke",
      image: "https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=1200&auto=format&fit=crop",
      aspect: "aspect-[4/3]",
    },
  ];

  const filteredWorks =
    activeCategory === "ALL"
      ? works
      : works.filter((w) => w.category === activeCategory);

  const categories = ["ALL", "COMMERCIAL", "WEDDING", "DOCUMENTARY", "MUSIC_VIDEO"];

  return (
    <div className="min-h-screen bg-[#090909] text-[#f2f1ed] selection:bg-[#5c7cff] selection:text-black font-sans">
      {/* Preloader Transition */}
      {!preloaderDone && (
        <div className="fixed inset-0 z-[100] bg-[#090909] flex flex-col items-center justify-center gap-4 transition-all duration-700">
          <div className="font-extrabold text-4xl tracking-tighter text-[#f2f1ed] animate-pulse">
            BDJG<sup className="text-sm font-mono text-[#5c7cff] ml-1">®</sup>
          </div>
          <div className="font-mono text-xs tracking-widest text-[#5c7cff]">
            SYSTEM_BOOT [{timecode}]
          </div>
        </div>
      )}

      {/* Navigation */}
      <PublicNav />

      {/* Hero Section */}
      <section className="relative min-h-[92vh] flex flex-col justify-end pt-32 pb-16 px-6 sm:px-12 border-b border-[#222222] overflow-hidden">
        {/* Background Ambient Glow */}
        <div className="absolute top-1/4 -right-40 w-96 h-96 bg-[#5c7cff]/10 rounded-full blur-3xl pointer-events-none" />
        <div className="absolute -bottom-20 -left-40 w-96 h-96 bg-[#3e9b6c]/5 rounded-full blur-3xl pointer-events-none" />

        <div className="max-w-7xl mx-auto w-full">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 font-mono text-xs tracking-widest text-[#777777] uppercase mb-8">
            <span className="flex items-center gap-2">
              <span className="w-2 h-2 rounded-full bg-[#3e9b6c] animate-ping" />
              Studio Available for Q4 2026 Production
            </span>
            <span className="text-[#a3a3a3]">REC / {timecode}</span>
          </div>

          <div className="space-y-2 mb-12">
            <h1 className="text-5xl sm:text-7xl lg:text-9xl font-extrabold tracking-tighter uppercase leading-[0.88] text-[#f2f1ed]">
              We Capture <br />
              <span className="text-transparent bg-clip-text bg-gradient-to-r from-[#5c7cff] via-[#85a0ff] to-[#f2f1ed] ml-0 sm:ml-16">
                Stories.
              </span>
            </h1>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-end pt-8 border-t border-[#222222]">
            <div className="lg:col-span-6 space-y-4">
              <p className="text-base sm:text-lg text-[#a3a3a3] max-w-xl leading-relaxed">
                Independent creative studio in Padang, Indonesia. Producing brand films, ceremonial documentaries, and cinematic imagery with uncompromising narrative craft.
              </p>
              <div className="flex flex-wrap items-center gap-3 pt-2">
                <Link
                  href="#book"
                  className="font-mono text-xs font-bold uppercase tracking-widest px-6 py-3.5 rounded bg-[#5c7cff] text-[#090909] hover:bg-[#7590ff] active:scale-[0.98] transition-all"
                >
                  Start a Project ↗
                </Link>
                <Link
                  href="/works"
                  className="font-mono text-xs uppercase tracking-widest px-6 py-3.5 rounded border border-[#333333] hover:border-[#666666] text-[#f2f1ed] transition-colors"
                >
                  Explore Works
                </Link>
              </div>
            </div>

            <div className="lg:col-span-6 grid grid-cols-3 gap-4 font-mono text-xs border-l border-[#222222] pl-6">
              <div>
                <div className="text-[#666666] uppercase mb-1">Pipeline</div>
                <div className="font-bold text-[#f2f1ed]">4K / 6K Raw</div>
              </div>
              <div>
                <div className="text-[#666666] uppercase mb-1">Color Grade</div>
                <div className="font-bold text-[#5c7cff]">DaVinci Master</div>
              </div>
              <div>
                <div className="text-[#666666] uppercase mb-1">Location</div>
                <div className="font-bold text-[#f2f1ed]">Padang (WIB)</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Marquee Strip */}
      <div className="border-b border-[#222222] py-3 bg-[#0c0c0c] overflow-hidden whitespace-nowrap font-mono text-xs uppercase tracking-widest text-[#777777]">
        <div className="flex gap-12 animate-marquee inline-block">
          <span>• COMMERCIAL FILM</span>
          <span>• CINEMATIC WEDDING</span>
          <span>• COLOR GRADING & MASTERING</span>
          <span>• DRONE FPV CINEMATOGRAPHY</span>
          <span>• EDITORIAL STILLS</span>
          <span>• BESPOKE SOUND DESIGN</span>
          <span>• BDJG® CREATIVE STUDIO</span>
        </div>
      </div>

      {/* Selected Works Showcase */}
      <section id="works" className="py-24 px-6 sm:px-12 max-w-7xl mx-auto">
        <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-12 mb-12 border-b border-[#222222]">
          <div>
            <div className="font-mono text-xs text-[#5c7cff] uppercase tracking-widest mb-2">Portfolio Directory</div>
            <h2 className="text-3xl sm:text-5xl font-extrabold tracking-tight text-[#f2f1ed]">
              Selected Stories & Films
            </h2>
          </div>

          {/* Filter Pills */}
          <div className="flex flex-wrap items-center gap-2 font-mono text-[11px] uppercase tracking-wider">
            {categories.map((cat) => (
              <button
                key={cat}
                type="button"
                onClick={() => setActiveCategory(cat)}
                className={`px-4 py-2 rounded-full border transition-colors ${
                  activeCategory === cat
                    ? "bg-[#f2f1ed] text-black border-[#f2f1ed] font-bold"
                    : "border-[#2a2a2a] text-[#888888] hover:border-[#555555] hover:text-[#f2f1ed]"
                }`}
              >
                {cat}
              </button>
            ))}
          </div>
        </div>

        {/* Works Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
          {filteredWorks.map((w) => (
            <div
              key={w.id}
              className="group cursor-pointer border border-[#222222] bg-[#121212] rounded-xl overflow-hidden hover:border-[#444444] transition-all duration-300"
            >
              <div className={`relative overflow-hidden bg-[#1a1a1a] ${w.aspect}`}>
                {/* Visual Image */}
                <div
                  className="w-full h-full bg-cover bg-center transition-transform duration-700 group-hover:scale-105 opacity-90 group-hover:opacity-100"
                  style={{ backgroundImage: `url(${w.image})` }}
                />
                <div className="absolute top-4 left-4 font-mono text-[10px] uppercase tracking-widest bg-black/70 backdrop-blur-md px-3 py-1 rounded text-[#5c7cff] border border-[#333333]">
                  {w.tag}
                </div>
                <div className="absolute bottom-4 right-4 font-mono text-[10px] uppercase tracking-widest bg-black/70 backdrop-blur-md px-3 py-1 rounded text-[#a3a3a3]">
                  {w.year}
                </div>
              </div>

              <div className="p-6">
                <div className="flex items-center justify-between gap-4 mb-2">
                  <h3 className="text-xl font-bold text-[#f2f1ed] group-hover:text-[#5c7cff] transition-colors">
                    {w.title}
                  </h3>
                  <span className="text-[#666666] group-hover:text-[#f2f1ed] group-hover:translate-x-1 transition-all">
                    ↗
                  </span>
                </div>
                <p className="text-xs text-[#888888] font-mono">
                  Client: {w.client}
                </p>
              </div>
            </div>
          ))}
        </div>

        <div className="text-center pt-16">
          <Link
            href="/works"
            className="inline-block font-mono text-xs uppercase tracking-widest px-8 py-3.5 rounded border border-[#333333] hover:border-[#666666] text-[#f2f1ed] transition-colors"
          >
            View Full Works Archive (48 Projects) →
          </Link>
        </div>
      </section>

      {/* Services Breakdown */}
      <section id="services" className="py-24 px-6 sm:px-12 bg-[#0c0c0c] border-y border-[#222222]">
        <div className="max-w-7xl mx-auto">
          <div className="max-w-2xl mb-16">
            <div className="font-mono text-xs text-[#5c7cff] uppercase tracking-widest mb-2">Production Capabilities</div>
            <h2 className="text-3xl sm:text-5xl font-extrabold tracking-tight text-[#f2f1ed] mb-4">
              Disciplines & Craft
            </h2>
            <p className="text-sm sm:text-base text-[#a3a3a3] leading-relaxed">
              We operate an end-to-end studio pipeline from initial treatment, storyboard, casting, and shoot production to precision color grading and sound design.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {[
              {
                num: "01",
                title: "Commercial Film",
                desc: "High-impact narrative commercials for brand identity, luxury hospitality, fashion lookbooks, and broadcast campaigns.",
                deliverables: ["16:9 4K Master", "9:16 Social Cuts", "Cinematic Trailer"],
              },
              {
                num: "02",
                title: "Cinematic Wedding",
                desc: "Emotionally resonant documentation of royal and heritage ceremonies with full multi-cam and FPV aerial coverage.",
                deliverables: ["Full Documentary Cut", "Same Day Edit (SDE)", "Highlight Film"],
              },
              {
                num: "03",
                title: "Editorial Stills",
                desc: "Studio portraiture, architectonic documentation, and editorial campaigns capturing raw textures and lighting.",
                deliverables: ["Curated Raw Culls", "Master Retouched Set", "Private Client Gallery"],
              },
              {
                num: "04",
                title: "Color & Post VFX",
                desc: "DaVinci Resolve ACES color mastering, dynamic sound mixing, subtitling conform, and visual effects cleanups.",
                deliverables: ["ProRes 4444XQ", "DCP Theater Deliverable", "Web H.265 / AV1"],
              },
            ].map((svc) => (
              <div
                key={svc.num}
                className="bg-[#121212] border border-[#222222] p-8 rounded-xl flex flex-col justify-between hover:border-[#5c7cff]/50 transition-colors"
              >
                <div>
                  <div className="font-mono text-xs text-[#5c7cff] uppercase tracking-widest mb-4">{svc.num}</div>
                  <h3 className="text-xl font-bold text-[#f2f1ed] mb-3">{svc.title}</h3>
                  <p className="text-xs text-[#a3a3a3] leading-relaxed mb-6">{svc.desc}</p>
                </div>
                <div className="pt-4 border-t border-[#1f1f1f]">
                  <div className="font-mono text-[10px] text-[#666666] uppercase mb-2">Key Deliverables</div>
                  <ul className="space-y-1 text-xs text-[#cccccc] font-mono">
                    {svc.deliverables.map((d) => (
                      <li key={d}>• {d}</li>
                    ))}
                  </ul>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Booking Form Wizard Section */}
      <section id="book" className="py-24 px-6 sm:px-12 max-w-5xl mx-auto">
        <div className="text-center max-w-xl mx-auto mb-16">
          <div className="font-mono text-xs text-[#5c7cff] uppercase tracking-widest mb-2">Start Production</div>
          <h2 className="text-3xl sm:text-5xl font-extrabold tracking-tight text-[#f2f1ed] mb-4">
            Book a Project
          </h2>
          <p className="text-sm text-[#a3a3a3] leading-relaxed">
            Tell us about your upcoming project or event. Our production desk will calculate availability, assign director treatments, and draft formal commercial terms.
          </p>
        </div>

        <BookingWizard />
      </section>

      {/* Footer */}
      <PublicFooter />
    </div>
  );
}
