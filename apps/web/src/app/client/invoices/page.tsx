"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { fetchApi } from "@/lib/api";

interface InvoiceItem {
  id: number;
  name: string;
  description?: string;
  quantity: number;
  unit_price: number;
  line_total: number;
}

interface Invoice {
  id: number;
  invoice_number: string;
  invoice_type: string;
  amount: number;
  paid_amount: number;
  status: string;
  due_at?: string;
  paid_at?: string;
  created_at: string;
  items: InvoiceItem[];
}

export default function ClientInvoicesPage() {
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [loading, setLoading] = useState(true);
  const [payingInvoiceId, setPayingInvoiceId] = useState<number | null>(null);

  useEffect(() => {
    loadInvoices();
  }, []);

  async function loadInvoices() {
    try {
      setLoading(true);
      const res = await fetchApi<{ data: Invoice[] }>("/api/v1/client/invoices");
      setInvoices(res.data || []);
    } catch (err) {
      console.error("Failed to load invoices:", err);
    } finally {
      setLoading(false);
    }
  }

  async function handlePayOnline(invoiceId: number) {
    try {
      setPayingInvoiceId(invoiceId);
      const res = await fetchApi<{
        data: {
          payment_url: string;
          transaction_id: number;
          merchant_order_id: string;
        };
      }>(`/api/v1/client/invoices/${invoiceId}/pay`, {
        method: "POST",
      });

      if (res.data?.payment_url) {
        window.open(res.data.payment_url, "_blank");
      }
    } catch (err: any) {
      alert("Payment initialization error: " + err.message);
    } finally {
      setPayingInvoiceId(null);
    }
  }

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "PAID":
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-900/60 text-emerald-300 border border-emerald-700/50">Paid & Verified</span>;
      case "ISSUED":
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-900/60 text-amber-300 border border-amber-700/50">Awaiting Payment</span>;
      case "VOID":
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-900/60 text-red-300 border border-red-700/50">Void</span>;
      default:
        return <span className="px-2.5 py-1 text-xs font-semibold rounded-full bg-zinc-800 text-zinc-400 border border-zinc-700">{status}</span>;
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold tracking-tight text-white">Invoices & Billing</h1>
        <p className="text-sm text-zinc-400 mt-1">
          Production invoices, down payments, instant QRIS/Virtual Account gateway, and payment receipts.
        </p>
      </div>

      {loading ? (
        <div className="h-64 bg-zinc-900/60 border border-zinc-800 rounded-xl animate-pulse" />
      ) : invoices.length === 0 ? (
        <div className="p-12 text-center bg-zinc-900/40 border border-zinc-800 rounded-xl">
          <span className="text-4xl">💳</span>
          <h3 className="text-lg font-semibold text-white mt-3">No Invoices Issued</h3>
          <p className="text-sm text-zinc-400 mt-1">
            Invoices will be generated automatically upon quotation acceptance.
          </p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {invoices.map((inv) => (
            <div
              key={inv.id}
              className="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 flex flex-col justify-between shadow-xl"
            >
              <div>
                <div className="flex items-center justify-between">
                  <span className="font-mono text-xs text-amber-500 font-extrabold">{inv.invoice_number}</span>
                  {getStatusBadge(inv.status)}
                </div>

                <div className="mt-4 flex items-baseline justify-between">
                  <span className="text-xs uppercase tracking-wider text-zinc-500 font-bold">
                    {inv.invoice_type} Invoice
                  </span>
                  <span className="text-2xl font-black text-white font-mono">
                    IDR {Number(inv.amount).toLocaleString("id-ID")}
                  </span>
                </div>

                <div className="mt-4 pt-4 border-t border-zinc-800/80 space-y-2 text-xs text-zinc-400">
                  {inv.due_at && (
                    <div className="flex justify-between">
                      <span>Due Date:</span>
                      <span className="text-zinc-200 font-medium">{inv.due_at}</span>
                    </div>
                  )}
                  {inv.paid_at && (
                    <div className="flex justify-between">
                      <span>Paid On:</span>
                      <span className="text-emerald-400 font-medium">{inv.paid_at}</span>
                    </div>
                  )}
                </div>

                {/* Item List */}
                {inv.items && inv.items.length > 0 && (
                  <div className="mt-4 p-3 bg-zinc-950 rounded-xl border border-zinc-800/60 space-y-1 text-xs">
                    {inv.items.map((item) => (
                      <div key={item.id} className="flex justify-between text-zinc-300">
                        <span className="truncate max-w-[200px]">{item.name}</span>
                        <span className="font-mono text-zinc-400">
                          IDR {Number(item.line_total).toLocaleString("id-ID")}
                        </span>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              {/* Action Button */}
              <div className="mt-6 pt-4 border-t border-zinc-800">
                {inv.status === "ISSUED" ? (
                  <button
                    onClick={() => handlePayOnline(inv.id)}
                    disabled={payingInvoiceId === inv.id}
                    className="w-full py-3 bg-amber-500 hover:bg-amber-400 active:scale-95 disabled:opacity-50 text-black font-extrabold text-xs rounded-xl shadow-lg transition duration-150 flex items-center justify-center gap-2"
                  >
                    <span>{payingInvoiceId === inv.id ? "Connecting Gateway..." : "⚡ Pay Invoice with Duitku (QRIS / VA)"}</span>
                  </button>
                ) : (
                  <div className="text-center py-2 text-xs text-emerald-400 font-semibold bg-emerald-950/40 border border-emerald-800/40 rounded-xl">
                    ✓ Paid & Reconciled
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
