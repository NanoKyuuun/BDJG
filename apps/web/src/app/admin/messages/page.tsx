"use client";

import { useState } from "react";

interface Conversation {
  id: string;
  sender: string;
  role: string;
  project: string;
  lastMessage: string;
  time: string;
  unread: boolean;
  avatar: string;
  messages: Array<{
    sender: string;
    text: string;
    time: string;
    isStudio: boolean;
  }>;
}

const INITIAL_CONVERSATIONS: Conversation[] = [
  {
    id: "c1",
    sender: "Raden Mas Danang",
    role: "Client",
    project: "Danang & Ayu Royal Wedding",
    lastMessage: "Apakah tim drone sudah mengurus izin terbang di area Grand Ballroom?",
    time: "10:24 WIB",
    unread: true,
    avatar: "RD",
    messages: [
      { sender: "Raden Mas Danang", text: "Selamat pagi tim BDJG, izin mengonfirmasi jadwal rundown akad nikah.", time: "09:15", isStudio: false },
      { sender: "Admin Studio", text: "Pagi Mas Danang! Rundown sudah kami sinkronkan dengan tim multi-cam.", time: "09:30", isStudio: true },
      { sender: "Raden Mas Danang", text: "Apakah tim drone sudah mengurus izin terbang di area Grand Ballroom?", time: "10:24", isStudio: false },
    ],
  },
  {
    id: "c2",
    sender: "Maya Indah",
    role: "Prospective Client",
    project: "Inquiry: Resort Campaign",
    lastMessage: "Terima kasih, kami menunggu draf quotation revisinya.",
    time: "Kemarin",
    unread: false,
    avatar: "MI",
    messages: [
      { sender: "Maya Indah", text: "Halo, kami ingin menanyakan estimasi paket video 3 hari di Mentawai.", time: "Kemarin 14:00", isStudio: false },
      { sender: "Admin Studio", text: "Halo Mbak Maya, paket sudah kami siapkan dengan rincian kru dan logistik.", time: "Kemarin 15:20", isStudio: true },
      { sender: "Maya Indah", text: "Terima kasih, kami menunggu draf quotation revisinya.", time: "Kemarin 16:45", isStudio: false },
    ],
  },
  {
    id: "c3",
    sender: "Golden Videographer",
    role: "Lead Worker",
    project: "Savanna Whisper Campaign",
    lastMessage: "File proxy 4K sudah selesai diexport ke storage server.",
    time: "2 Hari lalu",
    unread: false,
    avatar: "GV",
    messages: [
      { sender: "Golden Videographer", text: "File proxy 4K sudah selesai diexport ke storage server.", time: "08:10", isStudio: false },
    ],
  },
];

