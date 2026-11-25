"use client";

import Link from "next/link";
import { useEffect, useState } from "react";

export function PublicFooter() {
  const [padangTime, setPadangTime] = useState<string>("Padang / 00:00:00 WIB");

  useEffect(() => {
    const updateTime = () => {
      try {
        const timeStr = new Intl.DateTimeFormat("en-GB", {
          timeZone: "Asia/Jakarta",
          hour: "2-digit",
          minute: "2-digit",
          second: "2-digit",
          hour12: false,
        }).format(new Date());
        setPadangTime(`Padang / ${timeStr} WIB`);
      } catch {
        setPadangTime("Padang / Indonesia");
      }
    };
    updateTime();
    const interval = setInterval(updateTime, 1000);
    return () => clearInterval(interval);
  }, []);

  return (
    <footer className="bg-public-inset border-t border-public-line text-public-ink pt-16 pb-12 px-[var(--public-gutter)]">
      <div className="grid grid-cols-1 md:grid-cols-5 gap-12 mb-16">
        <div className="md:col-span-2 space-y-4">
          <div className="flex items-center gap-2">
            <span className="font-extrabold text-2xl tracking-tight text-public-ink">
              BDJG<sup className="text-xs font-mono ml-0.5 text-public-accent">®</sup>
            </span>
          </div>
          <p className="text-sm text-public-muted max-w-sm leading-relaxed">
            Independent creative studio specialized in commercial film, cinematic wedding documentation, visual effects, and high-fidelity post production.
          </p>
          <div className="font-mono text-xs text-public-subtle tracking-wider">
            PADANG — SUMATERA BARAT, INDONESIA
          </div>
        </div>

        <div>
          <h5 className="font-mono text-[11px] uppercase tracking-widest text-public-accent mb-4">Sitemap</h5>
          <ul className="space-y-2.5 text-sm text-public-muted">
            <li><Link href="/works" className="hover:text-public-ink transition-colors">Works & Portfolio</Link></li>
            <li><Link href="/services" className="hover:text-public-ink transition-colors">Services & Packages</Link></li>
            <li><Link href="/about" className="hover:text-public-ink transition-colors">About the Studio</Link></li>
            <li><Link href="/studio" className="hover:text-public-ink transition-colors">Gear & Facilities</Link></li>
            <li><Link href="/book" className="hover:text-public-ink transition-colors">Book a Project</Link></li>
          </ul>
        </div>

        <div>
          <h5 className="font-mono text-[11px] uppercase tracking-widest text-public-accent mb-4">Portals</h5>
          <ul className="space-y-2.5 text-sm text-public-muted">
            <li><Link href="/login" className="hover:text-public-ink transition-colors">Client Workspace</Link></li>
            <li><Link href="/login" className="hover:text-public-ink transition-colors">Worker Portal</Link></li>
            <li><Link href="/login" className="hover:text-public-ink transition-colors">Studio Management</Link></li>
          </ul>
        </div>

        <div>
          <h5 className="font-mono text-[11px] uppercase tracking-widest text-public-accent mb-4">Direct Inquiries</h5>
          <ul className="space-y-2.5 text-sm text-public-muted">
            <li><a href="mailto:hello@bdjg.studio" className="hover:text-public-ink transition-colors">hello@bdjg.studio</a></li>
            <li><a href="tel:+6281234567890" className="hover:text-public-ink transition-colors">+62 812-3456-7890</a></li>
            <li><a href="https://instagram.com" target="_blank" rel="noopener noreferrer" className="hover:text-public-ink transition-colors">Instagram ↗</a></li>
            <li><a href="https://vimeo.com" target="_blank" rel="noopener noreferrer" className="hover:text-public-ink transition-colors">Vimeo ↗</a></li>
          </ul>
        </div>
      </div>

      <div className="pt-8 border-t border-public-raised flex flex-col sm:flex-row items-center justify-between gap-4 font-mono text-[11px] tracking-widest text-public-subtle">
        <div>© 2026 BDJG CREATIVE STUDIO — ALL STORIES RESERVED</div>
        <div className="text-public-muted">{padangTime}</div>
        <div>RELEASE D / BUILD 2026.09</div>
      </div>
    </footer>
  );
}
