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
    <footer className="bg-[#0c0c0c] border-t border-[#222222] text-[#f2f1ed] pt-16 pb-12 px-6 sm:px-12">
      <div className="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-5 gap-12 mb-16">
        <div className="md:col-span-2 space-y-4">
          <div className="flex items-center gap-2">
            <span className="font-extrabold text-2xl tracking-tight text-[#f2f1ed]">
              BDJG<sup className="text-xs font-mono ml-0.5 text-[#5c7cff]">®</sup>
            </span>
          </div>
          <p className="text-sm text-[#a3a3a3] max-w-sm leading-relaxed">
            Independent creative studio specialized in commercial film, cinematic wedding documentation, visual effects, and high-fidelity post production.
          </p>
          <div className="font-mono text-xs text-[#666666] tracking-wider">
            PADANG — SUMATERA BARAT, INDONESIA
          </div>
        </div>

        <div>
          <h5 className="font-mono text-[11px] uppercase tracking-widest text-[#5c7cff] mb-4">Sitemap</h5>
          <ul className="space-y-2.5 text-sm text-[#a3a3a3]">
            <li><Link href="/works" className="hover:text-[#f2f1ed] transition-colors">Works & Portfolio</Link></li>
            <li><Link href="/services" className="hover:text-[#f2f1ed] transition-colors">Services & Packages</Link></li>
            <li><Link href="/about" className="hover:text-[#f2f1ed] transition-colors">About the Studio</Link></li>
            <li><Link href="/studio" className="hover:text-[#f2f1ed] transition-colors">Gear & Facilities</Link></li>
            <li><Link href="/book" className="hover:text-[#f2f1ed] transition-colors">Book a Project</Link></li>
          </ul>
        </div>

        <div>
          <h5 className="font-mono text-[11px] uppercase tracking-widest text-[#5c7cff] mb-4">Portals</h5>
          <ul className="space-y-2.5 text-sm text-[#a3a3a3]">
            <li><Link href="/login" className="hover:text-[#f2f1ed] transition-colors">Client Workspace</Link></li>
            <li><Link href="/login" className="hover:text-[#f2f1ed] transition-colors">Worker Portal</Link></li>
            <li><Link href="/login" className="hover:text-[#f2f1ed] transition-colors">Studio Management</Link></li>
          </ul>
        </div>

        <div>
          <h5 className="font-mono text-[11px] uppercase tracking-widest text-[#5c7cff] mb-4">Direct Inquiries</h5>
          <ul className="space-y-2.5 text-sm text-[#a3a3a3]">
            <li><a href="mailto:hello@bdjg.studio" className="hover:text-[#f2f1ed] transition-colors">hello@bdjg.studio</a></li>
            <li><a href="tel:+6281234567890" className="hover:text-[#f2f1ed] transition-colors">+62 812-3456-7890</a></li>
            <li><a href="https://instagram.com" target="_blank" rel="noopener noreferrer" className="hover:text-[#f2f1ed] transition-colors">Instagram ↗</a></li>
            <li><a href="https://vimeo.com" target="_blank" rel="noopener noreferrer" className="hover:text-[#f2f1ed] transition-colors">Vimeo ↗</a></li>
          </ul>
        </div>
      </div>

      <div className="max-w-7xl mx-auto pt-8 border-t border-[#1a1a1a] flex flex-col sm:flex-row items-center justify-between gap-4 font-mono text-[11px] tracking-widest text-[#666666]">
        <div>© 2026 BDJG CREATIVE STUDIO — ALL STORIES RESERVED</div>
        <div className="text-[#a3a3a3]">{padangTime}</div>
        <div>RELEASE D / BUILD 2026.09</div>
      </div>
    </footer>
  );
}