export default function AdminMessagesPage() {
  const [conversations, setConversations] = useState<Conversation[]>(INITIAL_CONVERSATIONS);
  const [activeId, setActiveId] = useState<string>("c1");
  const [replyText, setReplyText] = useState("");

  const activeConv = conversations.find((c) => c.id === activeId) || conversations[0];

  const handleSendReply = (e: React.FormEvent) => {
    e.preventDefault();
    if (!replyText.trim()) return;

    const newMsg = {
      sender: "Admin Studio",
      text: replyText.trim(),
      time: "Baru saja",
      isStudio: true,
    };

    setConversations((prev) =>
      prev.map((c) =>
        c.id === activeId
          ? {
              ...c,
              lastMessage: replyText.trim(),
              time: "Baru saja",
              unread: false,
              messages: [...c.messages, newMsg],
            }
          : c
      )
    );
    setReplyText("");
  };

  return (
    <>
      <div className="pagehead">
        <div>
          <h1>💬 Studio Communication Hub</h1>
          <p>Pusat percakapan langsung dengan klien, calon klien (inquiries), dan kru lapangan</p>
        </div>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "340px 1fr", gap: 20, minHeight: 600 }}>
        {/* Conversations List */}
        <div className="card" style={{ display: "flex", flexDirection: "column", height: "100%" }}>
          <div className="chead">
            <h3>Kotak Masuk ({conversations.length})</h3>
          </div>
          <div className="cbody" style={{ padding: 0, overflowY: "auto", flex: 1 }}>
            {conversations.map((c) => {
              const active = c.id === activeId;
              return (
                <div
                  key={c.id}
                  onClick={() => {
                    setActiveId(c.id);
                    setConversations((prev) =>
                      prev.map((item) => (item.id === c.id ? { ...item, unread: false } : item))
                    );
                  }}
                  style={{
                    padding: "14px 16px",
                    borderBottom: "1px solid rgba(255,255,255,0.05)",
                    cursor: "pointer",
                    background: active ? "rgba(92, 124, 255, 0.12)" : "transparent",
                    borderLeft: active ? "3px solid #5c7cff" : "3px solid transparent",
                    transition: "all 0.15s",
                  }}
                >
                  <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 4 }}>
                    <div style={{ fontWeight: 700, fontSize: 13, color: active ? "#5c7cff" : "#f2f1ed" }}>
                      {c.sender}
                    </div>
                    <div style={{ fontSize: 10, color: "var(--muted)" }}>{c.time}</div>
                  </div>
                  <div style={{ fontSize: 11, color: "var(--accent)", marginBottom: 4 }}>
                    {c.project}
                  </div>
                  <div
                    style={{
                      fontSize: 12,
                      color: c.unread ? "#ffffff" : "var(--muted)",
                      fontWeight: c.unread ? 600 : 400,
                      overflow: "hidden",
                      textOverflow: "ellipsis",
                      whiteSpace: "nowrap",
                    }}
                  >
                    {c.lastMessage}
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Active Chat Window */}
        <div className="card" style={{ display: "flex", flexDirection: "column", height: "100%" }}>
          <div className="chead" style={{ borderBottom: "1px solid rgba(255,255,255,0.08)" }}>
            <div>
              <div style={{ fontWeight: 700, fontSize: 15, color: "#f2f1ed" }}>{activeConv.sender}</div>
              <div style={{ fontSize: 11, color: "var(--muted)" }}>
                {activeConv.role} • Proyek: <b style={{ color: "var(--accent)" }}>{activeConv.project}</b>
              </div>
            </div>
            <span className="chip c-green" style={{ fontSize: 10 }}>Live Connection</span>
          </div>

          <div
            className="cbody"
            style={{
              flex: 1,
              overflowY: "auto",
              padding: "20px",
              display: "flex",
              flexDirection: "column",
              gap: 12,
              background: "rgba(0,0,0,0.2)",
            }}
          >
            {activeConv.messages.map((m, idx) => (
              <div
                key={idx}
                style={{
                  alignSelf: m.isStudio ? "flex-end" : "flex-start",
                  maxWidth: "75%",
                }}
              >
                <div
                  style={{
                    padding: "10px 14px",
                    borderRadius: 10,
                    fontSize: 13,
                    lineHeight: 1.5,
                    background: m.isStudio ? "#5c7cff" : "#1e1e1e",
                    color: m.isStudio ? "#090909" : "#f2f1ed",
                    fontWeight: m.isStudio ? 600 : 400,
                    border: m.isStudio ? "none" : "1px solid rgba(255,255,255,0.08)",
                  }}
                >
                  {m.text}
                </div>
                <div
                  style={{
                    fontSize: 10,
                    color: "var(--muted)",
                    marginTop: 3,
                    textAlign: m.isStudio ? "right" : "left",
                  }}
                >
                  {m.time}
                </div>
              </div>
            ))}
          </div>

          <form onSubmit={handleSendReply} style={{ padding: "14px 16px", borderTop: "1px solid rgba(255,255,255,0.08)", display: "flex", gap: 10 }}>
            <input
              type="text"
              className="form-control"
              value={replyText}
              onChange={(e) => setReplyText(e.target.value)}
              placeholder={`Ketik pesan balasan resmi untuk ${activeConv.sender}...`}
              style={{ flex: 1 }}
            />
            <button type="submit" className="btn btn-primary" style={{ padding: "0 20px" }}>
              Kirim ↗
            </button>
          </form>
        </div>
      </div>
    </>
  );
}
