"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface QuotationItem {
  id: number;
  name: string;
  description?: string;
  quantity: number;
  unit_price: number;
  line_total: number;
}

interface QuotationVersion {
  id: number;
  version_number: number;
  project_name: string;
  package_name_snapshot?: string;
  subtotal: number;
  discount: number;
  tax: number;
  grand_total: number;
  dp_amount: number;
  dp_value: number;
  remaining_amount: number;
  terms?: string;
  items: QuotationItem[];
}

interface Quotation {
  id: number;
  quotation_number: string;
  status: string;
  expires_at?: string;
  created_at: string;
  current_version?: QuotationVersion;
}

export default function ClientQuotationsPage() {
  const [quotations, setQuotations] = useState<Quotation[]>([]);
  const [selectedQuotation, setSelectedQuotation] = useState<Quotation | null>(null);
  const [loading, setLoading] = useState(true);

  // Modals
  const [showAcceptModal, setShowAcceptModal] = useState(false);
  const [showRevisionModal, setShowRevisionModal] = useState(false);
  const [revisionNotes, setRevisionNotes] = useState("");
  const [termsAgreed, setTermsAgreed] = useState(false);
  const [actionLoading, setActionLoading] = useState(false);

  useEffect(() => {
    loadQuotations();
  }, []);

  async function loadQuotations() {
    try {
      setLoading(true);
      const res = await fetchApi<{ data: Quotation[] }>("/api/v1/client/quotations");
      setQuotations(res.data || []);
      if (res.data && res.data.length > 0) {
        setSelectedQuotation(res.data[0]);
      }
    } catch (err) {
      console.error("Failed to load quotations:", err);
    } finally {
      setLoading(false);
    }
  }

  async function handleAccept() {
    if (!selectedQuotation || !termsAgreed) return;

    try {
      setActionLoading(true);
      await fetchApi(`/api/v1/client/quotations/${selectedQuotation.id}/accept`, {
        method: "POST",
      });
      setShowAcceptModal(false);
      await loadQuotations();
      alert("Quotation accepted! Your down payment (DP) invoice has been generated.");
    } catch (err: any) {
      alert("Failed to accept quotation: " + err.message);
    } finally {
      setActionLoading(false);
    }
  }

  async function handleRequestRevision() {
    if (!selectedQuotation || !revisionNotes.trim()) return;

    try {
      setActionLoading(true);
      await fetchApi(`/api/v1/client/quotations/${selectedQuotation.id}/request-revision`, {
        method: "POST",
        body: JSON.stringify({ revision_notes: revisionNotes }),
      });
      setShowRevisionModal(false);
      setRevisionNotes("");
      await loadQuotations();
      alert("Revision request submitted to studio team.");
    } catch (err: any) {
      alert("Failed to submit revision request: " + err.message);
    } finally {
      setActionLoading(false);
    }
  }

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "SENT":
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-900/60 text-amber-300 border border-amber-700/50">Ready for Review</span>;
      case "ACCEPTED":
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-900/60 text-emerald-300 border border-emerald-700/50">Accepted</span>;
      case "REVISION_REQUESTED":
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-900/60 text-blue-300 border border-blue-700/50">Revision In Progress</span>;
      case "DECLINED":
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-900/60 text-red-300 border border-red-700/50">Declined</span>;
      default:
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-zinc-800 text-zinc-400 border border-zinc-700">{status}</span>;
    }
  };

  const ver = selectedQuotation?.current_version;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight text-white">Commercial Quotations</h1>
        <p className="text-sm text-zinc-400 mt-1">
          Official production proposals, package breakdowns, payment schedules, and terms agreement.
        </p>
      </div>

      {loading ? (
        <div className="h-64 bg-zinc-900/60 border border-zinc-800 rounded-xl animate-pulse" />
      ) : quotations.length === 0 ? (
        <div className="p-12 text-center bg-zinc-900/40 border border-zinc-800 rounded-xl">
          <span className="text-4xl">🧾</span>
          <h3 className="text-lg font-semibold text-white mt-3">No Quotations Yet</h3>
          <p className="text-sm text-zinc-400 mt-1">Our producers are preparing your customized visual proposal.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
          {/* Left Column: Quotations List (4 Cols) */}
          <div className="lg:col-span-4 space-y-3">
            {quotations.map((q) => (
              <div
                key={q.id}
                onClick={() => setSelectedQuotation(q)}
                className={`p-4 rounded-xl border cursor-pointer transition ${
                  selectedQuotation?.id === q.id
                    ? "bg-zinc-900 border-amber-500/80 shadow-lg shadow-amber-500/5"
                    : "bg-zinc-900/50 hover:bg-zinc-900/80 border-zinc-800"
                }`}
              >
                <div className="flex items-center justify-between">
                  <span className="font-mono text-xs text-zinc-400 font-bold">{q.quotation_number}</span>
                  {getStatusBadge(q.status)}
                </div>
                <h4 className="text-sm font-bold text-white mt-2">
                  {q.current_version?.project_name || "Production Quote"}
                </h4>
                <div className="mt-3 flex items-center justify-between text-xs text-zinc-500">
                  <span>Version {q.current_version?.version_number || 1}</span>
                  <span className="text-amber-400 font-semibold font-mono">
                    IDR {Number(q.current_version?.grand_total || 0).toLocaleString("id-ID")}
                  </span>
                </div>
              </div>
            ))}
          </div>

          {/* Right Column: Detailed Quotation Sheet (8 Cols) */}
          {selectedQuotation && ver && (
            <div className="lg:col-span-8 bg-zinc-900 border border-zinc-800 rounded-2xl p-6 md:p-8 space-y-6 shadow-2xl">
              {/* Sheet Header */}
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-zinc-800">
                <div>
                  <span className="text-xs uppercase font-mono tracking-widest text-amber-500 font-bold">
                    Official Proposal
                  </span>
                  <h2 className="text-2xl font-extrabold text-white mt-1">{ver.project_name}</h2>
                  <p className="text-xs text-zinc-400 mt-1">Package: {ver.package_name_snapshot || "Custom Production"}</p>
                </div>

                <div className="text-right">
                  <span className="text-xs text-zinc-500 block">Total Investment</span>
                  <span className="text-2xl font-black text-amber-400 font-mono">
                    IDR {Number(ver.grand_total).toLocaleString("id-ID")}
                  </span>
                </div>
              </div>

              {/* Deliverables Breakdown Table */}
              <div>
                <h4 className="text-xs uppercase font-mono tracking-wider text-zinc-400 font-bold mb-3">
                  Scope & Deliverables Breakdown
                </h4>
                <div className="bg-zinc-950 border border-zinc-800/80 rounded-xl overflow-hidden">
                  <table className="w-full text-left text-xs">
                    <thead className="bg-zinc-900/90 text-zinc-400 border-b border-zinc-800">
                      <tr>
                        <th className="py-3 px-4">Item & Description</th>
                        <th className="py-3 px-4 text-center">Qty</th>
                        <th className="py-3 px-4 text-right">Unit Price</th>
                        <th className="py-3 px-4 text-right">Amount</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-zinc-800/60 text-zinc-300">
                      {ver.items.map((item) => (
                        <tr key={item.id} className="hover:bg-zinc-900/40">
                          <td className="py-3 px-4">
                            <span className="font-semibold text-white block">{item.name}</span>
                            {item.description && <span className="text-zinc-500 text-[11px]">{item.description}</span>}
                          </td>
                          <td className="py-3 px-4 text-center font-mono">{item.quantity}</td>
                          <td className="py-3 px-4 text-right font-mono">
                            IDR {Number(item.unit_price).toLocaleString("id-ID")}
                          </td>
                          <td className="py-3 px-4 text-right font-mono font-semibold text-zinc-100">
                            IDR {Number(item.line_total).toLocaleString("id-ID")}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>

              {/* Payment Schedule Card */}
              <div className="p-4 bg-zinc-950 border border-zinc-800 rounded-xl grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                  <span className="text-zinc-500 block">Down Payment (DP {ver.dp_value}%)</span>
                  <span className="text-base font-extrabold text-amber-400 font-mono">
                    IDR {Number(ver.dp_amount).toLocaleString("id-ID")}
                  </span>
                  <span className="text-[11px] text-zinc-500 block mt-0.5">Required for production activation</span>
                </div>
                <div>
                  <span className="text-zinc-500 block">Remaining Balance</span>
                  <span className="text-base font-extrabold text-zinc-200 font-mono">
                    IDR {Number(ver.remaining_amount).toLocaleString("id-ID")}
                  </span>
                  <span className="text-[11px] text-zinc-500 block mt-0.5">Due before master delivery</span>
                </div>
              </div>

              {/* Terms */}
              {ver.terms && (
                <div className="text-xs text-zinc-400 bg-zinc-950/50 p-4 rounded-xl border border-zinc-800/50">
                  <strong className="text-zinc-300 block mb-1">Production Terms:</strong>
                  <p className="leading-relaxed">{ver.terms}</p>
                </div>
              )}

              {/* Action Buttons (If Status is SENT) */}
              {selectedQuotation.status === "SENT" && (
                <div className="pt-4 border-t border-zinc-800 flex flex-col sm:flex-row items-center justify-end gap-3">
                  <button
                    onClick={() => setShowRevisionModal(true)}
                    className="w-full sm:w-auto px-5 py-2.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-semibold text-xs rounded-xl transition"
                  >
                    Request Adjustments
                  </button>

                  <button
                    onClick={() => setShowAcceptModal(true)}
                    className="w-full sm:w-auto px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-black font-extrabold text-xs rounded-xl shadow-lg transition"
                  >
                    Accept & Generate DP Invoice →
                  </button>
                </div>
              )}
            </div>
          )}
        </div>
      )}

      {/* ACCEPT MODAL */}
      {showAcceptModal && (
        <div className="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-zinc-900 border border-zinc-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <h3 className="text-lg font-bold text-white">Accept Quotation Proposal</h3>
            <p className="text-xs text-zinc-400 leading-relaxed">
              By accepting Quotation <strong>#{selectedQuotation?.quotation_number}</strong>, you agree to the itemized
              scope of work and terms. A down payment (DP) invoice will be immediately issued to activate production.
            </p>

            <label className="flex items-start gap-3 text-xs text-zinc-300 cursor-pointer pt-2">
              <input
                type="checkbox"
                checked={termsAgreed}
                onChange={(e) => setTermsAgreed(e.target.checked)}
                className="mt-0.5 rounded bg-zinc-950 border-zinc-700 text-amber-500 focus:ring-0"
              />
              <span>I confirm and accept the production agreement and terms.</span>
            </label>

            <div className="flex justify-end gap-3 pt-4 border-t border-zinc-800">
              <button
                onClick={() => setShowAcceptModal(false)}
                className="px-4 py-2 bg-zinc-800 text-zinc-400 hover:text-white rounded-lg text-xs"
              >
                Cancel
              </button>
              <button
                onClick={handleAccept}
                disabled={!termsAgreed || actionLoading}
                className="px-5 py-2 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-black font-bold rounded-lg text-xs"
              >
                {actionLoading ? "Processing..." : "Confirm Acceptance"}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* REVISION REQUEST MODAL */}
      {showRevisionModal && (
        <div className="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-zinc-900 border border-zinc-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <h3 className="text-lg font-bold text-white">Request Quotation Adjustments</h3>
            <p className="text-xs text-zinc-400">
              Please describe the scope adjustments or custom requirements you would like our producers to revise.
            </p>

            <textarea
              value={revisionNotes}
              onChange={(e) => setRevisionNotes(e.target.value)}
              placeholder="e.g., Please add 1 additional cinematographer and drone coverage for Day 2..."
              rows={4}
              className="w-full px-3 py-2 bg-zinc-950 border border-zinc-800 focus:border-amber-500 rounded-xl text-xs text-white placeholder-zinc-600 focus:outline-none resize-none"
            />

            <div className="flex justify-end gap-3 pt-4 border-t border-zinc-800">
              <button
                onClick={() => setShowRevisionModal(false)}
                className="px-4 py-2 bg-zinc-800 text-zinc-400 hover:text-white rounded-lg text-xs"
              >
                Cancel
              </button>
              <button
                onClick={handleRequestRevision}
                disabled={!revisionNotes.trim() || actionLoading}
                className="px-5 py-2 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-black font-bold rounded-lg text-xs"
              >
                {actionLoading ? "Submitting..." : "Submit Revision Request"}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
