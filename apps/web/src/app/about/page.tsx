"use client";

import Link from "next/link";
import { PublicNav } from "@/components/public-nav";
import { PublicFooter } from "@/components/public-footer";

export default function AboutPage() {
  const team = [
    {
      name: "Budi J. Gunawan",
      role: "Creative Director & Founder",
      focus: "Cinematic Narrative, Creative Direction, Commercial Strategy",
      image: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=600&auto=format&fit=crop",
    },
    {
      name: "Ahmad Faris",
      role: "Lead Director of Photography",
      focus: "ARRI/RED Large Format Operation, Optical Systems, Precision Gaffer",
      image: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=600&auto=format&fit=crop",
    },
    {
      name: "Ayu Lestari",
      role: "Head of Post-Production & Colorist",
      focus: "DaVinci Resolve ACES Mastering, Narrative Editing, Sound Design",
      image: "https://images.unsplash.com/photo-1517841905240-472988babdf9?q=80&w=600&auto=format&fit=crop",
    },
    {
      name: "Fikri Ramadhan",
      role: "Lead Documentary Cinematographer & Drone Pilot",
      focus: "High-Speed FPV Flight, Remote Expeditions, Cultural Narrative",
      image: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=600&auto=format&fit=crop",
    },
  ];

  return (
    <div className="min-h-screen bg-public-canvas text-public-ink selection:bg-public-accent selection:text-black font-sans">
      <PublicNav />

      {/* Hero */}
      <section className="pt-36 pb-20 px-6 sm:px-12 border-b border-public-line max-w-7xl mx-auto">
        <div className="max-w-3xl space-y-6">
          <div className="font-mono text-xs text-public-accent uppercase tracking-widest">Studio Heritage & Mission</div>
          <h1 className="text-4xl sm:text-6xl font-extrabold tracking-tight text-public-ink leading-[1.05]">
            Rooted in West Sumatra. <br />
            <span className="text-transparent bg-clip-text bg-gradient-to-r from-public-accent to-public-ink">
              Crafting for the Global Eye.
            </span>
          </h1>
          <p className="text-base sm:text-lg text-public-muted leading-relaxed">
            BDJG was founded on the belief that cinema should not merely record an event; it must translate human soul, architectural rhythm, and ancestral emotion into lasting cinematic artifacts.
          </p>
        </div>
      </section>

      {/* Studio Philosophy Grid */}
      <section className="py-24 px-6 sm:px-12 max-w-7xl mx-auto border-b border-public-line">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div className="p-8 rounded-xl bg-public-panel border border-public-line space-y-4">
            <div className="font-mono text-xs text-public-accent uppercase tracking-widest">Principle 01</div>
            <h3 className="text-xl font-bold text-public-ink">Authentic Narrative Over Cliché</h3>
            <p className="text-xs text-public-muted leading-relaxed">
              We reject formulaic cookie-cutter video templates. Every brand campaign and wedding celebration has a distinct cadence, texture, and emotional core that guides every camera lens choice.
            </p>
          </div>

          <div className="p-8 rounded-xl bg-public-panel border border-public-line space-y-4">
            <div className="font-mono text-xs text-public-accent uppercase tracking-widest">Principle 02</div>
            <h3 className="text-xl font-bold text-public-ink">Technical Purity in RAW</h3>
            <p className="text-xs text-public-muted leading-relaxed">
              From 6K RAW high dynamic range capture to 16-bit uncompressed color pipelines, we treat digital sensors with the discipline of classical photochemical film stocks.
            </p>
          </div>

          <div className="p-8 rounded-xl bg-public-panel border border-public-line space-y-4">
            <div className="font-mono text-xs text-public-accent uppercase tracking-widest">Principle 03</div>
            <h3 className="text-xl font-bold text-public-ink">Transparent Commercial Operations</h3>
            <p className="text-xs text-public-muted leading-relaxed">
              Our custom-built studio management platform ensures clients have complete real-time visibility over production schedules, timestamped preview cuts, revision rounds, and verified deliverables.
            </p>
          </div>
        </div>
      </section>

      {/* Core Creative Team */}
      <section className="py-24 px-6 sm:px-12 max-w-7xl mx-auto">
        <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16 pb-8 border-b border-public-line">
          <div>
            <div className="font-mono text-xs text-public-accent uppercase tracking-widest mb-2">The Unit</div>
            <h2 className="text-3xl sm:text-4xl font-extrabold text-public-ink">Creative Leads & Directors</h2>
          </div>
          <div className="font-mono text-xs text-public-subtle">PADANG STUDIO HQ • 14 CORE CREW</div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          {team.map((member) => (
            <div key={member.name} className="group space-y-4">
              <div className="relative aspect-[3/4] overflow-hidden rounded-xl bg-public-raised border border-public-line">
                <div
                  className="w-full h-full bg-cover bg-center grayscale group-hover:grayscale-0 transition-all duration-500 group-hover:scale-105"
                  style={{ backgroundImage: `url(${member.image})` }}
                />
              </div>
              <div>
                <h3 className="font-bold text-lg text-public-ink">{member.name}</h3>
                <div className="font-mono text-xs text-public-accent mb-2">{member.role}</div>
                <p className="text-xs text-public-subtle leading-relaxed">{member.focus}</p>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* Bottom CTA */}
      <section className="py-20 px-6 sm:px-12 bg-public-inset border-t border-public-line text-center">
        <div className="max-w-xl mx-auto space-y-6">
          <h2 className="text-3xl font-bold text-public-ink">Have a Vision Ready to Film?</h2>
          <p className="text-sm text-public-muted">
            Collaborate with our directors and cinematographers to produce your next landmark campaign or celebration.
          </p>
          <Link
            href="/book"
            className="inline-block font-mono text-xs font-bold uppercase tracking-widest px-8 py-3.5 rounded bg-public-accent text-public-inset hover:bg-public-accent-hover"
          >
            Initiate Project Treatment ↗
          </Link>
        </div>
      </section>

      <PublicFooter />
    </div>
  );
}
