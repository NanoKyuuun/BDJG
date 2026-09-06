"use client";

import { useEffect, useState } from "react";
import { fetchApi } from "@/lib/api";

interface UserItem {
  id: number;
  public_id: string;
  name: string;
  email: string;
  status: string;
  roles: string[];
  created_at: string;
}

interface RoleItem {
  id: number;
  name: string;
  permissions_count: number;
}

export default function AdminUsersPage() {
  const [users, setUsers] = useState<UserItem[]>([]);
  const [roles, setRoles] = useState<RoleItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [roleFilter, setRoleFilter] = useState("");
  const [statusFilter, setStatusFilter] = useState("");

  // Create user form state
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [formName, setFormName] = useState("");
  const [formEmail, setFormEmail] = useState("");
  const [formPassword, setFormPassword] = useState("");
  const [formRole, setFormRole] = useState("ADMIN");

  const loadData = () => {
    setLoading(true);
    const query = new URLSearchParams();
    if (search) query.set("search", search);
    if (roleFilter) query.set("role", roleFilter);
    if (statusFilter) query.set("status", statusFilter);

    Promise.all([
      fetchApi<{ data: UserItem[] }>(`/api/v1/admin/system/users?${query.toString()}`),
      fetchApi<{ data: RoleItem[] }>("/api/v1/admin/system/roles").catch(() => ({ data: [] })),
    ])
      .then(([userRes, roleRes]) => {
        setUsers(userRes.data || []);
        setRoles(roleRes.data || []);
      })
      .catch((err) => console.error("Failed to load users:", err))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadData();
  }, [roleFilter, statusFilter]);

  const handleStatusChange = async (userId: number, newStatus: string) => {
    try {
      await fetchApi(`/api/v1/admin/system/users/${userId}/status`, {
        method: "PATCH",
        body: JSON.stringify({ status: newStatus }),
      });
      loadData();
    } catch (err: any) {
      alert(err.message || "Gagal mengubah status pengguna");
    }
  };

  const handleCreateUser = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await fetchApi("/api/v1/admin/system/users", {
        method: "POST",
        body: JSON.stringify({
          name: formName,
          email: formEmail,
          password: formPassword,
          roles: [formRole],
          status: "ACTIVE",
        }),
      });
      alert("Pengguna baru berhasil dibuat!");
      setShowCreateModal(false);
      setFormName("");
      setFormEmail("");
      setFormPassword("");
      loadData();
    } catch (err: any) {
      alert(err.message || "Gagal membuat pengguna");
    }
  };

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>🛡️ Users & Role Management</h1>
          <p>Kelola akun pengguna, kontrol peran akses Spatie RBAC, dan status login</p>
        </div>
        <button className="btn btn-p" onClick={() => setShowCreateModal(true)}>
          + Tambah Pengguna
        </button>
      </div>

      {showCreateModal && (
        <div className="card mb" style={{ marginBottom: 16, border: "1px solid #F97316" }}>
          <div className="chead">
            <h3 style={{ color: "#F97316" }}>👤 Tambah Akun Pengguna Baru</h3>
            <button className="btn btn-x sm" onClick={() => setShowCreateModal(false)}>
              Batal
            </button>
          </div>
          <div className="cbody">
            <form onSubmit={handleCreateUser} style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                <div>
                  <label style={{ fontSize: 11, color: "var(--muted)", fontWeight: 600 }}>Nama Lengkap</label>
                  <input
                    type="text"
                    required
                    placeholder="Contoh: Budi Prakoso"
                    value={formName}
                    onChange={(e) => setFormName(e.target.value)}
                    style={{
                      width: "100%",
                      padding: "8px 12px",
                      background: "#09090B",
                      border: "1px solid rgba(255,255,255,0.15)",
                      borderRadius: 6,
                      color: "#FFF",
                      fontSize: 13,
                      marginTop: 4,
                    }}
                  />
                </div>
                <div>
                  <label style={{ fontSize: 11, color: "var(--muted)", fontWeight: 600 }}>Email Login</label>
                  <input
                    type="email"
                    required
                    placeholder="email@bdjg.studio"
                    value={formEmail}
                    onChange={(e) => setFormEmail(e.target.value)}
                    style={{
                      width: "100%",
                      padding: "8px 12px",
                      background: "#09090B",
                      border: "1px solid rgba(255,255,255,0.15)",
                      borderRadius: 6,
                      color: "#FFF",
                      fontSize: 13,
                      marginTop: 4,
                    }}
                  />
                </div>
              </div>

              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                <div>
                  <label style={{ fontSize: 11, color: "var(--muted)", fontWeight: 600 }}>Password Awal</label>
                  <input
                    type="password"
                    required
                    minLength={8}
                    placeholder="Min 8 karakter..."
                    value={formPassword}
                    onChange={(e) => setFormPassword(e.target.value)}
                    style={{
                      width: "100%",
                      padding: "8px 12px",
                      background: "#09090B",
                      border: "1px solid rgba(255,255,255,0.15)",
                      borderRadius: 6,
                      color: "#FFF",
                      fontSize: 13,
                      marginTop: 4,
                    }}
                  />
                </div>
                <div>
                  <label style={{ fontSize: 11, color: "var(--muted)", fontWeight: 600 }}>Peran Akses (Role)</label>
                  <select
                    value={formRole}
                    onChange={(e) => setFormRole(e.target.value)}
                    style={{
                      width: "100%",
                      padding: "8px 12px",
                      background: "#09090B",
                      border: "1px solid rgba(255,255,255,0.15)",
                      borderRadius: 6,
                      color: "#FFF",
                      fontSize: 13,
                      marginTop: 4,
                    }}
                  >
                    <option value="ADMIN">ADMIN (Operasional Studio)</option>
                    <option value="WORKER">WORKER (Kru / Tim Lapangan)</option>
                    <option value="CLIENT">CLIENT (Klien)</option>
                    <option value="OWNER">OWNER (Super Admin)</option>
                  </select>
                </div>
              </div>

              <button type="submit" className="btn btn-p" style={{ alignSelf: "flex-end" }}>
                Simpan & Aktifkan Akun
              </button>
            </form>
          </div>
        </div>
      )}

      <div style={{ display: "flex", gap: 8, marginBottom: 16, flexWrap: "wrap", alignItems: "center" }}>
        <input
          type="text"
          placeholder="Cari nama atau email..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          onKeyDown={(e) => e.key === "Enter" && loadData()}
          style={{
            padding: "7px 12px",
            background: "#09090B",
            border: "1px solid rgba(255,255,255,0.12)",
            borderRadius: 6,
            color: "#FFF",
            fontSize: 12,
            width: 220,
          }}
        />
        <button className="btn btn-x" onClick={loadData}>
          Cari
        </button>

        <div style={{ marginLeft: "auto", display: "flex", gap: 6 }}>
          {["", "OWNER", "ADMIN", "WORKER", "CLIENT"].map((r) => (
            <button
              key={r}
              className={`fchip ${roleFilter === r ? "on" : ""}`}
              onClick={() => setRoleFilter(r)}
            >
              {r || "Semua Role"}
            </button>
          ))}
        </div>
      </div>

      <div className="card">
        <div className="chead">
          <h3>Daftar Pengguna Studio ({users.length})</h3>
        </div>
        <div className="cbody" style={{ padding: 0 }}>
          {loading ? (
            <div className="empty">Memuat data pengguna...</div>
          ) : users.length === 0 ? (
            <div className="empty">Tidak ada pengguna yang cocok.</div>
          ) : (
            <div style={{ overflowX: "auto" }}>
              <table style={{ width: "100%", textAlign: "left", borderCollapse: "collapse", fontSize: 13 }}>
                <thead>
                  <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.08)", color: "var(--muted)" }}>
                    <th style={{ padding: "12px 16px" }}>Nama Pengguna</th>
                    <th style={{ padding: "12px 16px" }}>Email</th>
                    <th style={{ padding: "12px 16px" }}>Peran (Roles)</th>
                    <th style={{ padding: "12px 16px" }}>Status Akun</th>
                    <th style={{ padding: "12px 16px", textAlign: "right" }}>Kontrol Status</th>
                  </tr>
                </thead>
                <tbody>
                  {users.map((u) => (
                    <tr
                      key={u.id}
                      style={{
                        borderBottom: "1px solid rgba(255,255,255,0.04)",
                        transition: "background 0.2s",
                      }}
                    >
                      <td style={{ padding: "12px 16px", fontWeight: 700, color: "#FFFFFF" }}>
                        {u.name}
                      </td>
                      <td style={{ padding: "12px 16px", color: "var(--muted)" }}>{u.email}</td>
                      <td style={{ padding: "12px 16px" }}>
                        <div style={{ display: "flex", gap: 4, flexWrap: "wrap" }}>
                          {u.roles.map((r) => (
                            <span
                              key={r}
                              className={`chip ${
                                r === "OWNER"
                                  ? "c-black"
                                  : r === "ADMIN"
                                  ? "c-orange"
                                  : r === "WORKER"
                                  ? "c-blue"
                                  : "c-green"
                              }`}
                              style={{ fontSize: 10, fontWeight: 700 }}
                            >
                              {r}
                            </span>
                          ))}
                        </div>
                      </td>
                      <td style={{ padding: "12px 16px" }}>
                        <span
                          className={`chip ${
                            u.status === "ACTIVE"
                              ? "c-green"
                              : u.status === "SUSPENDED"
                              ? "c-orange"
                              : "c-red"
                          }`}
                          style={{ fontSize: 11, fontWeight: 700 }}
                        >
                          {u.status}
                        </span>
                      </td>
                      <td style={{ padding: "12px 16px", textAlign: "right" }}>
                        <select
                          value={u.status}
                          onChange={(e) => handleStatusChange(u.id, e.target.value)}
                          style={{
                            padding: "4px 8px",
                            background: "#000",
                            border: "1px solid rgba(255,255,255,0.2)",
                            borderRadius: 4,
                            color: "#FFF",
                            fontSize: 11,
                            cursor: "pointer",
                          }}
                        >
                          <option value="ACTIVE">ACTIVE</option>
                          <option value="SUSPENDED">SUSPENDED</option>
                          <option value="DISABLED">DISABLED</option>
                        </select>
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
