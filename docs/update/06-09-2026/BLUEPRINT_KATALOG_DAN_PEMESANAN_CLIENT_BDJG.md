# Blueprint Katalog dan Pemesanan Mandiri Client BDJG

**Versi:** 1.0  
**Tanggal:** 6 September 2026  
**Ruang lingkup:** Client Portal, katalog Wedding BDJG 2026, brief, quotation, pembayaran, dan aktivasi project

---

## 1. Tujuan

Fitur ini memungkinkan client yang telah memiliki akun BDJG untuk memesan layanan berikutnya langsung dari dashboard tanpa harus berpindah ke WhatsApp.

Portal BDJG menjadi sumber informasi utama untuk:

- melihat katalog dan detail paket;
- memeriksa ketersediaan tanggal;
- mengisi serta menyimpan brief;
- mengunggah referensi;
- menerima permintaan informasi tambahan;
- menyetujui quotation;
- membayar invoice;
- memantau pembentukan dan pengerjaan project;
- melihat riwayat komunikasi dan dokumen pesanan.

WhatsApp tetap tersedia sebagai jalur bantuan opsional untuk konsultasi kompleks, kebutuhan mendesak, atau apabila client memang memilih dihubungi melalui WhatsApp.

---

## 2. Prinsip Utama

1. **Client lama dapat self-service.** Client yang sudah login tidak perlu mengisi ulang identitas dan data perusahaan dari awal.
2. **Portal menjadi source of truth.** Harga, brief, quotation, invoice, pembayaran, persetujuan, dan status pesanan harus tercatat di platform.
3. **Email hanya sebagai notifikasi.** Setiap email mengarahkan client kembali ke halaman terkait di Client Portal.
4. **WhatsApp bukan jalur wajib.** WhatsApp digunakan hanya untuk bantuan atau pembahasan khusus.
5. **Pesanan tidak langsung menjadi project.** Sistem membuat `Service Order` terlebih dahulu. Project baru dibuat setelah syarat bisnis terpenuhi.
6. **Harga historis harus tetap konsisten.** Perubahan harga katalog tidak boleh mengubah pesanan atau quotation yang telah diterbitkan.
7. **Tanggal belum dianggap aman sebelum tersedia.** Sistem harus memeriksa kapasitas tim dan jadwal sebelum menerima pembayaran.

---

## 3. Struktur Menu Client Portal

Menu yang disarankan untuk akun client:

| Menu | Fungsi |
|---|---|
| Dashboard | Ringkasan pesanan, quotation, invoice, dan project aktif |
| Katalog Layanan | Melihat seluruh paket BDJG yang dapat dipesan |
| Pesanan Saya | Melihat draft dan seluruh pengajuan layanan |
| Draft Brief | Melanjutkan brief yang belum dikirim |
| Quotation | Membaca, menerima, atau meminta revisi quotation |
| Invoice & Pembayaran | Melihat invoice dan melakukan pembayaran |
| Project Saya | Memantau project yang sudah aktif |
| Pesan | Percakapan yang terhubung ke pesanan atau project |
| File & Delivery | Mengakses preview dan hasil akhir yang sudah dirilis |

Dashboard dapat menampilkan tindakan cepat berikut:

- **Lihat Katalog**
- **Pesan Lagi**
- **Lanjutkan Draft Brief**
- **Bayar Invoice**
- **Lihat Project Aktif**

---

## 4. Struktur Katalog Wedding BDJG 2026

Katalog menggunakan data dari dokumen **Penjelasan Price List Wedding BDJG 2026**.

### 4.1 Happiness Package — Foto dan Video

