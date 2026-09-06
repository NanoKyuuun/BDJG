"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface ServiceItem {
  id: number;
  name: string;
  slug: string;
  packages?: Array<{
    id: number;
    name: string;
    price: number;
    description_external?: string;
  }>;
}

function formatRupiah(amount: number): string {
  return "Rp " + (amount || 0).toLocaleString("id-ID");
}

export default function AdminServicesPage() {
  const [services, setServices] = useState<ServiceItem[]>([]);
  const [loading, setLoading] = useState(true);

  const loadServices = () => {
    setLoading(true);
    fetchApi<{ data: ServiceItem[] }>("/api/v1/admin/catalog/services")
      .then((res) => setServices(res.data || []))
      .catch((err) => console.error("Failed to load services:", err))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadServices();
  }, []);

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>📦 Services & Packages Catalog</h1>
          <p>Katalog master layanan video/foto studio, paket harga, dan opsi add-ons</p>
        </div>
      </div>

      <div className="card">
        <div className="chead">
          <h3>Katalog Layanan Aktif ({services.length})</h3>
        </div>
        <div className="cbody" style={{ padding: 0 }}>
          {loading ? (
            <div className="empty">Memuat data katalog...</div>
          ) : services.length === 0 ? (
            <div className="empty">Belum ada layanan terdaftar di katalog.</div>
          ) : (
            <div style={{ overflowX: "auto" }}>
              <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
                <thead>
                  <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                    <th style={{ padding: "12px 16px" }}>Nama Layanan</th>
                    <th style={{ padding: "12px 16px" }}>Slug URL</th>
                    <th style={{ padding: "12px 16px" }}>Paket Terkait</th>
                  </tr>
                </thead>
                <tbody>
                  {services.map((s) => (
                    <tr
                      key={s.id}
                      style={{
                        borderBottom: "1px solid rgba(255,255,255,0.04)",
                        transition: "background 0.2s",
                      }}
                    >
                      <td style={{ padding: "12px 16px", fontWeight: 700, color: "#FFFFFF" }}>
                        {s.name}
                      </td>
                      <td style={{ padding: "12px 16px", color: "var(--muted)", fontSize: 12 }}>
                        /{s.slug}
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        {s.packages && s.packages.length > 0 ? (
                          <div style={{ display: "flex", gap: 6, flexWrap: "wrap" }}>
                            {s.packages.map((pkg) => (
                              <span key={pkg.id} className="chip c-black" style={{ fontSize: 11 }}>
                                {pkg.name} — {formatRupiah(pkg.price)}
                              </span>
                            ))}
                          </div>
                        ) : (
                          <span style={{ fontSize: 11, color: "var(--muted)" }}>-</span>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </>
  );
}
