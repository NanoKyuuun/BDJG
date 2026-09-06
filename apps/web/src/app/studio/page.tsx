"use client";

import Link from "next/link";
import { PublicNav } from "@/components/public-nav";
import { PublicFooter } from "@/components/public-footer";

export default function StudioPage() {
  const equipmentCategories = [
    {
      category: "Cinematic Cameras & Large Format",
      items: [
        "ARRI Alexa Mini LF (Large Format 4.5K Open Gate)",
        "RED V-Raptor 8K VV & RED Komodo-X 6K Global Shutter",
        "Sony FX6 & FX3 Cinema Line (Dual Native ISO Units)",
        "Hasselblad H6D-100c (100MP Medium Format Still Sensor)",
      ],
    },
    {
      category: "Optics & Anamorphic Glass",
      items: [
        "Cooke Anamorphic /i Full Frame Plus Prime Set",
        "Leica Summicron-C T2.0 Cine Prime Set (18mm to 100mm)",
        "Canon K35 Vintage Cine Rehoused Primes",
        "Laowa 24mm T14 2X Periprobe Macro Lens System",
      ],
    },
    {
      category: "Lighting, Rigging & Grip",
      items: [
        "Aputure Electro Storm CS15 (1500W Full Color Point Source)",
        "Aputure 1200d Pro & 600c Pro RGBWW Units with Light Domes",
        "Astera Titan Tube 8-Light Wireless Wireless DMX Kit",
        "DJI Ronin 2 3-Axis Gimbal with Master Wheels Control",
        "Custom Heavy-Duty Dana Dolly Track System (12ft)",
      ],
    },
    {
      category: "Post-Production & Audio Suites",
      items: [
        "Apple Mac Studio M2 Ultra (192GB Unified Memory) Color Suite",
        "Flanders Scientific XMP310 (31-inch QD-OLED HDR Color Master Monitor)",
        "Genelec 8341A SAM Studio Monitors (5.1 Surround Sound Array)",
        "Sennheiser MKH 416 & Sound Devices 833 8-Channel Field Recorder",
      ],
    },
  ];

  return (
    <div className="min-h-screen bg-[#090909] text-[#f2f1ed] selection:bg-[#5c7cff] selection:text-black font-sans">
      <PublicNav />

      {/* Hero */}
      <section className="pt-36 pb-20 px-6 sm:px-12 border-b border-[#222222] max-w-7xl mx-auto">
        <div className="max-w-3xl space-y-4">
          <div className="font-mono text-xs text-[#5c7cff] uppercase tracking-widest">Physical & Technical Infrastructure</div>
          <h1 className="text-4xl sm:text-6xl font-extrabold tracking-tight text-[#f2f1ed]">
            Studio Facilities & Optics Inventory
          </h1>
          <p className="text-base sm:text-lg text-[#a3a3a3] leading-relaxed">
            Our purpose-built production facility in Padang houses an acoustic sound stage, cyc-wall cyclorama, dedicated color grading theater, and calibrated camera prep bays.
          </p>
        </div>
      </section>

      {/* Facilities Grid */}
      <section className="py-20 px-6 sm:px-12 max-w-7xl mx-auto border-b border-[#222222]">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div className="p-8 rounded-xl bg-[#121212] border border-[#222222] space-y-3">
            <div className="font-mono text-xs text-[#5c7cff] uppercase">Stage 01</div>
            <h3 className="text-xl font-bold text-[#f2f1ed]">Infinity Cyclorama Stage</h3>
            <p className="text-xs text-[#a3a3a3] leading-relaxed">
              120 sqm soundproof studio with 5-meter ceiling grid, pre-rigged RGBWW spacelights, seamless white corner curve, and 3-phase 30kW generator power supply.
            </p>
          </div>

          <div className="p-8 rounded-xl bg-[#121212] border border-[#222222] space-y-3">
            <div className="font-mono text-xs text-[#5c7cff] uppercase">Suite 02</div>
            <h3 className="text-xl font-bold text-[#f2f1ed]">DaVinci Master Grading Theater</h3>
            <p className="text-xs text-[#a3a3a3] leading-relaxed">
              Neutral grey 18% ambient light room with calibrated Flanders Scientific QD-OLED monitoring, DaVinci Advanced Panels, and client lounge seating.
            </p>
          </div>

          <div className="p-8 rounded-xl bg-[#121212] border border-[#222222] space-y-3">
            <div className="font-mono text-xs text-[#5c7cff] uppercase">Bay 03</div>
            <h3 className="text-xl font-bold text-[#f2f1ed]">Camera Prep & Optical Check</h3>
            <p className="text-xs text-[#a3a3a3] leading-relaxed">
              Collimator optical bench, focus chart testing lanes, and clean-air sensor cleaning facilities to guarantee spotless multi-cam setups before every shoot day.
            </p>
          </div>
        </div>
      </section>

      {/* Equipment List */}
      <section className="py-20 px-6 sm:px-12 max-w-7xl mx-auto">
        <div className="mb-12">
          <div className="font-mono text-xs text-[#5c7cff] uppercase tracking-widest mb-2">Internal Gear Arsenal</div>
          <h2 className="text-3xl font-extrabold text-[#f2f1ed]">Production Equipment Roster</h2>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          {equipmentCategories.map((eq) => (
            <div key={eq.category} className="p-8 rounded-xl bg-[#121212] border border-[#222222]">
              <h3 className="font-mono text-xs uppercase tracking-widest text-[#5c7cff] mb-4 pb-3 border-b border-[#222222]">
                {eq.category}
              </h3>
              <ul className="space-y-3 font-mono text-xs text-[#cccccc]">
                {eq.items.map((item) => (
                  <li key={item} className="flex items-start gap-3">
                    <span className="text-[#666666]">▪</span>
                    <span>{item}</span>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </section>

      {/* Studio Location Footer CTA */}
      <section className="py-16 px-6 sm:px-12 bg-[#0c0c0c] border-t border-[#222222] text-center">
        <div className="max-w-xl mx-auto space-y-4">
          <div className="font-mono text-xs text-[#5c7cff] uppercase">Studio Rental & Bookings</div>
          <h2 className="text-2xl font-bold text-[#f2f1ed]">Inquire for Studio Stage & Gear Hire</h2>
          <p className="text-xs text-[#a3a3a3]">
            Our soundstage and cinema packages are available for visiting international and national commercial directors.
          </p>
          <div className="pt-2">
            <Link
              href="/contact"
              className="inline-block font-mono text-xs font-bold uppercase tracking-widest px-6 py-3 rounded bg-[#f2f1ed] text-black hover:bg-white"
            >
              Contact Studio Manager ↗
            </Link>
          </div>
        </div>
      </section>

      <PublicFooter />
    </div>
  );
}