| Paket | Harga | Coverage | Ringkasan | Jalur awal |
|---|---:|---:|---|---|
| Couple Session | Rp3.500.000 | 3 jam | 1 photographer, 1 videographer, 1 assistant, 2 konsep, foto cetak, teaser ±1:30, 40 foto edited, file original, flashdisk | Checkout standar apabila tanggal dan lokasi memenuhi |
| Half Day Wedding | Rp4.000.000 | 3–5 jam | 1 photographer, 1 videographer, foto cetak, teaser ±1:30, file edited dan original, flashdisk | Checkout standar apabila tanggal dan lokasi memenuhi |
| Full Day Wedding | Rp5.500.000 | 8–10 jam | 1 photographer, 1 videographer, 1 assistant, album, 120 foto cetak, cinema full ±4 menit, highlight ±45 detik, file edited dan original, flashdisk | Checkout standar dan dapat diberi label **Best Value** |

### 4.2 Photography Package

| Paket | Harga | Coverage | Ringkasan | Jalur awal |
|---|---:|---:|---|---|
| Couple Session Photography | Rp2.000.000 | 3 jam | 1 photographer, 1 assistant, 2 konsep, foto cetak, 20 foto edited, file original, flashdisk | Checkout standar apabila tanggal dan lokasi memenuhi |
| Romance | Rp3.000.000 | 8–10 jam | 1 photographer, 1 assistant, album, 100 foto cetak, file edited dan original, flashdisk | Checkout standar apabila tanggal dan lokasi memenuhi |
| Soul | Rp5.500.000 | 8–10 jam | 2 photographer, album, 120 foto cetak, file edited dan original, flashdisk | Checkout standar apabila tanggal dan lokasi memenuhi |
| Loyalty | **Perlu konfirmasi** | 8–10 jam | 2 photographer, 1 candid photographer, album exclusive, album magazine/collage, foto cetak, file edited dan original, flashdisk | Wajib review admin sampai harga dikonfirmasi |

### 4.3 Videography Package

| Paket | Harga | Coverage / output | Ringkasan | Jalur awal |
|---|---:|---|---|---|
| Couple Session Videography | Rp2.000.000 | 3 jam | 1 videographer, 1 assistant, 2 konsep, cinema full ±4 menit, highlight ±45 detik, flashdisk | Checkout standar apabila tanggal dan lokasi memenuhi |
| Perfect | Rp4.000.000 | 8–10 jam | 2 videographer, 1 assistant, cinema full, teaser, highlight, flashdisk | Checkout standar apabila tanggal dan lokasi memenuhi |
| Lover | Rp2.500.000 | 8–10 jam | 2 videographer, cinema full ±4 menit, flashdisk | Checkout standar apabila tanggal dan lokasi memenuhi |
| Cute | Rp1.500.000 | 8–10 jam | 1 videographer, video ±1:30, flashdisk | Wajib review sampai istilah output dikonfirmasi |
| Sweet | Rp1.000.000 | 8–10 jam | 1 videographer, video ±45 detik | Wajib review sampai istilah output dikonfirmasi |
| Mamoar | Rp2.000.000 | 8–10 jam | 1 videographer, dokumentasi ±15 menit, flashdisk | Checkout standar apabila tanggal dan lokasi memenuhi |

### 4.4 Custom Your Story

Paket ini tidak menggunakan checkout harga tetap. Client mengirim kebutuhan dan budget, kemudian admin menyusun quotation.

Brief custom dapat mencakup:

- budget;
- tanggal dan durasi acara;
- jumlah atau lokasi venue;
- jumlah photographer;
- jumlah videographer;
- kebutuhan assistant;
- output foto;
- output video;
- album dan cetak foto;
- referensi gaya;
- kebutuhan khusus lainnya.

Tombol utama: **Ajukan Custom Brief**.

---

## 5. Informasi pada Kartu dan Detail Paket

### 5.1 Kartu katalog

Setiap kartu paket minimal menampilkan:

- nama paket;
- kategori;
- harga atau label **Hubungi untuk Penawaran**;
- coverage;
- jumlah kru utama;
- tiga sampai lima benefit terpenting;
- label seperti **Best Value**, apabila digunakan;
- tombol **Lihat Detail**;
- tombol **Pesan Sekarang** atau **Ajukan Brief**.

