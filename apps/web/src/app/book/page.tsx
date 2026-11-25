"use client";

import { PublicNav } from "@/components/public-nav";
import { PublicFooter } from "@/components/public-footer";
import { BookingWizard } from "@/components/booking-wizard";

export default function BookPage() {
  return (
    <div className="min-h-screen bg-public-canvas text-public-ink selection:bg-public-accent selection:text-black font-sans">
      <PublicNav />

      {/* Header */}
      <section className="pt-36 pb-12 px-6 sm:px-12 max-w-4xl mx-auto text-center">
        <div className="font-mono text-xs text-public-accent uppercase tracking-widest mb-2">Production Reservation</div>
        <h1 className="text-4xl sm:text-6xl font-extrabold tracking-tight text-public-ink mb-4">
          Book a Project
        </h1>
        <p className="text-sm sm:text-base text-public-muted max-w-xl mx-auto leading-relaxed">
          Submit your production requirements, creative brief, and timeline. Our studio management desk immediately prepares quotation estimates and checks director availability.
        </p>
      </section>

      {/* Wizard */}
      <section className="pb-24 px-6 sm:px-12 max-w-4xl mx-auto">
        <BookingWizard />
      </section>

      <PublicFooter />
    </div>
  );
}
