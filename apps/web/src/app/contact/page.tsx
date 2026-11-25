"use client";

import Link from "next/link";
import { PublicNav } from "@/components/public-nav";
import { PublicFooter } from "@/components/public-footer";

export default function ContactPage() {
  return (
    <div className="min-h-screen bg-public-canvas text-public-ink selection:bg-public-accent selection:text-black font-sans">
      <PublicNav />

      {/* Header */}
      <section className="pt-36 pb-16 px-6 sm:px-12 border-b border-public-line max-w-7xl mx-auto">
        <div className="max-w-3xl space-y-4">
          <div className="font-mono text-xs text-public-accent uppercase tracking-widest">Connect with Us</div>
          <h1 className="text-4xl sm:text-6xl font-extrabold tracking-tight text-public-ink">
            Studio Desk & Coordinates
          </h1>
          <p className="text-base sm:text-lg text-public-muted leading-relaxed">
            Have an upcoming project, press inquiry, or equipment collaboration? Reach our production team directly or visit our studio in Padang.
          </p>
        </div>
      </section>

      {/* Contact Grid */}
      <section className="py-20 px-6 sm:px-12 max-w-7xl mx-auto">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12">
          {/* Info Side */}
          <div className="lg:col-span-5 space-y-8">
            <div className="p-8 rounded-xl bg-public-panel border border-public-line space-y-6">
              <div>
                <div className="font-mono text-[10px] text-public-accent uppercase tracking-widest mb-1">Studio Location</div>
                <h3 className="font-bold text-lg text-public-ink">BDJG Creative Studio HQ</h3>
                <p className="text-sm text-public-muted mt-1 leading-relaxed">
                  Jl. Khatib Sulaiman No. 48, Lolong Belanti, Kec. Padang Utara, Kota Padang, Sumatera Barat 25136, Indonesia
                </p>
              </div>

              <div className="pt-4 border-t border-public-line">
                <div className="font-mono text-[10px] text-public-accent uppercase tracking-widest mb-1">Direct Communication</div>
                <div className="space-y-1 text-sm font-mono">
                  <div>
                    <a href="mailto:hello@bdjg.studio" className="text-public-ink hover:text-public-accent transition-colors">
                      hello@bdjg.studio
                    </a>
                  </div>
                  <div>
                    <a href="tel:+6281234567890" className="text-public-ink hover:text-public-accent transition-colors">
                      +62 812-3456-7890 (WhatsApp & Phone)
                    </a>
                  </div>
                </div>
              </div>

              <div className="pt-4 border-t border-public-line">
                <div className="font-mono text-[10px] text-public-accent uppercase tracking-widest mb-1">Operational Hours</div>
                <p className="text-xs text-public-muted font-mono">
                  Monday – Saturday: 09:00 – 18:00 WIB <br />
                  Sunday: Reserved for Scheduled Shoots
                </p>
              </div>
            </div>

            <div className="p-8 rounded-xl bg-public-raised border border-public-line space-y-4">
              <h4 className="font-bold text-base text-public-ink">Planning a Production?</h4>
              <p className="text-xs text-public-muted leading-relaxed">
                For detailed quotation requests, treatment pitches, and timeline reservations, use our streamlined 6-step project booking wizard.
              </p>
              <Link
                href="/book"
                className="inline-block font-mono text-xs font-bold uppercase tracking-widest px-6 py-3 rounded bg-public-accent text-public-inset hover:bg-public-accent-hover transition-colors"
              >
                Go to Project Booking Form ↗
              </Link>
            </div>
          </div>

          {/* Quick Contact Form */}
          <div className="lg:col-span-7 p-8 sm:p-12 rounded-xl bg-public-panel border border-public-line">
            <h3 className="text-2xl font-bold text-public-ink mb-2">Direct Message</h3>
            <p className="text-xs text-public-subtle font-mono mb-8">Direct transmission to studio management inbox.</p>

            <form
              onSubmit={(e) => {
                e.preventDefault();
                alert("Thank you for reaching out. We will reply to your email shortly.");
              }}
              className="space-y-6"
            >
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block font-mono text-xs text-public-muted uppercase mb-2">Your Name</label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Maya Indah"
                    className="w-full bg-public-inset border border-public-control rounded-lg p-3 text-sm text-public-ink focus:outline-none focus:border-public-accent"
                  />
                </div>
                <div>
                  <label className="block font-mono text-xs text-public-muted uppercase mb-2">Email Address</label>
                  <input
                    type="email"
                    required
                    placeholder="maya@example.com"
                    className="w-full bg-public-inset border border-public-control rounded-lg p-3 text-sm text-public-ink focus:outline-none focus:border-public-accent"
                  />
                </div>
              </div>

              <div>
                <label className="block font-mono text-xs text-public-muted uppercase mb-2">Subject / Topic</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Studio Visit / Equipment Inquiry / Commercial Pitch"
                  className="w-full bg-public-inset border border-public-control rounded-lg p-3 text-sm text-public-ink focus:outline-none focus:border-public-accent"
                />
              </div>

              <div>
                <label className="block font-mono text-xs text-public-muted uppercase mb-2">Message</label>
                <textarea
                  rows={5}
                  required
                  placeholder="Write your note here..."
                  className="w-full bg-public-inset border border-public-control rounded-lg p-3 text-sm text-public-ink focus:outline-none focus:border-public-accent"
                />
              </div>

              <button
                type="submit"
                className="w-full font-mono text-xs font-bold uppercase tracking-widest py-3.5 rounded bg-public-accent text-public-inset hover:bg-public-accent-hover transition-colors"
              >
                Send Message ↗
              </button>
            </form>
          </div>
        </div>
      </section>

      <PublicFooter />
    </div>
  );
}