### 5.2 Halaman detail paket

Halaman detail menampilkan:

- deskripsi paket;
- seluruh deliverable;
- jumlah dan tipe kru;
- durasi coverage;
- jumlah revisi apabila tersedia;
- estimasi waktu pengerjaan apabila tersedia;
- biaya yang sudah termasuk;
- biaya yang belum termasuk;
- ketentuan transportasi dan akomodasi;
- contoh portfolio yang relevan;
- add-on yang dapat dipilih;
- kebijakan perubahan tanggal dan pembatalan;
- tombol tindakan utama.

### 5.3 Informasi yang belum tersedia

Data berikut belum terdapat secara pasti dalam price list dan harus dapat diatur admin sebelum checkout publik diaktifkan:

- persentase atau nominal DP;
- tenggat pelunasan;
- jumlah revisi setiap output;
- estimasi waktu delivery;
- harga add-on;
- base area BDJG;
- rumus transportasi dan akomodasi;
- kapasitas tim per tanggal;
- kebijakan reschedule dan pembatalan.

---

## 6. Alur Utama Client yang Sudah Memiliki Akun

### Tahap 1 — Masuk ke dashboard

1. Client login.
2. Sistem menampilkan project aktif, tagihan, dan rekomendasi pemesanan ulang.
3. Client memilih **Katalog Layanan** atau **Pesan Lagi**.

### Tahap 2 — Memilih paket

1. Client memilih kategori Foto + Video, Photography, Videography, atau Custom.
2. Client membuka detail paket.
3. Client memilih **Pesan Sekarang**.
4. Sistem membuat `Service Order` berstatus `DRAFT`.

### Tahap 3 — Mengisi informasi acara

Client mengisi:

- nama acara atau pasangan;
- jenis acara;
- tanggal;
- jam mulai dan selesai;
- alamat dan lokasi venue;
- jumlah venue;
- estimasi tamu;
- pilihan paket;
- add-on;
- catatan kebutuhan;
- preferensi komunikasi;
- referensi atau moodboard.

Data profil, nomor telepon, email, dan informasi billing diisi otomatis dari akun, tetapi tetap dapat dikonfirmasi client.

### Tahap 4 — Pemeriksaan sistem

Sistem memeriksa:

- kelengkapan brief;
- status aktif paket;
- versi dan harga paket;
- ketersediaan jadwal;
- kapasitas kru;
- durasi coverage;
- jumlah venue;
- lokasi di dalam atau di luar base area;
- permintaan di luar deliverable standar.

Hasil pemeriksaan menentukan salah satu dari tiga jalur berikut.

---

## 7. Tiga Jalur Pemesanan

### 7.1 Jalur A — Checkout standar

Jalur ini digunakan apabila:

- harga paket sudah pasti;
- tanggal tersedia;
- durasi sesuai paket;
- venue dan lokasi memenuhi ketentuan standar;
- tidak ada output khusus;
- tidak memerlukan tambahan kru di luar katalog.

Alur:

1. Client memeriksa ringkasan paket dan brief.
2. Sistem menghitung harga paket dan add-on.
3. Client menyetujui ketentuan.
4. Sistem membuat invoice DP.
5. Client membayar melalui payment gateway.
6. Backend memverifikasi merchant/order, nominal, signature, dan status transaksi ke provider.
7. Setelah pembayaran valid, pesanan dikunci.
8. Project dibuat tepat satu kali.
9. Client menerima notifikasi dan diarahkan ke halaman project.

### 7.2 Jalur B — Paket dengan review admin

Jalur ini digunakan apabila:

- lokasi berada di luar base area;
- terdapat beberapa venue;
- durasi melebihi paket;
- client meminta tambahan kru;
- ada output atau spesifikasi tambahan;
- tanggal memerlukan konfirmasi manual;
- sistem mendeteksi perubahan harga;
- paket belum memiliki data final.

Alur:

