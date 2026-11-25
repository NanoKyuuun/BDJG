"use client";

import Link from "next/link";
import { PublicNav } from "@/components/public-nav";
import { PublicFooter } from "@/components/public-footer";

export default function ServicesPage() {
  const serviceDisciplines = [
    {
      id: "commercial",
      title: "Commercial & Brand Cinema",
      tagline: "Narrative-driven visual identity for luxury, lifestyle, and global corporate brands.",
      scope: [
        "Creative Treatment, Concept & Scriptwriting",
        "Full Production Crew & Precision Lighting",
        "Multi-Location & Drone Aerial Filming",
        "Master 4K DCI Delivery & 9:16 Social Re-edits",
        "Custom Score Composition & Sound Design",
      ],
      packages: [
        { name: "Brand Highlight", price: "From Rp 20.000.000", days: "1-Day Shoot", team: "4-Person Unit" },
        { name: "Cinematic Campaign", price: "From Rp 45.000.000", days: "2 to 3-Day Shoot", team: "Full 8-Person Unit" },
      ],
    },
    {
      id: "wedding",
      title: "Royal & Cinematic Wedding Documentation",
      tagline: "Preserving sacred ancestral traditions and emotional vows with timeless cinematic elegance.",
      scope: [
        "Pre-Wedding Cinematic Narrative Short",
        "Multi-Camera 4K Ceremony & Reception Live Mixing",
        "FPV Aerial Grand Entrance Drone Coverage",
        "Same Day Edit (SDE) for Evening Reception",
        "Physical Hardcover Archival Box + SSD Handover",
      ],
      packages: [
        { name: "Heritage Document", price: "From Rp 25.000.000", days: "1-Day Ceremony", team: "5-Person Unit" },
        { name: "Royal Grandeur Master", price: "From Rp 50.000.000", days: "Full Weekend / 2-Day", team: "Lead Director + 8-Person Unit" },
      ],
    },
    {
      id: "photo",
      title: "Editorial, Fashion & Architecture Photo",
      tagline: "Medium format high-resolution stills for commercial print, digital lookbooks, and architechture.",
      scope: [
        "Studio Lighting Setup with Profoto & Broncolor",
        "Art Direction, Set Styling & Model Coordination",
        "High-End Beauty & Color Retouching (16-bit TIFF)",
        "Instant Tethered Client Review On-Set",
        "Private Digital Selection & Curation Gallery",
      ],
      packages: [
        { name: "Studio Lookbook", price: "From Rp 12.000.000", days: "Full Day Session", team: "Photographer + Digital Tech" },
        { name: "Architectural & Commercial", price: "From Rp 22.000.000", days: "2-Day Multi-Angle", team: "Lead Photographer + Assistant" },
      ],
    },
    {
      id: "post",
      title: "Color Grading, Finishing & VFX",
      tagline: "Precision DaVinci Resolve color grading on calibrated OLED monitoring and VFX finishing.",
      scope: [
        "ACES Color Pipeline & Camera LUT Creation",
        "Conform from RAW (REDCODE, ARRI RAW, ProRes)",
        "Sky Replacement, Wire Removal & Beauty Cleanup",
        "Dolby Atmos / 5.1 Surround Sound Mastering",
        "DCP Theater Package & Streaming Formats",
      ],
      packages: [
        { name: "Short Form Finishing", price: "From Rp 8.000.000", days: "3-5 Turnaround Days", team: "Colorist" },
        { name: "Feature / Series Master", price: "Custom Quote", days: "10-15 Turnaround Days", team: "Senior Colorist + VFX Artist" },
      ],
    },
  ];

  return (
    <div className="min-h-screen bg-public-canvas text-public-ink selection:bg-public-accent selection:text-black font-sans">
      <PublicNav />

      {/* Header */}
      <section className="pt-36 pb-16 px-6 sm:px-12 border-b border-public-line max-w-7xl mx-auto">
        <div className="max-w-3xl">
          <div className="font-mono text-xs text-public-accent uppercase tracking-widest mb-2">Capabilities Matrix</div>
          <h1 className="text-4xl sm:text-6xl font-extrabold tracking-tight text-public-ink mb-6">
            Production Disciplines & Service Tiers
          </h1>
          <p className="text-base sm:text-lg text-public-muted leading-relaxed">
            Every production at BDJG is managed with strict quality gates, dedicated director oversight, and guaranteed technical specifications.
          </p>
        </div>
      </section>

      {/* Disciplines Detailed List */}
      <section className="py-20 px-6 sm:px-12 max-w-7xl mx-auto space-y-16">
        {serviceDisciplines.map((d, idx) => (
          <div
            key={d.id}
            className="p-8 sm:p-12 rounded-2xl bg-public-panel border border-public-line grid grid-cols-1 lg:grid-cols-12 gap-8 items-start hover:border-public-control transition-colors"
          >
            <div className="lg:col-span-5 space-y-4">
              <div className="font-mono text-xs text-public-accent uppercase tracking-widest">Discipline 0{idx + 1}</div>
              <h2 className="text-2xl sm:text-3xl font-bold text-public-ink">{d.title}</h2>
              <p className="text-sm text-public-muted leading-relaxed">{d.tagline}</p>

              <div className="pt-4">
                <Link
                  href="/book"
                  className="inline-block font-mono text-xs font-bold uppercase tracking-widest px-6 py-3 rounded bg-public-accent text-public-inset hover:bg-public-accent-hover transition-colors"
                >
                  Commission Discipline ↗
                </Link>
              </div>
            </div>

            <div className="lg:col-span-7 space-y-6 lg:border-l lg:border-public-line lg:pl-8">
              <div>
                <h4 className="font-mono text-xs uppercase tracking-widest text-public-subtle mb-3">Included Scope & Standards</h4>
                <ul className="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-public-muted">
                  {d.scope.map((s) => (
                    <li key={s} className="flex items-start gap-2">
                      <span className="text-public-success font-bold">✓</span>
                      <span>{s}</span>
                    </li>
                  ))}
                </ul>
              </div>

              <div>
                <h4 className="font-mono text-xs uppercase tracking-widest text-public-subtle mb-3">Standard Package Tiers</h4>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {d.packages.map((pkg) => (
                    <div key={pkg.name} className="p-4 rounded-lg bg-public-raised border border-public-line">
                      <div className="font-bold text-sm text-public-ink">{pkg.name}</div>
                      <div className="font-mono text-xs text-public-accent font-semibold my-1">{pkg.price}</div>
                      <div className="font-mono text-[10px] text-public-subtle">{pkg.days} • {pkg.team}</div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        ))}
      </section>

      {/* CTA */}
      <section className="py-20 px-6 sm:px-12 bg-public-inset border-t border-public-line text-center">
        <div className="max-w-2xl mx-auto space-y-6">
          <h2 className="text-3xl font-extrabold text-public-ink">Require a Custom Commercial Proposal?</h2>
          <p className="text-sm text-public-muted">
            Submit your multi-day itinerary or bespoke agency script. We generate customized quotation lines with clear DP milestone schedules.
          </p>
          <Link
            href="/book"
            className="inline-block font-mono text-xs font-bold uppercase tracking-widest px-8 py-4 rounded bg-public-accent text-public-inset hover:bg-public-accent-hover transition-all"
          >
            Start Project Consultation ↗
          </Link>
        </div>
      </section>

      <PublicFooter />
    </div>
  );
}
