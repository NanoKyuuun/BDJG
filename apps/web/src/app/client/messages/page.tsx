"use client";

import { useAuth } from "@/lib/use-auth";

export default function ClientMessagesPage() {
  const { user } = useAuth();

  return (
    <>
      <div className="pagehead">
        <div>
          <div style={{ fontSize: 11, fontWeight: 700, color: "var(--blue)", letterSpacing: 1.2 }}>
            DIRECT COMMUNICATION
          </div>
          <h1 style={{ marginTop: 4 }}>💬 Studio Communication &amp; Briefs</h1>
          <p>Kanal komunikasi langsung dengan tim Project Manager BDJG Creative Studio</p>
        </div>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 20 }}>
        <div className="card" style={{ padding: 24 }}>
          <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 8 }}>📞 Hubungi Tim Produksi</h3>
          <p style={{ fontSize: 12.5, color: "var(--muted)", lineHeight: 1.5, marginBottom: 20 }}>
            Untuk pertanyaan operasional, perubahan konsep, atau koordinasi mendesak, Anda dapat menghubungi PIC Studio BDJG.
          </p>

          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            <div style={{ padding: "12px 14px", background: "var(--card2)", borderRadius: 8, border: "1px solid var(--line)" }}>
              <div style={{ fontSize: 11, fontWeight: 700, color: "var(--muted)" }}>EMAIL OPERASIONAL</div>
              <div style={{ fontSize: 14, fontWeight: 800, marginTop: 2, color: "var(--orange)" }}>hello@bdjg.studio</div>
            </div>

            <div style={{ padding: "12px 14px", background: "var(--card2)", borderRadius: 8, border: "1px solid var(--line)" }}>
              <div style={{ fontSize: 11, fontWeight: 700, color: "var(--muted)" }}>WHATSAPP STUDIO SUPPORT</div>
              <div style={{ fontSize: 14, fontWeight: 800, marginTop: 2, color: "var(--green)" }}>+62 812-3456-7890</div>
            </div>
          </div>
        </div>

        <div className="card" style={{ padding: 24 }}>
          <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 8 }}>📌 Jam Operasional Studio</h3>
          <p style={{ fontSize: 12.5, color: "var(--muted)", lineHeight: 1.5, marginBottom: 16 }}>
            Tim produksi BDJG memproses catatan revisi dan jadwal pada jam kerja berikut:
          </p>

          <div style={{ fontSize: 13, display: "flex", flexDirection: "column", gap: 8, color: "var(--ink)" }}>
            <div style={{ display: "flex", justifyContent: "space-between" }}>
              <span>Senin – Jumat</span>
              <strong style={{ color: "var(--yellow)" }}>09:00 – 18:00 WIB</strong>
            </div>
            <div style={{ display: "flex", justifyContent: "space-between" }}>
              <span>Sabtu (Shooting Sched.)</span>
              <strong style={{ color: "var(--orange)" }}>Sesuai Jadwal Lapangan</strong>
            </div>
            <div style={{ display: "flex", justifyContent: "space-between" }}>
              <span>Minggu &amp; Libur Nasional</span>
              <strong style={{ color: "var(--muted)" }}>Tutup</strong>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