1. Client mengirim brief.
2. Pesanan berstatus `UNDER_REVIEW`.
3. Admin menerima notifikasi di dashboard.
4. Admin meminta informasi tambahan melalui percakapan pesanan jika diperlukan.
5. Admin menerbitkan quotation.
6. Client menerima, meminta revisi, atau menolak quotation.
7. Setelah quotation diterima, sistem membuat invoice DP.
8. Setelah pembayaran terverifikasi, sistem membuat project.

### 7.3 Jalur C — Custom Your Story

Alur:

1. Client memilih **Ajukan Custom Brief**.
2. Client mengisi budget dan spesifikasi kebutuhannya.
3. Client dapat mengunggah contoh foto, video, rundown, dan dokumen venue.
4. Admin memeriksa brief dan kapasitas tim.
5. Diskusi dilakukan di percakapan pesanan.
6. Jika dibutuhkan, admin menawarkan jadwal konsultasi atau WhatsApp.
7. Admin menerbitkan quotation custom.
8. Client menyetujui quotation dan membayar DP.
9. Sistem membuat project setelah pembayaran valid.

---

## 8. Kapan Harus Masuk Review Admin?

| Kondisi | Checkout langsung | Review admin |
|---|:---:|:---:|
| Paket aktif, harga pasti, tanggal tersedia | Ya | Tidak |
| Brief standar dan coverage sesuai paket | Ya | Tidak |
| Lokasi di luar base area | Tidak | Ya |
| Transportasi atau akomodasi diperlukan | Tidak | Ya |
| Lebih dari satu venue | Tidak | Ya |
| Tambahan jam coverage | Tidak | Ya |
| Tambahan photographer/videographer | Tidak | Ya |
| Deliverable di luar paket | Tidak | Ya |
| Custom Your Story | Tidak | Ya |
| Harga paket belum dikonfirmasi | Tidak | Ya |
| Jadwal atau kapasitas kru belum pasti | Tidak | Ya |

---

## 9. Status Service Order

Status utama yang direkomendasikan:

| Status | Arti |
|---|---|
| `DRAFT` | Brief belum dikirim oleh client |
| `SUBMITTED` | Brief sudah dikirim dan menunggu pemeriksaan |
| `NEEDS_INFORMATION` | Admin meminta informasi tambahan |
| `UNDER_REVIEW` | Pesanan sedang diperiksa |
| `QUOTATION_READY` | Quotation tersedia untuk client |
| `REVISION_REQUESTED` | Client meminta revisi quotation |
| `AWAITING_ACCEPTANCE` | Menunggu persetujuan quotation |
| `AWAITING_PAYMENT` | Invoice telah tersedia |
| `PAYMENT_PENDING` | Pembayaran sedang diproses provider |
| `PAID` | Pembayaran telah terverifikasi |
| `PROJECT_CREATED` | Project berhasil dibuat |
| `DECLINED` | Quotation ditolak client |
| `EXPIRED` | Penawaran atau batas pembayaran berakhir |
| `CANCELLED` | Pesanan dibatalkan secara sah |

Status pembayaran tidak boleh hanya berdasarkan redirect browser. Callback harus diverifikasi backend dan, jika tersedia, dikonfirmasi melalui transaction-status API milik payment provider.

---

## 10. Brief Terstruktur

### 10.1 Field umum

- judul pesanan;
- jenis acara;
- tanggal dan waktu;
- venue;
- alamat lengkap;
- jumlah venue;
- estimasi tamu;
- nama contact person di lokasi;
- nomor contact person;
- rundown;
- catatan khusus;
- unggahan referensi.

### 10.2 Field couple session

- jumlah konsep;
- lokasi pemotretan;
- indoor atau outdoor;
- referensi gaya;
- kebutuhan makeup/styling apabila relevan;
- pilihan tanggal cadangan;
- kebutuhan izin lokasi.

### 10.3 Field wedding

