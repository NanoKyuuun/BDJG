"use client";

import { useState } from "react";

interface BookingWizardProps {
  initialService?: string;
  onSuccess?: (inquiryId: string) => void;
}

export function BookingWizard({ initialService = "COMMERCIAL_FILM", onSuccess }: BookingWizardProps) {
  const [step, setStep] = useState(1);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [inquiryResult, setInquiryResult] = useState<{ id: number; ref?: string } | null>(null);

  const [formData, setFormData] = useState({
    service: initialService,
    package_name: "Signature Production Package",
    addons: [] as string[],
    project_brief: "",
    preferred_date: "",
    alternative_date: "",
    location: "Padang / West Sumatra",
    estimated_budget: "25000000",
    client_name: "",
    email: "",
    phone: "",
    company: "",
  });

  const services = [
    { id: "COMMERCIAL_FILM", name: "Commercial & Brand Film", desc: "Cinematic commercial films for premium brands and campaigns." },
    { id: "WEDDING_CINEMA", name: "Wedding & Ceremonial", desc: "Emotionally resonant wedding documentary & cinematic highlights." },
    { id: "PHOTOGRAPHY", name: "Editorial & Portrait", desc: "Studio portraiture, fashion lookbooks, and architecture photo." },
    { id: "POST_VFX", name: "Color Grading & VFX", desc: "High-end DaVinci Resolve grading, conform, and visual effects." },
  ];

  const packages = [
    { id: "ESSENTIAL", name: "Essential Studio Pack", price: "Rp 15.000.000", desc: "1-day shoot, 2 camera operators, 4K highlight cut, 2 revision rounds." },
    { id: "SIGNATURE", name: "Signature Production Package", price: "Rp 28.000.000", desc: "2-day shoot, full crew, cinematic drone, documentary master, sound design." },
    { id: "BESPOKE", name: "Bespoke Enterprise Package", price: "Custom / Rp 45.000.000+", desc: "Multi-location narrative production, bespoke score, full VFX & coloring." },
  ];

  const availableAddons = [
    { id: "FPV_DRONE", name: "Cinematic FPV Drone Flight", price: "+ Rp 3.500.000" },
    { id: "SDE", name: "Same-Day-Edit (SDE) Fast Turnaround", price: "+ Rp 4.500.000" },
    { id: "RAW_STORAGE", name: "Full RAW Project SSD Handover", price: "+ Rp 2.000.000" },
    { id: "ANALOG_FILM", name: "35mm Analog Film Stills (3 Rolls)", price: "+ Rp 2.500.000" },
  ];

  const toggleAddon = (addonName: string) => {
    setFormData((prev) => ({
      ...prev,
      addons: prev.addons.includes(addonName)
        ? prev.addons.filter((a) => a !== addonName)
        : [...prev.addons, addonName],
    }));
  };

  const handleNext = () => {
    setError(null);
    if (step === 1 && !formData.service) {
      setError("Please choose a service discipline.");
      return;
    }
    if (step === 2 && !formData.package_name) {
      setError("Please select a base package.");
      return;
    }
    if (step === 3 && formData.project_brief.trim().length < 10) {
      setError("Please provide a brief story or requirement (minimum 10 characters).");
      return;
    }
    if (step === 4 && !formData.preferred_date) {
      setError("Please specify your target date.");
      return;
    }
    if (step === 5) {
      if (!formData.client_name.trim()) {
        setError("Your name is required.");
        return;
      }
      if (!formData.email.trim() || !formData.phone.trim()) {
        setError("Both email and phone/WhatsApp number are required for follow-up.");
        return;
      }
    }
    setStep((prev) => Math.min(prev + 1, 6));
  };

  const handleSubmit = async () => {
    setSubmitting(true);
    setError(null);
    try {
      const res = await fetch("/api/v1/inquiries", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({
          client_name: formData.client_name,
          email: formData.email,
          phone: formData.phone,
          company: formData.company || null,
          service: formData.service,
          package: formData.package_name,
          addons: formData.addons,
          project_brief: `[${formData.package_name}] Add-ons: ${formData.addons.join(", ") || "None"} | Location: ${formData.location} | Brief: ${formData.project_brief}`,
          preferred_date: formData.preferred_date,
          alternative_date: formData.alternative_date || null,
          estimated_budget: parseInt(formData.estimated_budget, 10) || 25000000,
        }),
      });

      if (!res.ok) {
        const errJson = await res.json().catch(() => ({}));
        throw new Error(errJson.message || "Failed to submit inquiry. Please try again.");
      }

      const data = await res.json();
      const refCode = `BDJG-INQ-${new Date().getFullYear()}-${String(data?.data?.id || Math.floor(Math.random() * 900 + 100)).padStart(4, "0")}`;
      setInquiryResult({ id: data?.data?.id || 1, ref: refCode });
      if (onSuccess) onSuccess(refCode);
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Submission error.";
      setError(msg);
    } finally {
      setSubmitting(false);
    }
  };

  if (inquiryResult) {
    return (
      <div className="bg-public-panel border border-public-line p-8 sm:p-12 rounded-xl text-center max-w-2xl mx-auto shadow-2xl">
        <div className="w-16 h-16 bg-public-success/10 border border-public-success rounded-full flex items-center justify-center mx-auto mb-6 text-public-success">
          <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <div className="font-mono text-xs text-public-success uppercase tracking-widest mb-2">Inquiry Confirmed</div>
        <h3 className="text-3xl font-extrabold text-public-ink mb-4">Story Received</h3>
        <p className="text-public-muted text-sm leading-relaxed mb-6">
          Thank you, <strong className="text-public-ink">{formData.client_name}</strong>. Your project inquiry has been lodged in our studio management queue under reference:
        </p>
        <div className="inline-block font-mono text-xl font-bold bg-public-raised border border-public-control px-6 py-3 rounded-lg text-public-accent tracking-wider mb-8">
          {inquiryResult.ref}
        </div>
        <p className="text-xs text-public-subtle max-w-md mx-auto mb-8">
          Our producer will review your requirements and follow up via WhatsApp ({formData.phone}) and email ({formData.email}) within 24 operational hours.
        </p>
        <button
          type="button"
          onClick={() => {
            setInquiryResult(null);
            setStep(1);
          }}
          className="font-mono text-xs uppercase tracking-wider px-6 py-3 rounded bg-public-line hover:bg-public-control text-public-ink transition-colors"
        >
          Submit Another Request
        </button>
      </div>
    );
  }

  const stepTitles = ["Service Discipline", "Package & Scope", "Creative Brief", "Production Schedule", "Contact Info", "Review & Confirm"];

  return (
    <div className="bg-public-panel border border-public-line rounded-xl p-6 sm:p-10 shadow-2xl">
      {/* Step Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-public-line">
        <div>
          <span className="font-mono text-xs uppercase tracking-widest text-public-accent">
            Step 0{step} / 06 — {stepTitles[step - 1]}
          </span>
          <h3 className="text-xl font-bold text-public-ink mt-1">
            {step === 1 && "Choose Your Production Discipline"}
            {step === 2 && "Select Deliverable Tier & Add-ons"}
            {step === 3 && "Tell Us About Your Vision & Scope"}
            {step === 4 && "Production Timeline & Location"}
            {step === 5 && "Primary Producer / Client Contact"}
            {step === 6 && "Verify Commercial & Creative Details"}
          </h3>
        </div>
        <div className="flex items-center gap-1.5">
          {[1, 2, 3, 4, 5, 6].map((s) => (
            <div
              key={s}
              className={`h-1.5 w-6 rounded-full transition-all ${
                s === step ? "bg-public-accent" : s < step ? "bg-public-success" : "bg-public-line"
              }`}
            />
          ))}
        </div>
      </div>

      {error && (
        <div className="mb-6 p-4 rounded bg-red-950/40 border border-red-800/60 text-red-300 text-xs font-mono">
          ⚠ {error}
        </div>
      )}

      {/* Step 1: Service */}
      {step === 1 && (
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          {services.map((svc) => (
            <button
              key={svc.id}
              type="button"
              onClick={() => setFormData({ ...formData, service: svc.id })}
              className={`p-6 rounded-lg text-left border transition-all ${
                formData.service === svc.id
                  ? "bg-public-raised border-public-accent shadow-lg shadow-public-accent/5 ring-1 ring-public-accent"
                  : "bg-public-inset border-public-line hover:border-public-control"
              }`}
            >
              <div className="font-mono text-xs text-public-accent uppercase tracking-wider mb-1.5">0{services.indexOf(svc) + 1}</div>
              <div className="font-bold text-lg text-public-ink mb-2">{svc.name}</div>
              <p className="text-xs text-public-muted leading-relaxed">{svc.desc}</p>
            </button>
          ))}
        </div>
      )}

      {/* Step 2: Packages & Addons */}
      {step === 2 && (
        <div className="space-y-6">
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {packages.map((pkg) => (
              <button
                key={pkg.id}
                type="button"
                onClick={() => setFormData({ ...formData, package_name: pkg.name })}
                className={`p-5 rounded-lg text-left border transition-all ${
                  formData.package_name === pkg.name
                    ? "bg-public-raised border-public-accent ring-1 ring-public-accent"
                    : "bg-public-inset border-public-line hover:border-public-control"
                }`}
              >
                <div className="font-bold text-sm text-public-ink mb-1">{pkg.name}</div>
                <div className="font-mono text-xs font-semibold text-public-accent mb-2">{pkg.price}</div>
                <p className="text-[11px] text-public-subtle leading-relaxed">{pkg.desc}</p>
              </button>
            ))}
          </div>

          <div className="pt-4 border-t border-public-line">
            <label className="block font-mono text-xs uppercase tracking-widest text-public-muted mb-3">
              Optional Add-on Enhancements
            </label>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              {availableAddons.map((add) => {
                const checked = formData.addons.includes(add.name);
                return (
                  <button
                    key={add.id}
                    type="button"
                    onClick={() => toggleAddon(add.name)}
                    className={`p-3.5 rounded border text-left flex items-center justify-between transition-colors ${
                      checked
                        ? "bg-public-accent/10 border-public-accent text-public-ink"
                        : "bg-public-inset border-public-line text-public-muted hover:border-public-control"
                    }`}
                  >
                    <div>
                      <div className="text-xs font-semibold">{add.name}</div>
                      <div className="font-mono text-[10px] text-public-accent">{add.price}</div>
                    </div>
                    <div className={`w-4 h-4 rounded border flex items-center justify-center font-mono text-[10px] ${
                      checked ? "bg-public-accent border-public-accent text-public-inset font-bold" : "border-public-control"
                    }`}>
                      {checked && "✓"}
                    </div>
                  </button>
                );
              })}
            </div>
          </div>
        </div>
      )}

      {/* Step 3: Brief */}
      {step === 3 && (
        <div className="space-y-4">
          <div>
            <label className="block font-mono text-xs uppercase tracking-wider text-public-muted mb-2">
              Project Brief & Vision *
            </label>
            <textarea
              rows={5}
              value={formData.project_brief}
              onChange={(e) => setFormData({ ...formData, project_brief: e.target.value })}
              placeholder="Describe the mood, target audience, reference aesthetic, deliverable formats (e.g. 9:16 Reels + 16:9 4K Master), or story notes..."
              className="w-full bg-public-inset border border-public-control rounded-lg p-4 text-sm text-public-ink placeholder-public-subtle focus:outline-none focus:border-public-accent"
            />
          </div>

          <div>
            <label className="block font-mono text-xs uppercase tracking-wider text-public-muted mb-2">
              Estimated Budget Target (IDR)
            </label>
            <select
              value={formData.estimated_budget}
              onChange={(e) => setFormData({ ...formData, estimated_budget: e.target.value })}
              className="w-full bg-public-inset border border-public-control rounded-lg p-3 text-sm text-public-ink focus:outline-none focus:border-public-accent"
            >
              <option value="15000000">Rp 15.000.000 - Rp 25.000.000</option>
              <option value="25000000">Rp 25.000.000 - Rp 40.000.000</option>
              <option value="50000000">Rp 50.000.000 - Rp 75.000.000</option>
              <option value="100000000">Rp 100.000.000+ (High-End Production)</option>
            </select>
          </div>
        </div>
      )}

      {/* Step 4: Schedule */}
      {step === 4 && (
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label className="block font-mono text-xs uppercase tracking-wider text-public-muted mb-2">
              Target Shoot / Event Date *
            </label>
            <input
              type="date"
              value={formData.preferred_date}
              onChange={(e) => setFormData({ ...formData, preferred_date: e.target.value })}
              className="w-full bg-public-inset border border-public-control rounded-lg p-3 text-sm text-public-ink focus:outline-none focus:border-public-accent"
            />
          </div>
          <div>
            <label className="block font-mono text-xs uppercase tracking-wider text-public-muted mb-2">
              Alternative Backup Date
            </label>
            <input
              type="date"
              value={formData.alternative_date}
              onChange={(e) => setFormData({ ...formData, alternative_date: e.target.value })}
              className="w-full bg-public-inset border border-public-control rounded-lg p-3 text-sm text-public-ink focus:outline-none focus:border-public-accent"
            />
          </div>
          <div className="sm:col-span-2">
            <label className="block font-mono text-xs uppercase tracking-wider text-public-muted mb-2">
              Shooting Location / Venue
            </label>
            <input
              type="text"
              value={formData.location}
              onChange={(e) => setFormData({ ...formData, location: e.target.value })}
              placeholder="e.g. Padang Studio, Bukittinggi Highlands, Mentawai, Jakarta..."
              className="w-full bg-public-inset border border-public-control rounded-lg p-3 text-sm text-public-ink focus:outline-none focus:border-public-accent"
            />
          </div>
        </div>
      )}

      {/* Step 5: Contact */}
      {step === 5 && (
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label className="block font-mono text-xs uppercase tracking-wider text-public-muted mb-2">
              Your Full Name *
            </label>
            <input
              type="text"
              value={formData.client_name}
              onChange={(e) => setFormData({ ...formData, client_name: e.target.value })}
              placeholder="e.g. Raden Mas Danang"
              className="w-full bg-public-inset border border-public-control rounded-lg p-3 text-sm text-public-ink focus:outline-none focus:border-public-accent"
            />
          </div>
          <div>
            <label className="block font-mono text-xs uppercase tracking-wider text-public-muted mb-2">
              Company / Brand (Optional)
            </label>
            <input
              type="text"
              value={formData.company}
              onChange={(e) => setFormData({ ...formData, company: e.target.value })}
              placeholder="e.g. PT Sinar Mahakarya"
              className="w-full bg-public-inset border border-public-control rounded-lg p-3 text-sm text-public-ink focus:outline-none focus:border-public-accent"
            />
          </div>
          <div>
            <label className="block font-mono text-xs uppercase tracking-wider text-public-muted mb-2">
              Email Address *
            </label>
            <input
              type="email"
              value={formData.email}
              onChange={(e) => setFormData({ ...formData, email: e.target.value })}
              placeholder="danang@example.com"
              className="w-full bg-public-inset border border-public-control rounded-lg p-3 text-sm text-public-ink focus:outline-none focus:border-public-accent"
            />
          </div>
          <div>
            <label className="block font-mono text-xs uppercase tracking-wider text-public-muted mb-2">
              WhatsApp / Phone *
            </label>
            <input
              type="tel"
              value={formData.phone}
              onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
              placeholder="081234567890"
              className="w-full bg-public-inset border border-public-control rounded-lg p-3 text-sm text-public-ink focus:outline-none focus:border-public-accent"
            />
          </div>
        </div>
      )}

      {/* Step 6: Review */}
      {step === 6 && (
        <div className="bg-public-inset border border-public-control rounded-lg p-6 space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-mono">
            <div>
              <span className="text-public-subtle uppercase block">Discipline</span>
              <span className="text-public-ink font-semibold">{formData.service}</span>
            </div>
            <div>
              <span className="text-public-subtle uppercase block">Package</span>
              <span className="text-public-ink font-semibold">{formData.package_name}</span>
            </div>
            <div>
              <span className="text-public-subtle uppercase block">Add-ons</span>
              <span className="text-public-ink">{formData.addons.join(", ") || "None"}</span>
            </div>
            <div>
              <span className="text-public-subtle uppercase block">Target Date</span>
              <span className="text-public-accent font-semibold">{formData.preferred_date}</span>
            </div>
            <div>
              <span className="text-public-subtle uppercase block">Location</span>
              <span className="text-public-ink">{formData.location}</span>
            </div>
            <div>
              <span className="text-public-subtle uppercase block">Contact</span>
              <span className="text-public-ink">{formData.client_name} ({formData.phone})</span>
            </div>
          </div>
          <div className="pt-4 border-t border-public-line">
            <span className="font-mono text-[11px] text-public-subtle uppercase block mb-1">Brief Summary</span>
            <p className="text-xs text-public-muted italic bg-public-inset p-3 rounded border border-public-line">
              &ldquo;{formData.project_brief}&rdquo;
            </p>
          </div>
        </div>
      )}

      {/* Actions */}
      <div className="flex items-center justify-between gap-4 pt-8 mt-8 border-t border-public-line">
        <button
          type="button"
          disabled={step === 1 || submitting}
          onClick={() => setStep((s) => Math.max(s - 1, 1))}
          className="font-mono text-xs uppercase tracking-wider px-5 py-2.5 rounded border border-public-control text-public-muted hover:text-public-ink disabled:opacity-30 disabled:pointer-events-none transition-colors"
        >
          ← Back
        </button>

        {step < 6 ? (
          <button
            type="button"
            onClick={handleNext}
            className="font-mono text-xs font-bold uppercase tracking-wider px-6 py-2.5 rounded bg-public-accent text-public-inset hover:bg-public-accent-hover active:scale-[0.98] transition-all"
          >
            Next Step →
          </button>
        ) : (
          <button
            type="button"
            disabled={submitting}
            onClick={handleSubmit}
            className="font-mono text-xs font-bold uppercase tracking-wider px-8 py-3 rounded bg-public-accent text-public-inset hover:bg-public-accent-hover active:scale-[0.98] disabled:opacity-50 transition-all flex items-center gap-2"
          >
            {submitting ? "Submitting to Queue..." : "Confirm & Send Inquiry ↗"}
          </button>
        )}
      </div>
    </div>
  );
}
