"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { usePathname } from "next/navigation";

export function PublicNav() {
  const [scrolled, setScrolled] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  const pathname = usePathname();

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 30);
    };
    window.addEventListener("scroll", handleScroll, { passive: true });
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  const navLinks = [
    { label: "Works", href: "/works" },
    { label: "Services", href: "/services" },
    { label: "About", href: "/about" },
    { label: "Studio", href: "/studio" },
    { label: "Contact", href: "/contact" },
  ];

  return (
    <>
      <header
        className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
          scrolled
            ? "bg-[#090909]/90 backdrop-blur-md border-b border-[#222222] py-3.5 px-6 sm:px-12"
            : "bg-transparent py-6 px-6 sm:px-12"
        }`}
      >
        <div className="max-w-7xl mx-auto flex items-center justify-between">
          <Link href="/" className="flex items-center gap-2 group">
            <span className="font-extrabold text-xl tracking-tight text-[#f2f1ed] group-hover:text-[#5c7cff] transition-colors">
              BDJG<sup className="text-[10px] font-mono ml-0.5 text-[#5c7cff]">®</sup>
            </span>
            <span className="hidden sm:inline-block text-[11px] font-mono tracking-widest text-[#777777] uppercase ml-3 pl-3 border-l border-[#2a2a2a]">
              Visual Studio / Padang
            </span>
          </Link>

          {/* Desktop Nav */}
          <nav className="hidden md:flex items-center gap-8 font-mono text-[11px] tracking-widest uppercase">
            {navLinks.map((link) => (
              <Link
                key={link.href}
                href={link.href}
                className={`transition-colors relative py-1 ${
                  pathname === link.href
                    ? "text-[#5c7cff] font-semibold"
                    : "text-[#a3a3a3] hover:text-[#f2f1ed]"
                }`}
              >
                {link.label}
                {pathname === link.href && (
                  <span className="absolute bottom-0 left-0 right-0 h-[1.5px] bg-[#5c7cff]" />
                )}
              </Link>
            ))}
          </nav>

          <div className="flex items-center gap-3">
            <Link
              href="/login"
              className="text-[11px] font-mono tracking-widest uppercase text-[#a3a3a3] hover:text-[#f2f1ed] px-3 py-2 transition-colors"
            >
              Client Portal
            </Link>
            <Link
              href="/book"
              className="inline-flex items-center justify-center font-mono text-[11px] font-bold tracking-widest uppercase px-4 py-2.5 rounded bg-[#5c7cff] text-[#090909] hover:bg-[#7590ff] active:scale-[0.98] transition-all"
            >
              Book Project ↗
            </Link>
            <button
              type="button"
              onClick={() => setMenuOpen(!menuOpen)}
              className="md:hidden p-2 text-[#a3a3a3] hover:text-[#f2f1ed] border border-[#2a2a2a] rounded"
              aria-label="Toggle menu"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {menuOpen ? (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                ) : (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                )}
              </svg>
            </button>
          </div>
        </div>
      </header>

      {/* Mobile Drawer */}
      {menuOpen && (
        <div className="fixed inset-0 z-40 bg-[#090909]/95 backdrop-blur-xl flex flex-col justify-center px-8 md:hidden">
          <div className="space-y-6 font-bold text-3xl text-[#f2f1ed]">
            {navLinks.map((link) => (
              <div key={link.href}>
                <Link
                  href={link.href}
                  onClick={() => setMenuOpen(false)}
                  className="block hover:text-[#5c7cff] transition-colors"
                >
                  {link.label}
                </Link>
              </div>
            ))}
            <div className="pt-6 border-t border-[#222222]">
              <Link
                href="/login"
                onClick={() => setMenuOpen(false)}
                className="block text-xl text-[#a3a3a3] hover:text-[#f2f1ed] mb-4"
              >
                Client Portal ↗
              </Link>
              <Link
                href="/book"
                onClick={() => setMenuOpen(false)}
                className="inline-block font-mono text-sm font-bold tracking-widest uppercase px-6 py-3 rounded bg-[#5c7cff] text-[#090909]"
              >
                Book a Project ↗
              </Link>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