- jenis prosesi;
- waktu persiapan;
- waktu akad atau pemberkatan;
- waktu resepsi;
- jumlah venue;
- perpindahan lokasi;
- momen wajib direkam;
- daftar keluarga inti;
- vendor atau wedding organizer;
- izin penggunaan hasil untuk portfolio.

### 10.4 Perilaku form

- brief dapat disimpan otomatis sebagai draft;
- field ditampilkan berdasarkan kategori paket;
- file dapat diunggah sebelum submit;
- client dapat menyalin brief project lama;
- admin dapat meminta perbaikan tanpa menghapus jawaban sebelumnya;
- perubahan setelah quotation diterima harus tercatat sebagai revisi baru.

---

## 11. Fitur Pesan Lagi

Pada project yang telah selesai, tampilkan tombol **Pesan Lagi**.

Saat digunakan, sistem:

1. membuat `Service Order` baru;
2. menyalin paket dan brief lama sebagai draft;
3. menggunakan harga katalog terbaru, bukan harga project lama;
4. memberi tahu client apabila paket telah berubah atau tidak aktif;
5. meminta client memilih tanggal dan lokasi baru;
6. mengharuskan client memeriksa ulang seluruh brief sebelum submit.

Project lama tetap tersimpan dan tidak diubah.

---

## 12. Quotation dan Harga

### 12.1 Aturan quotation

- setiap quotation memiliki nomor dan versi;
- hanya satu versi yang dapat berstatus diterima;
- revisi tidak mengubah versi sebelumnya;
- quotation memiliki masa berlaku;
- harga berisi rincian paket, add-on, transportasi, akomodasi, diskon, pajak jika berlaku, dan total;
- client harus memberikan persetujuan eksplisit;
- waktu dan akun yang memberikan persetujuan harus dicatat.

### 12.2 Snapshot harga

Saat pesanan dikirim atau quotation diterbitkan, simpan snapshot:

- nama paket;
- harga;
- coverage;
- deliverable;
- kru;
- add-on;
- syarat dan ketentuan;
- versi katalog.

Dengan demikian, perubahan katalog tidak mengubah kontrak pesanan lama.

### 12.3 DP dan pelunasan

Nominal DP belum disebutkan pada price list. Nilainya harus dibuat sebagai pengaturan bisnis, bukan ditulis permanen di dalam kode.

Project hanya aktif apabila:

- quotation wajib sudah diterima; dan
- pembayaran yang diwajibkan untuk aktivasi telah terverifikasi.

Final delivery dapat dikunci sampai kewajiban pembayaran akhir terpenuhi, sesuai kebijakan BDJG.

---

## 13. Jadwal dan Reservasi Tanggal

Sebelum invoice diterbitkan, sistem harus memeriksa:

- jumlah photographer tersedia;
- jumlah videographer tersedia;
- assistant tersedia;
- konflik dengan project lain;
- waktu perpindahan lokasi;
- estimasi perjalanan;
- kebutuhan peralatan khusus.

Jika tanggal dinyatakan tersedia, sistem dapat memberikan `slot_hold_until` yang durasinya dapat diatur admin. Apabila invoice tidak dibayar sampai batas tersebut, slot dilepas dan ketersediaan diperiksa ulang.

Label yang ditampilkan kepada client:

- **Tersedia**
- **Perlu Konfirmasi**
- **Tidak Tersedia**
- **Ditahan Sementara**

---

## 14. Percakapan di Dalam Platform

Setiap `Service Order` mempunyai satu ruang percakapan tersendiri.

Fitur minimal:

- pesan client dan admin;
- lampiran;
- timestamp;
- status sudah dibaca;
- notifikasi pesan baru;
- referensi terhadap brief atau quotation;
- audit log untuk pesan sistem;
- pembatasan akses berdasarkan pemilik pesanan.

WhatsApp dapat dipakai untuk komunikasi tambahan, tetapi hasil kesepakatan yang memengaruhi harga, ruang lingkup, jadwal, atau deliverable harus dicatat kembali di portal.

