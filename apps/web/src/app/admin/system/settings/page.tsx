"use client";

import { useEffect, useState } from "react";
import { useAuth } from "@/lib/use-auth";

export default function AdminSettingsPage() {
  const { user } = useAuth();
  const [copied, setCopied] = useState(false);

  const handleCopyWebhook = () => {
    navigator.clipboard.writeText("http://localhost:8000/api/v1/webhooks/duitku");
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <>
      <div className="pagehead">
        <div>
          <div style={{ fontSize: 11, fontWeight: 700, color: "var(--orange)", letterSpacing: 1.2 }}>
            SYSTEM &amp; INTEGRATIONS
          </div>
          <h1 style={{ marginTop: 4 }}>⚙️ System &amp; Studio Settings</h1>
          <p>Konfigurasi gateway pembayaran, penyimpanan media, dan parameter studio BDJG</p>
        </div>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 20 }}>
        {/* Payment Gateway Sandbox Config */}
        <div className="card" style={{ padding: 22 }}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 14 }}>
            <h3 style={{ fontSize: 16, fontWeight: 700 }}>💳 Duitku Payment Gateway</h3>
            <span className="chip c-green">SANDBOX ACTIVE</span>
          </div>

          <p style={{ fontSize: 12, color: "var(--muted)", marginBottom: 16, lineHeight: 1.5 }}>
            Sistem pembayaran menggunakan Duitku Payment Gateway dengan verifikasi signature HMAC-SHA256 untuk
            pelunasan otomatis dan aktivasi proyek.
          </p>

          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            <div style={{ padding: "10px 14px", background: "var(--card2)", borderRadius: 8, border: "1px solid var(--line)" }}>
              <div style={{ fontSize: 10.5, fontWeight: 700, color: "var(--muted)" }}>MERCHANT CODE</div>
              <div style={{ fontSize: 14, fontWeight: 800, marginTop: 2, fontFamily: "monospace" }}>D12345 (Sandbox)</div>
            </div>

            <div style={{ padding: "10px 14px", background: "var(--card2)", borderRadius: 8, border: "1px solid var(--line)" }}>
              <div style={{ fontSize: 10.5, fontWeight: 700, color: "var(--muted)" }}>WEBHOOK CALLBACK URL</div>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginTop: 4 }}>
                <span style={{ fontSize: 12, fontFamily: "monospace", color: "var(--orange)" }}>
                  http://localhost:8000/api/v1/webhooks/duitku
                </span>
                <button className="btn btn-o" style={{ padding: "3px 8px", fontSize: 11 }} onClick={handleCopyWebhook}>
                  {copied ? "Tersalin!" : "Salin"}
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Media & Storage Driver */}
        <div className="card" style={{ padding: 22 }}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 14 }}>
            <h3 style={{ fontSize: 16, fontWeight: 700 }}>🗄️ Storage &amp; Video Engine</h3>
            <span className="chip c-blue">S3 / LOCAL READY</span>
          </div>

          <p style={{ fontSize: 12, color: "var(--muted)", marginBottom: 16, lineHeight: 1.5 }}>
            Pengelolaan file master, kompresi proxy video via background queue FFmpeg, dan streaming terproteksi.
          </p>

          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            <div style={{ padding: "10px 14px", background: "var(--card2)", borderRadius: 8, border: "1px solid var(--line)" }}>
              <div style={{ fontSize: 10.5, fontWeight: 700, color: "var(--muted)" }}>DEFAULT DISK DRIVER</div>
              <div style={{ fontSize: 14, fontWeight: 800, marginTop: 2 }}>Local Storage / S3 Compatible</div>
            </div>

            <div style={{ padding: "10px 14px", background: "var(--card2)", borderRadius: 8, border: "1px solid var(--line)" }}>
              <div style={{ fontSize: 10.5, fontWeight: 700, color: "var(--muted)" }}>VIDEO TRANSCODING</div>
              <div style={{ fontSize: 14, fontWeight: 800, marginTop: 2 }}>FFmpeg Queued Jobs (H.264 1080p Proxy)</div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
