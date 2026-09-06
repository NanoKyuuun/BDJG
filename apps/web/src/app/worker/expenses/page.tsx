"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface WorkerExpense {
  id: number;
  project_id: number;
  project_name?: string;
  title: string;
  amount: number;
  category: string;
  status: string;
  receipt_storage_key?: string;
  notes?: string;
  rejection_reason?: string;
  created_at: string;
}

interface WorkerProject {
  id: number;
  name: string;
  project_number: string;
}

export default function WorkerExpensesPage() {
  const [expenses, setExpenses] = useState<WorkerExpense[]>([]);
  const [projects, setProjects] = useState<WorkerProject[]>([]);
  const [loading, setLoading] = useState(true);

  // Form Modal
  const [showModal, setShowModal] = useState(false);
  const [selectedProjectId, setSelectedProjectId] = useState<number | "">("");
  const [title, setTitle] = useState("");
  const [amount, setAmount] = useState<number | "">("");
  const [category, setCategory] = useState("TRAVEL");
  const [notes, setNotes] = useState("");
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    loadData();
  }, []);

  async function loadData() {
    try {
      setLoading(true);
      const [expRes, projRes] = await Promise.all([
        fetchApi<{ data: WorkerExpense[] }>("/api/v1/worker/expenses"),
        fetchApi<{ data: WorkerProject[] }>("/api/v1/worker/projects"),
      ]);
      setExpenses(expRes.data || []);
      setProjects(projRes.data || []);
      if (projRes.data && projRes.data.length > 0) {
        setSelectedProjectId(projRes.data[0].id);
      }
    } catch (err) {
      console.error("Failed to load expenses:", err);
    } finally {
      setLoading(false);
    }
  }

  async function handleSubmitExpense(e: React.FormEvent) {
    e.preventDefault();
    if (!selectedProjectId || !title.trim() || !amount) return;

    try {
      setSubmitting(true);
      await fetchApi("/api/v1/worker/expenses", {
        method: "POST",
        body: JSON.stringify({
          project_id: Number(selectedProjectId),
          title,
          amount: Number(amount),
          category,
          receipt_storage_key: `projects/${selectedProjectId}/expenses/receipt_${Date.now()}.jpg`,
          notes: notes.trim() || null,
        }),
      });

      setShowModal(false);
      setTitle("");
      setAmount("");
      setNotes("");
      await loadData();
      alert("Expense reimbursement claim submitted for studio approval.");
    } catch (err: any) {
      alert("Failed to submit claim: " + err.message);
    } finally {
      setSubmitting(false);
    }
  }

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "APPROVED":
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-900/60 text-emerald-300 border border-emerald-700/50">Approved</span>;
      case "PAID":
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-900/60 text-blue-300 border border-blue-700/50">Paid & Reconciled</span>;
      case "REJECTED":
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-900/60 text-red-300 border border-red-700/50">Rejected</span>;
      default:
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-900/60 text-amber-300 border border-amber-700/50">Under Review</span>;
    }
  };

  const totalClaimed = expenses.reduce((sum, e) => sum + e.amount, 0);
  const totalApproved = expenses
    .filter((e) => e.status === "APPROVED" || e.status === "PAID")
    .reduce((sum, e) => sum + e.amount, 0);

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold tracking-tight text-white">Expense Claims & Reimbursements</h1>
          <p className="text-sm text-zinc-400 mt-1">
            Submit field production expenses, transportation, rental costs, and track approval status.
          </p>
        </div>

        <button
          onClick={() => setShowModal(true)}
          className="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-black font-extrabold text-xs rounded-xl shadow transition"
        >
          + Submit Expense Claim
        </button>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div className="p-5 bg-zinc-900 border border-zinc-800 rounded-xl">
          <span className="text-xs text-zinc-400">Total Approved & Paid</span>
          <p className="text-2xl font-extrabold text-emerald-400 font-mono mt-1">
            IDR {totalApproved.toLocaleString("id-ID")}
          </p>
        </div>
        <div className="p-5 bg-zinc-900 border border-zinc-800 rounded-xl">
          <span className="text-xs text-zinc-400">Total Claims Submitted</span>
          <p className="text-2xl font-extrabold text-white font-mono mt-1">
            IDR {totalClaimed.toLocaleString("id-ID")}
          </p>
        </div>
      </div>

      {/* Expenses Table */}
      <div className="bg-zinc-900 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl">
        <div className="p-4 border-b border-zinc-800 flex items-center justify-between">
          <h3 className="text-sm font-bold text-white">Claims History</h3>
        </div>

        {loading ? (
          <div className="p-8 text-center text-zinc-500 text-xs">Loading claims history...</div>
        ) : expenses.length === 0 ? (
          <div className="p-12 text-center text-zinc-500 text-xs">
            <span>No expense claims submitted yet.</span>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead className="bg-zinc-950 text-zinc-400 border-b border-zinc-800">
                <tr>
                  <th className="py-3 px-4">Title & Description</th>
                  <th className="py-3 px-4">Project</th>
                  <th className="py-3 px-4">Category</th>
                  <th className="py-3 px-4 text-right">Amount</th>
                  <th className="py-3 px-4 text-center">Status</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-zinc-800 text-zinc-300">
                {expenses.map((exp) => (
                  <tr key={exp.id} className="hover:bg-zinc-800/40">
                    <td className="py-3 px-4">
                      <span className="font-semibold text-white block">{exp.title}</span>
                      {exp.notes && <span className="text-zinc-500 text-[11px]">{exp.notes}</span>}
                      {exp.rejection_reason && (
                        <span className="text-red-400 text-[11px] block mt-1">Reason: {exp.rejection_reason}</span>
                      )}
                    </td>
                    <td className="py-3 px-4 text-zinc-400">{exp.project_name || `#${exp.project_id}`}</td>
                    <td className="py-3 px-4 text-zinc-400">{exp.category}</td>
                    <td className="py-3 px-4 text-right font-mono font-bold text-amber-400">
                      IDR {Number(exp.amount).toLocaleString("id-ID")}
                    </td>
                    <td className="py-3 px-4 text-center">{getStatusBadge(exp.status)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* NEW EXPENSE CLAIM MODAL */}
      {showModal && (
        <div className="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <form
            onSubmit={handleSubmitExpense}
            className="bg-zinc-900 border border-zinc-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl"
          >
            <h3 className="text-lg font-bold text-white">Submit Reimbursement Claim</h3>

            <div className="space-y-3 pt-2">
              <div>
                <label className="text-xs text-zinc-400 block mb-1">Production Project</label>
                <select
                  required
                  value={selectedProjectId}
                  onChange={(e) => setSelectedProjectId(Number(e.target.value))}
                  className="w-full px-3 py-2 bg-zinc-950 border border-zinc-800 rounded-lg text-xs text-white focus:outline-none focus:border-amber-500"
                >
                  {projects.map((p) => (
                    <option key={p.id} value={p.id}>
                      {p.name} ({p.project_number})
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="text-xs text-zinc-400 block mb-1">Expense Title</label>
                <input
                  type="text"
                  required
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  placeholder="e.g. Toll, Fuel & Equipment Rental"
                  className="w-full px-3 py-2 bg-zinc-950 border border-zinc-800 rounded-lg text-xs text-white focus:outline-none focus:border-amber-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="text-xs text-zinc-400 block mb-1">Amount (IDR)</label>
                  <input
                    type="number"
                    required
                    min={1000}
                    value={amount}
                    onChange={(e) => setAmount(Number(e.target.value))}
                    placeholder="e.g. 500000"
                    className="w-full px-3 py-2 bg-zinc-950 border border-zinc-800 rounded-lg text-xs text-white font-mono focus:outline-none focus:border-amber-500"
                  />
                </div>

                <div>
                  <label className="text-xs text-zinc-400 block mb-1">Category</label>
                  <select
                    value={category}
                    onChange={(e) => setCategory(e.target.value)}
                    className="w-full px-3 py-2 bg-zinc-950 border border-zinc-800 rounded-lg text-xs text-white focus:outline-none focus:border-amber-500"
                  >
                    <option value="TRAVEL">Travel / Transport</option>
                    <option value="MEALS">Meals & Catering</option>
                    <option value="EQUIPMENT_RENTAL">Equipment Rental</option>
                    <option value="ACCOMMODATION">Accommodation</option>
                    <option value="OTHER">Other Expense</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="text-xs text-zinc-400 block mb-1">Notes / Description</label>
                <textarea
                  value={notes}
                  onChange={(e) => setNotes(e.target.value)}
                  placeholder="Item details, shoot day context, or vendor note..."
                  rows={3}
                  className="w-full px-3 py-2 bg-zinc-950 border border-zinc-800 rounded-lg text-xs text-white focus:outline-none focus:border-amber-500 resize-none"
                />
              </div>
            </div>

            <div className="flex justify-end gap-3 pt-4 border-t border-zinc-800">
              <button
                type="button"
                onClick={() => setShowModal(false)}
                className="px-4 py-2 bg-zinc-800 text-zinc-400 hover:text-white rounded-lg text-xs"
              >
                Cancel
              </button>
              <button
                type="submit"
                disabled={submitting}
                className="px-5 py-2 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-black font-bold rounded-lg text-xs"
              >
                {submitting ? "Submitting..." : "Submit Claim"}
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