---

## 15. Notifikasi

### 15.1 Notifikasi dalam portal

- brief berhasil dikirim;
- admin meminta informasi tambahan;
- quotation diterbitkan atau direvisi;
- quotation mendekati kedaluwarsa;
- invoice tersedia;
- pembayaran berhasil atau gagal;
- project berhasil dibuat;
- jadwal atau status project berubah.

### 15.2 Email

Email dikirim sebagai pemberitahuan dan berisi deep link ke halaman terkait.

| Peristiwa | Penerima | Tautan tujuan |
|---|---|---|
| Brief dikirim | Client dan admin | Detail pesanan |
| Informasi tambahan diminta | Client | Percakapan/brief pesanan |
| Quotation tersedia | Client | Detail quotation |
| Quotation diterima | Client dan admin | Detail pesanan |
| Invoice diterbitkan | Client | Pembayaran invoice |
| Pembayaran terverifikasi | Client dan admin | Detail pembayaran/project |
| Project dibuat | Client | Halaman project |

Email tidak menjadi tempat persetujuan quotation dan tidak menyimpan keputusan utama.

---

## 16. Rekomendasi Struktur Data

### 16.1 Entitas katalog

- `service_categories`
- `catalog_packages`
- `package_deliverables`
- `package_crew_requirements`
- `package_add_ons`
- `catalog_versions`

### 16.2 Entitas pemesanan

- `service_orders`
- `service_order_items`
- `service_order_briefs`
- `service_order_attachments`
- `service_order_messages`
- `service_order_status_logs`

### 16.3 Entitas komersial

- `quotations`
- `quotation_versions`
- `quotation_items`
- `invoices`
- `payments`

### 16.4 Relasi inti

```text
Client
  └── Service Order
        ├── Package Snapshot
        ├── Brief
        ├── Attachments
        ├── Messages
        ├── Quotation Versions
        └── Invoice
              └── Verified Payment
                    └── Project
```

Kolom penting pada `service_orders`:

- `id`
- `order_number`
- `client_id`
- `catalog_package_id`
- `catalog_version_id`
- `source` (`CATALOG`, `REORDER`, atau `CUSTOM`)
- `review_type` (`AUTO_CHECKOUT` atau `ADMIN_REVIEW`)
- `status`
- `event_date`
- `start_time`
- `end_time`
- `venue_count`
- `location_summary`
- `package_snapshot`
- `submitted_at`
- `slot_hold_until`
- `project_id`
- `created_by`

Gunakan constraint atau idempotency key agar satu pesanan tidak menghasilkan lebih dari satu project.

---

## 17. Rekomendasi Endpoint

### Client

```text
GET    /v1/client/catalog/categories
GET    /v1/client/catalog/packages
GET    /v1/client/catalog/packages/{package}
POST   /v1/client/service-orders
GET    /v1/client/service-orders
GET    /v1/client/service-orders/{order}
PATCH  /v1/client/service-orders/{order}/brief
POST   /v1/client/service-orders/{order}/attachments
POST   /v1/client/service-orders/{order}/submit
POST   /v1/client/service-orders/{order}/messages
POST   /v1/client/service-orders/{order}/quotation/accept
POST   /v1/client/service-orders/{order}/quotation/revision-request
POST   /v1/client/service-orders/{order}/quotation/decline
POST   /v1/client/service-orders/{order}/checkout
POST   /v1/client/projects/{project}/reorder
```

### Admin

```text
GET    /v1/admin/catalog/packages
POST   /v1/admin/catalog/packages
PATCH  /v1/admin/catalog/packages/{package}
POST   /v1/admin/catalog/packages/{package}/publish
GET    /v1/admin/service-orders
GET    /v1/admin/service-orders/{order}
POST   /v1/admin/service-orders/{order}/request-information
POST   /v1/admin/service-orders/{order}/messages
POST   /v1/admin/service-orders/{order}/quotation
POST   /v1/admin/service-orders/{order}/confirm-availability
POST   /v1/admin/service-orders/{order}/cancel
```

