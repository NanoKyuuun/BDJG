"use client";

import Link from "next/link";
import { useState } from "react";
import { usePathname } from "next/navigation";

const navLinks = [
  { label: "Works", href: "/works" },
  { label: "Services", href: "/services" },
  { label: "About", href: "/about" },
  { label: "Studio", href: "/studio" },
  { label: "Contact", href: "/contact" },
];

const bookLinkClass = "inline-flex min-h-10 items-center justify-center whitespace-nowrap rounded-full bg-public-accent px-4 font-mono text-[11px] font-bold uppercase tracking-wide text-public-inset transition-colors hover:bg-public-ink";

export function PublicNav() {
  const [menuOpen, setMenuOpen] = useState(false);
  const pathname = usePathname();

  return (
    <>
      <header className="fixed inset-x-0 top-0 z-50 border-b border-public-line bg-public-canvas">
        <div className="flex h-[var(--public-nav-height)] items-center justify-between gap-3 px-[var(--public-gutter)] lg:grid lg:grid-cols-[1fr_auto_1fr]">
          <Link
            href="/"
            aria-label="BDJG Studio — Home"
            aria-current={pathname === "/" ? "page" : undefined}
            className="w-fit text-xl font-extrabold tracking-tight text-public-ink transition-colors hover:text-public-accent"
          >
            BDJG<sup className="ml-0.5 font-mono text-[10px] text-public-accent">®</sup>
          </Link>

          <nav aria-label="Public navigation" className="hidden items-center gap-1.5 font-mono text-xs lg:flex">
            {navLinks.map(({ href, label }) => {
              const active = pathname === href || pathname.startsWith(`${href}/`);

              return (
                <Link
                  key={href}
                  href={href}
                  aria-current={active ? "page" : undefined}
                  className={`rounded-full border px-3 py-1.5 transition-colors hover:border-public-ink hover:text-public-ink ${
                    active ? "border-public-accent text-public-accent" : "border-public-line text-public-ink"
                  }`}
                >
                  {label}
                </Link>
              );
            })}
          </nav>

          <div className="flex items-center gap-2 sm:gap-3 lg:justify-self-end">
            <Link href="/login" className="hidden whitespace-nowrap px-2 py-2 font-mono text-[11px] uppercase tracking-wide text-public-muted transition-colors hover:text-public-ink lg:inline-flex">
              Client Portal
            </Link>
            <Link href="/book" aria-current={pathname === "/book" ? "page" : undefined} className={bookLinkClass}>
              Book Project ↗
            </Link>
            <button
              type="button"
              onClick={() => setMenuOpen(!menuOpen)}
              className="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full border border-public-line text-public-muted hover:text-public-ink lg:hidden"
              aria-label="Toggle menu"
              aria-expanded={menuOpen}
              aria-controls={menuOpen ? "public-mobile-menu" : undefined}
            >
              <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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

      {menuOpen && (
        <nav id="public-mobile-menu" aria-label="Mobile navigation" className="fixed inset-x-0 bottom-0 top-[calc(var(--public-nav-height)+1px)] z-40 flex flex-col overflow-y-auto bg-public-canvas/95 px-[var(--public-gutter)] py-8 backdrop-blur-xl lg:hidden">
          <div className="my-auto space-y-4 text-2xl font-bold text-public-ink">
            {navLinks.map(({ href, label }) => {
              const active = pathname === href || pathname.startsWith(`${href}/`);

              return (
                <Link
                  key={href}
                  href={href}
                  aria-current={active ? "page" : undefined}
                  onClick={() => setMenuOpen(false)}
                  className="block w-fit py-1 transition-colors hover:text-public-accent aria-[current=page]:text-public-accent"
                >
                  {label}
                </Link>
              );
            })}
            <div className="space-y-4 border-t border-public-line pt-5">
              <Link href="/login" onClick={() => setMenuOpen(false)} className="block w-fit text-lg text-public-muted hover:text-public-ink">
                Client Portal ↗
              </Link>
              <Link href="/book" aria-current={pathname === "/book" ? "page" : undefined} onClick={() => setMenuOpen(false)} className={bookLinkClass}>
                Book a Project ↗
              </Link>
            </div>
          </div>
        </nav>
      )}
    </>
  );
}
