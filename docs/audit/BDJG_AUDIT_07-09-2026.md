# Full Audit Report — BDJG Implementation vs Blueprint

**Tanggal Audit:** 7 September 2026  
**Sumber Kebenaran:** `docs/blueprint.md` + `docs/update/06-09-2026/BLUEPRINT_KATALOG_DAN_PEMESANAN_CLIENT_BDJG.md`  
**Scope:** Backend (Laravel) + Frontend (Next.js) + Database Migrations

---

## Ringkasan Eksekutif

| Kategori | Jumlah Temuan |
|---|---|
| 🔴 Kritis (langgar security/data integrity) | 4 |
| 🟠 Penting (hardcode yang harus dikonfigurasi) | 6 |
| 🟡 Tidak Sesuai Blueprint (fitur kurang/berbeda) | 9 |
| 🟢 Sesuai Blueprint | ✅ |

---

## 🔴 TEMUAN KRITIS

### K-01 — Backend API route `/v1/client/*` masih mengizinkan OWNER dan ADMIN

**File:** [`routes/api.php` L211](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/api/routes/api.php#L211)

```php
// SAAT INI — SALAH
Route::prefix('v1/client')->middleware('role:CLIENT|OWNER|ADMIN')->group(...)

// SEHARUSNYA
Route::prefix('v1/client')->middleware('role:CLIENT')->group(...)
```

**Dampak:** Admin yang mengakses `/api/v1/client/service-orders` akan mendapat error 403 dari controller (karena `$user->clients()->first()` null), TAPI bisa jadi security bypass untuk endpoint yang tidak mengecek client scope. Blueprint §19 aturan 1: *"Client hanya boleh melihat dan mengubah pesanan miliknya sendiri."*

---

### K-02 — Backend API route `/v1/worker/*` masih mengizinkan OWNER dan ADMIN masuk ke worker portal

**File:** [`routes/api.php` L185](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/api/routes/api.php#L185)

```php
// SAAT INI — SALAH
Route::prefix('v1/worker')->middleware('role:WORKER|OWNER|ADMIN')->group(...)

// SEHARUSNYA
Route::prefix('v1/worker')->middleware('role:WORKER')->group(...)
```

**Dampak:** Admin yang memanggil `/api/v1/worker/projects` akan mendapat data worker tanpa ada ownership check yang proper. Blueprint §36: *"Worker scope adalah ASSIGNED."*

---

### K-03 — Status pembayaran dari redirect browser, tanpa PAYMENT_PENDING guard yang cukup

**File:** [`CheckoutStandardOrderAction.php`](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/api/app/Domains/Orders/Actions/CheckoutStandardOrderAction.php)

Setelah DP Invoice dibuat, status langsung `AWAITING_PAYMENT`. Tidak ada mekanisme `PAYMENT_PENDING` yang di-set saat client redirect ke Duitku, sehingga tidak ada perbedaan antara "belum bayar" dan "sedang proses di gateway". Blueprint §9: `PAYMENT_PENDING` harus ada saat pembayaran sedang diproses provider.

---

### K-04 — Idempotency project activation belum ada unique constraint yang kuat

**File:** [`service_orders migration`](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/api/database/migrations/2026_09_07_000001_create_service_orders_table.php#L32)

```php
$table->foreignId('project_id')->nullable()->unique()->constrained('projects')->nullOnDelete();
```

Sudah ada `->unique()` pada `project_id` di `service_orders`, ini bagus. Tapi `ActivateProjectAction` perlu di-audit apakah benar-benar idempotent (cek `lockForUpdate` + double-check `project_id`). Blueprint §19 aturan 7: *"Aktivasi project harus idempotent dan terjadi tepat satu kali."*

---

## 🟠 TEMUAN PENTING — HARDCODED VALUES

### H-01 — DP 30% hardcoded di frontend sebagai teks UI

**File:** [`brief/page.tsx` L299](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/web/src/app/client/orders/[id]/brief/page.tsx#L299)

```tsx
// HARDCODED — SALAH
"Paket standar dalam area Surabaya. Setelah brief dikonfirmasi, Anda dapat langsung 
melakukan pembayaran DP 30% untuk mengunci tanggal jadwal."
```

Blueprint §12.3: *"Nominal DP belum disebutkan pada price list. Nilainya harus dibuat sebagai pengaturan bisnis, bukan ditulis permanen di dalam kode."*

Backend `CheckoutStandardOrderAction.php` sudah benar (membaca dari `package.default_dp_type` dan `package.default_dp_value`), tapi frontend masih menampilkan "30%" secara hardcoded padahal nilai sebenarnya bisa berbeda per paket.

---

### H-02 — "Surabaya" sebagai base area hardcoded di frontend (4 lokasi)

**Files:**
- [`brief/page.tsx` L73](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/web/src/app/client/orders/[id]/brief/page.tsx#L73) — `useState("Surabaya")`
- [`brief/page.tsx` L105](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/web/src/app/client/orders/[id]/brief/page.tsx#L105) — fallback `|| "Surabaya"`
- [`brief/page.tsx` L298](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/web/src/app/client/orders/[id]/brief/page.tsx#L298) — teks *"di luar area dasar Surabaya"*
- [`orders/[id]/page.tsx` L502](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/web/src/app/client/orders/[id]/page.tsx#L502) — *"Dalam Kota Surabaya"*
- [`admin/sales/orders/page.tsx` L402, L531](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/web/src/app/admin/sales/orders/page.tsx#L402)

Blueprint §5.3: *"base area BDJG harus dapat diatur admin."* Kota dan nama base area tidak boleh hardcoded.

---

### H-03 — DP 50% fallback hardcoded di backend

**File:** [`CheckoutStandardOrderAction.php` L37](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/api/app/Domains/Orders/Actions/CheckoutStandardOrderAction.php#L37)

```php
$dpAmount = (int) ($packagePrice * 0.5); // Default 50%
```

Jika paket tidak punya `default_dp_type` atau nilai 0, fallback ke 50%. Ini hardcoded. Seharusnya ambil dari konfigurasi bisnis (misalnya `config('bdjg.default_dp_percentage')` atau dari tabel `settings`).

---

### H-04 — Due date invoice DP hardcoded 2 hari

**File:** [`CheckoutStandardOrderAction.php` L56](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/api/app/Domains/Orders/Actions/CheckoutStandardOrderAction.php#L56)

```php
'due_at' => now()->addDays(2),
```

Blueprint §13: *"durasi slot hold dapat diatur admin."* Due date invoice harusnya juga bisa dikonfigurasi, bukan hardcoded 2 hari.

---

### H-05 — Slot hold 48 jam hardcoded di SubmitServiceOrderAction

**File:** [`SubmitServiceOrderAction.php` L99](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/api/app/Domains/Orders/Actions/SubmitServiceOrderAction.php#L99)

```php
$order->slot_hold_until = now()->addHours(48);
```

Blueprint §13: *"durasi yang dapat diatur admin."* Harus dikonfigurasi, bukan hardcoded.

---

### H-06 — Terms invoice hardcoded sebagai teks statis

**File:** [`CheckoutStandardOrderAction.php` L57](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/api/app/Domains/Orders/Actions/CheckoutStandardOrderAction.php#L57)

```php
'terms' => 'Down Payment (DP) 50% via Duitku Sandbox / Virtual Account.',
```

Dua masalah: (1) menyebut persentase DP hardcoded (50%), (2) menyebut "Sandbox" yang tidak boleh ada di production logic.

---

## 🟡 TIDAK SESUAI BLUEPRINT

### B-01 — Paket "Loyalty", "Cute", "Sweet" belum di-handle di frontend dengan benar

**Blueprint §20.1–20.3** mensyaratkan:
- **Loyalty**: simpan sebagai `DRAFT` atau tampilkan tombol "Minta Penawaran" saja (harga belum konfirmasi)
- **Cute**: label output `"Video ±1:30 — jenis output menunggu konfirmasi"`, jangan checkout otomatis
- **Sweet**: label output `"Video ±45 detik — jenis output menunggu konfirmasi"`, jangan checkout otomatis

**Backend `SubmitServiceOrderAction.php` sudah benar** (L44-54): mendeteksi nama paket dan force admin review. Tapi frontend katalog belum menampilkan visual yang berbeda untuk paket-paket ini (tidak ada label "Perlu Konfirmasi" yang jelas, tombol "Pilih Paket" tampil sama).

---

### B-02 — Tidak ada `catalog_version_id` di service_orders

**Blueprint §16.2 dan §12.2** mensyaratkan snapshot harus termasuk `catalog_version_id`. Migration saat ini tidak punya kolom ini.

```php
// TIDAK ADA di migration
// $table->foreignId('catalog_version_id')->nullable()->...
```

Blueprint §16.1 juga mensyaratkan entitas `catalog_versions`, yang tidak ada di sistem saat ini.

---

### B-03 — Kolom service_orders tidak lengkap dibanding blueprint

**Blueprint §16.4** mendefinisikan kolom `catalog_package_id` (alias berbeda dari `package_id`), tapi yang lebih penting, beberapa kolom yang disebut blueprint tidak ada:

| Kolom Blueprint | Ada di Migration? |
|---|---|
| `catalog_version_id` | ❌ Tidak ada |
| `location_summary` | ❌ Tidak ada (ada `location_name` + `location_address`) |
| Nama di blueprint: `catalog_package_id` | ✅ Ada sebagai `package_id` (naming berbeda tapi acceptable) |

---

### B-04 — Tidak ada validasi kelengkapan brief sebelum submit

**Blueprint §7 Tahap 4** mensyaratkan sistem memeriksa kelengkapan brief sebelum routing ke Jalur A/B. Saat ini `SubmitServiceOrderAction` tidak memvalidasi apakah `event_date`, `venue_name`, dll sudah diisi. Client bisa submit brief kosong.

---

### B-05 — Menu "Draft Brief" tidak ada di client portal

**Blueprint §3** mendefinisikan menu:
```
| Draft Brief | Melanjutkan brief yang belum dikirim |
```

**Client layout.tsx** saat ini tidak punya menu "Draft Brief" terpisah. Fitur ini bisa ditampilkan sebagai filter di "Pesanan Saya", tapi tidak sesuai spesifikasi menu blueprint yang mendefinisikannya sebagai menu tersendiri.

---

### B-06 — Fitur "Pesan Lagi" belum tampil di UI client

**Blueprint §11** mensyaratkan tombol "Pesan Lagi" muncul pada project yang sudah selesai. Backend `ReorderProjectAction` sudah ada. Tapi belum ada tombol "Pesan Lagi" di halaman project client (`/client/projects`).

---

### B-07 — Tidak ada ketersediaan tanggal (availability check) sebelum submit

**Blueprint §13** mensyaratkan sistem memeriksa:
- jumlah photographer/videographer/assistant tersedia
- konflik dengan project lain
- sebelum invoice diterbitkan

**`SubmitServiceOrderAction`** langsung generate invoice untuk Jalur A tanpa cek availability. Untuk Jalur B admin memang perlu confirm, tapi untuk Jalur A ini seharusnya ada automated check.

**Ini adalah gap signifikan** karena dua pesanan dengan tanggal yang sama bisa keduanya masuk Jalur A dan keduanya dapat slot.

---

### B-08 — Teks konfirmasi brief menampilkan "1x24 jam" sebagai SLA hardcoded

**File:** [`brief/page.tsx` L298](file:///C:/Users/NanoKyuuun/Documents/BDJG/apps/web/src/app/client/orders/[id]/brief/page.tsx#L298)

```
"...tim BDJG akan mereview brief Anda dan menerbitkan surat penawaran (Quotation) dalam 1x24 jam."
```

SLA response time ini hardcoded. Sebaiknya ambil dari konfigurasi atau tidak disebutkan secara spesifik.

---

### B-09 — Admin route filter "NEEDS_INFORMATION" belum ada di frontend admin orders

**Blueprint §18.2** mendefinisikan filter di inbox pesanan admin termasuk "membutuhkan informasi". Frontend admin (`/admin/sales/orders`) mungkin belum ada tab/filter khusus untuk status `NEEDS_INFORMATION`. Status enum sudah ada di backend, tapi perlu diverifikasi apakah UI sudah menampilkan filter ini.

---

## ✅ YANG SUDAH SESUAI BLUEPRINT

| Aspek | Status |
|---|---|
| Semua status `ServiceOrderStatus` enum sesuai blueprint §9 | ✅ Lengkap |
| `ServiceOrderReviewType` (AUTO_CHECKOUT / ADMIN_REVIEW) | ✅ Sesuai |
| Decision engine Jalur A/B/C di `SubmitServiceOrderAction` | ✅ Implementasi bagus |
| Package snapshot disimpan di `service_orders.package_snapshot` | ✅ Sesuai |
| `slot_hold_until` ada di migration | ✅ Ada |
| `service_order_messages` — percakapan per order | ✅ Ada |
| `service_order_attachments` — upload referensi | ✅ Ada |
| `service_order_status_logs` — audit status | ✅ Ada |
| Authorization: Client hanya akses order miliknya (Policy check) | ✅ Ada |
| Paket Loyalty/Cute/Sweet → force admin review di backend | ✅ Benar |
| Duitku callback verified via HMAC signature | ✅ Ada |
| Idempotency: `project_id` unique constraint di service_orders | ✅ Ada |
| Semua menu client portal ada | ✅ Sesuai (kecuali "Draft Brief") |
| ReorderProjectAction sudah ada | ✅ Ada (UI belum) |
| Admin: request-information, confirm-availability, cancel endpoints | ✅ Ada |

---

## Rekomendasi Prioritas

### Segera (sebelum go-live)

1. **[K-01, K-02]** Fix backend API middleware: `/v1/client/*` → `role:CLIENT` saja, `/v1/worker/*` → `role:WORKER` saja
2. **[H-01]** Hapus "DP 30%" hardcoded dari brief/page.tsx — tampilkan dari data backend
3. **[H-02]** Hapus "Surabaya" hardcoded — buat field `base_area_name` dari config/API
4. **[B-04]** Tambahkan validasi kelengkapan brief di `SubmitServiceOrderAction`

### Sprint berikutnya

5. **[H-03, H-04, H-05]** Pindahkan semua nilai business (DP%, due date, slot hold duration) ke tabel `settings` atau `config/bdjg.php`
6. **[B-07]** Implementasikan availability check sebelum Jalur A checkout (minimal check apakah tanggal sudah ada project lain)
7. **[B-01]** Tambahkan label visual "Perlu Konfirmasi" / "Hubungi untuk Penawaran" di kartu katalog untuk paket Loyalty/Cute/Sweet

### Backlog

8. **[B-02, B-03]** Tambahkan `catalog_versions` tabel dan `catalog_version_id` di service_orders
9. **[B-05]** Tambahkan menu "Draft Brief" di client portal sidebar
10. **[B-06]** Tambahkan tombol "Pesan Lagi" di halaman project selesai
11. **[B-08]** Hapus SLA "1x24 jam" hardcoded dari UI
12. **[B-09]** Verifikasi dan lengkapi filter "Butuh Informasi" di admin order inbox
13. **[K-03]** Implementasikan status `PAYMENT_PENDING` saat client redirect ke Duitku