Seluruh endpoint harus memakai authorization policy berbasis pemilik pesanan dan role. Client hanya boleh mengakses pesanan miliknya sendiri.

---

## 18. Halaman Admin yang Diperlukan

### 18.1 Pengelolaan katalog

Admin dapat:

- menambah dan mengubah paket;
- mengatur kategori;
- mengatur harga;
- mengatur deliverable;
- mengatur kebutuhan kru;
- menambah add-on;
- menonaktifkan paket tanpa menghapus riwayat;
- membuat versi katalog;
- mengatur label rekomendasi;
- menentukan apakah paket mendukung checkout otomatis.

### 18.2 Inbox pesanan

Admin dapat menyaring berdasarkan:

- pesanan baru;
- membutuhkan informasi;
- menunggu review;
- menunggu quotation;
- menunggu client;
- menunggu pembayaran;
- siap dibuat menjadi project;
- kedaluwarsa atau dibatalkan.

### 18.3 Kalender ketersediaan

Menampilkan:

- project aktif;
- slot yang sedang ditahan;
- pesanan yang menunggu review;
- kebutuhan photographer, videographer, dan assistant;
- konflik jadwal.

---

## 19. Aturan Keamanan dan Integritas

1. Client hanya boleh melihat dan mengubah pesanan miliknya sendiri.
2. Client tidak dapat mengubah brief setelah quotation diterima tanpa membuat proses revisi.
3. Harga akhir harus dihitung backend, bukan dipercaya dari browser.
4. Paket dan harga menggunakan snapshot pada pesanan.
5. File upload diverifikasi berdasarkan pemilik intent, ukuran, MIME, checksum, dan keberadaan objek.
6. Callback pembayaran harus memverifikasi signature, merchant, order ID, nominal, dan status provider.
7. Aktivasi project harus idempotent dan terjadi tepat satu kali.
8. Status pembayaran tidak boleh mundur dari berhasil menjadi pending atau gagal.
9. Perubahan harga, quotation, status, dan persetujuan masuk ke audit log.
10. Akun nonaktif tidak boleh tetap memakai sesi lama untuk mengakses portal.

---

## 20. Data Price List yang Harus Dikonfirmasi

Sebelum seluruh paket dapat dipublikasikan sebagai checkout otomatis, lakukan konfirmasi berikut:

### 20.1 Loyalty

Harga terbaca berbeda antara teks dan tampilan visual:

- Rp5.000.000; atau
- Rp15.000.000.

**Tindakan:** simpan paket sebagai `DRAFT` atau aktifkan hanya tombol **Minta Penawaran** sampai harga resmi dipastikan.

### 20.2 Cute

Judul mengarah ke **Cinema Teaser**, sedangkan detail menyebut **Cinema Full ±1 menit 30 detik**.

**Rekomendasi sementara:** gunakan label `Video ±1 menit 30 detik — jenis output menunggu konfirmasi` dan jangan aktifkan checkout otomatis.

### 20.3 Sweet

Judul mengarah ke **Cinema Highlight**, sedangkan detail menyebut **Cinema Full ±45 detik**.

**Rekomendasi sementara:** gunakan label `Video ±45 detik — jenis output menunggu konfirmasi` dan jangan aktifkan checkout otomatis.

### 20.4 Transportasi dan akomodasi

Semua harga belum termasuk transportasi dan akomodasi untuk lokasi di luar base BDJG.

**Tindakan:** apabila sistem belum memiliki perhitungan wilayah dan biaya otomatis, arahkan pesanan tersebut ke review admin.

---

## 21. Tahapan Implementasi

### Fase 1 — Fondasi katalog dan pesanan

- katalog dari price list;
- halaman kategori dan detail paket;
- draft `Service Order`;
- structured brief;
- upload referensi;
- daftar dan detail pesanan client;
- inbox pesanan admin;
- authorization policy.

### Fase 2 — Review dan quotation

- pemeriksaan ketersediaan;
- percakapan pesanan;
- permintaan informasi tambahan;
- quotation berversi;
- accept, revision request, dan decline;
- notifikasi portal dan email.

### Fase 3 — Checkout dan aktivasi

- invoice DP;
- integrasi payment gateway;
- callback dan status verification;
- slot hold;
- aktivasi project yang idempotent;
- audit log.

### Fase 4 — Optimasi repeat order

- tombol Pesan Lagi;
- duplikasi brief;
- rekomendasi paket;
- add-on;
- kalender ketersediaan yang lebih otomatis;
- analitik conversion katalog ke project.

---

## 22. Acceptance Criteria

Fitur dianggap siap apabila:

- [ ] Client aktif dapat membuka katalog dari dashboard.
- [ ] Katalog menampilkan paket dan harga sesuai versi yang dipublikasikan.
- [ ] Paket yang datanya belum pasti tidak dapat di-checkout otomatis.
- [ ] Client dapat menyimpan dan melanjutkan draft brief.
- [ ] Client dapat mengunggah referensi dengan aman.
- [ ] Sistem dapat membedakan checkout standar dan review admin.
- [ ] Pesanan lokasi luar base masuk review admin.
- [ ] Admin dapat meminta informasi tambahan melalui portal.
- [ ] Quotation mempunyai versi, masa berlaku, dan histori persetujuan.
- [ ] Invoice dibuat dari data harga backend.
- [ ] Pembayaran diverifikasi secara aman.
- [ ] Satu pesanan hanya dapat menghasilkan satu project.
- [ ] Project tidak aktif sebelum quotation dan pembayaran yang diwajibkan valid.
- [ ] Client hanya dapat mengakses pesanan, file, quotation, invoice, dan project miliknya.
- [ ] Email mengarah kembali ke halaman terkait di portal.
- [ ] WhatsApp bersifat opsional dan bukan satu-satunya jalur komunikasi.
- [ ] Perubahan katalog tidak mengubah harga pesanan lama.
- [ ] Tombol Pesan Lagi membuat pesanan baru tanpa mengubah project lama.

---

## 23. Alur Akhir yang Direkomendasikan

### Pesanan standar

```text
Login
→ Katalog
→ Pilih Paket
→ Isi Brief
→ Cek Jadwal dan Ketentuan
→ Ringkasan Harga
→ Invoice DP
→ Pembayaran Terverifikasi
→ Project Dibuat
```

### Pesanan yang memerlukan penyesuaian

```text
Login
→ Katalog
→ Pilih Paket
→ Isi Brief Spesifik
→ Review Admin
→ Diskusi di Portal
→ Quotation
→ Persetujuan Client
→ Invoice DP
→ Pembayaran Terverifikasi
→ Project Dibuat
```

### Custom Your Story

```text
Login
→ Ajukan Custom Brief
→ Upload Referensi dan Rundown
→ Review Admin
→ Konsultasi Opsional
→ Quotation Custom
→ Persetujuan Client
→ Invoice DP
→ Pembayaran Terverifikasi
→ Project Dibuat
```

---

## Kesimpulan

Price list Wedding BDJG 2026 dapat digunakan sebagai dasar katalog Client Portal. Paket dengan harga, coverage, dan deliverable yang sudah pasti dapat diarahkan ke checkout standar setelah jadwal serta lokasi tervalidasi. Kebutuhan khusus, biaya perjalanan, tambahan kru, perubahan deliverable, dan paket yang informasinya belum final harus masuk ke review admin.

Struktur bisnis yang direkomendasikan adalah:

```text
Catalog → Service Order → Brief → Quotation/Checkout → Payment → Project
```

Dengan struktur tersebut, client lama dapat memesan kembali sepenuhnya melalui akun BDJG, sementara admin tetap memiliki kontrol terhadap kapasitas, harga khusus, jadwal, dan brief kompleks.
