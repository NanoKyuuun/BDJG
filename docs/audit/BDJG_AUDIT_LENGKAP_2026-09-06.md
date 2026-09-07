# Audit Lengkap BDJG — Blueprint, Dokumentasi, dan Implementasi

**Tanggal audit:** 6 September 2026  
**Objek audit:** isi arsip `BDJG(1).zip` pada working tree yang disertakan  
**Metode:** membaca dokumen menurut hierarki sumber kebenaran, menelusuri implementasi backend/frontend, memeriksa kontrak dan migration secara statis, serta menjalankan verifikasi frontend yang tersedia  
**Keputusan:** **BLOCKED — belum layak disebut Release A selesai dan belum layak dipromosikan ke production**

---

## 1. Ringkasan eksekutif

Fondasi proyek ini sebenarnya menjanjikan. Struktur domain Laravel cukup jelas, enum dan API Resource sudah banyak dipakai, perhitungan quotation dilakukan di server, ULID sudah tersedia pada banyak tabel, ada usaha memakai Policy, Action, audit log, transaction, signed URL, dan sudah terdapat 25 file test backend dengan 141 deklarasi skenario. Frontend juga berhasil melewati TypeScript dan production build.

Namun, implementasi saat ini melebar ke Release B, C, dan D sebelum gate Release A terpenuhi. Dampaknya bukan sekadar checklist belum dirapikan: ada celah otorisasi lintas portal, risiko command injection pada worker FFmpeg, eskalasi Admin menjadi Owner, pemrosesan pembayaran yang belum authoritative/idempotent, pipeline media yang menghasilkan artefak palsu tetapi ditandai `READY`, serta kontrak frontend–backend yang putus.

Kesimpulan praktisnya:

1. **Jangan deploy ke production.**
2. **Bekukan penambahan fitur Release B/C/D.**
3. **Tutup lebih dulu dua temuan P0 dan seluruh temuan P1.**
4. **Kembali ke gate paling awal yang belum selesai, yaitu Phase 1, lalu buktikan setiap fase secara berurutan.**
5. **Jangan memakai checklist saat ini sebagai bukti kelulusan**, karena 1.213 dari 1.338 item masih kosong dan beberapa item yang dicentang justru berlabel `DEFERRED`.

### Ringkasan temuan

| Severity | Jumlah | Makna |
|---|---:|---|
| P0 | 2 | Risiko kebocoran lintas tenant/portal atau eksekusi perintah pada server; harus ditutup sebelum lingkungan bersama dipakai |
| P1 | 15 | Blocker release: eskalasi privilege, pembayaran/data integrity, fitur inti rusak, atau gate wajib gagal |
| P2 | 11 | Kekurangan arsitektur/kontrak/operasional yang serius tetapi tidak langsung setara P0/P1 |
| P3 | 1 | Debt dan hygiene yang menurunkan maintainability/kejelasan |
| **Total** | **29** | Temuan utama; beberapa temuan mengelompokkan beberapa gejala dengan akar masalah yang sama |

---

## 2. Ruang lingkup dan batasan audit

Dokumen dibaca mengikuti hierarki repo:

1. `docs/PRD.md`
2. `docs/blueprint.md`
3. `docs/DESIGN.md`
4. `docs/TECHNICAL.md`
5. `docs/IMPLEMENTATION.md`
6. `docs/PHASE-COMPLETION-CHECKLIST.md`
7. `AGENTS.md`

Area kode yang ditinjau:

- authentication, session lifecycle, RBAC, Policy, dan route boundary;
- catalog, client, inquiry, quotation, invoice, payment, project activation;
- worker, assignment, task, schedule;
- media upload, FFmpeg, revision, final delivery;
- audit, finance, notification;
- portal Admin/Worker/Client dan public/CMS-like UI;
- migration, seeder, API Resource, CI, Docker, manifest, lockfile, dan README.

Yang **belum dapat dijalankan**:

- Pest/backend test;
- `migrate:fresh`, seeder, dan route boot Laravel;
- Duitku Sandbox round trip;
- queue/Horizon dan FFmpeg nyata;
- browser E2E/Playwright.

Alasannya: lingkungan audit tidak menyediakan `php`, `composer`, atau `docker`. Karena itu, temuan yang bergantung pada boot Laravel diberi bahasa “sangat mungkin”/“perlu dibuktikan runtime”; temuan lain bersifat terkonfirmasi dari alur kode.

Working tree di dalam arsip juga sudah sangat kotor: hampir seluruh file tercatat modified dan line ending CRLF membuat `git diff --check` menghasilkan noise yang sangat besar. Audit ini menilai **snapshot working tree yang diberikan**, bukan delta terhadap commit bersih.

---

## 3. Status phase dan release yang sebenarnya

### 3.1 Gate paling awal yang belum selesai

Status paling defensible adalah:

```text
Phase 0: tercatat selesai, tetapi belum bisa diverifikasi ulang di runtime audit
Phase 1: IN PROGRESS / BLOCKED
Phase 2: FAILED REVIEW
Phase 3: IN PROGRESS / BLOCKED
Phase 4–21: implementasi parsial ada, tetapi checklist/evidence belum dimulai
Release A: NOT COMPLETE
Release B/C/D: implementasi dini/parsial; tidak sah dianggap selesai
```

Bukti langsung:

- Phase 1 masih tidak memiliki test CSRF, password reset, dan email verification (`docs/PHASE-COMPLETION-CHECKLIST.md:456-458`), UI forgot/reset/verify (`:462-465`), manual session test dan evidence (`:472-476`).
- Pada baris `472`, “All required auth tests green” dicentang padahal tiga test wajib tepat di atasnya belum dicentang.
- Phase 2 mencentang isolation test yang pada teks yang sama diberi label `DEFERRED` (`:535-538`). Ini bukan evidence yang valid.
- Phase 3 belum menandai dashboard, empty/error/forbidden/normal states, permission-aware navigation, dan no-dummy gate (`:554-598`).
- Semua item Phase 4 dan seterusnya kosong, termasuk final gate Release A.

### 3.2 Statistik checklist

| Bagian | Checked | Unchecked |
|---|---:|---:|
| Seluruh checklist | 125 | 1.213 |
| Phase 1 | 35 | 12 |
| Phase 2 | 38 | 2 |
| Phase 3 | 16 | 12 |
| Phase 4–21 | 0 | seluruh item |
| Release A final gate | 0 | 97 |
| Release B/C/D final gates | 0 | seluruh item |

Jadi keberadaan halaman atau class untuk Release B/C/D tidak bisa dipakai sebagai bukti completion. Dokumen sendiri mensyaratkan urutan fase dan evidence gate.

---

## 4. Temuan P0 — harus ditutup segera

### SEC-01 — Boundary `/v1/admin` tidak benar-benar Admin dan sudah membuka kebocoran lintas resource

**Severity: P0 — confirmed secara statis**

Seluruh route Admin berada di grup yang hanya memakai `auth:sanctum` (`apps/api/routes/api.php:68-80`). Tidak ada middleware role/permission pada prefix `/v1/admin`.

Dua exploit path yang jelas:

1. Role `CLIENT` mendapat permission `media.view` (`apps/api/database/seeders/RoleSeeder.php:58-65`). Endpoint Admin `GET /v1/admin/projects/{project}/media` hanya memanggil `viewAny` lalu mengambil seluruh media berdasarkan ID proyek yang diberikan (`MediaAssetController.php:25-35`). `MediaAssetPolicy::viewAny` hanya mengecek `media.view` (`MediaAssetPolicy.php:11-14`), tanpa ownership proyek atau visibility. Akibatnya Client dapat mencoba ID proyek lain dan menerima media internal/final milik client lain.
2. `GET /v1/admin/finance/expenses` memakai `WorkerExpensePolicy::viewAny` (`FinanceController.php:22-33`). Policy itu mengizinkan setiap role `WORKER` (`WorkerExpensePolicy.php:10-13`), sementara query Admin tidak membatasi `worker_profile_id`. Worker dapat melihat expense semua worker beserta project dan approver.

Ini melanggar hard blocker checklist: Client A tidak boleh melihat resource Client B, Worker tidak boleh melihat internal finance, dan endpoint protected wajib punya backend authorization yang benar.

**Perbaikan wajib:**

- pasang boundary route eksplisit `role:OWNER|ADMIN` untuk seluruh namespace Admin;
- tetap gunakan permission per aksi di dalamnya;
- pada nested resource, authorize parent (`Gate::authorize('view', $project)`) dan query hanya dari relasi yang sudah diotorisasi;
- tambah test negatif langsung terhadap URL Admin untuk CLIENT dan WORKER, bukan hanya portal URL mereka sendiri;
- tambahkan data-leakage assertion terhadap serializer Admin.

### MED-SEC-01 — Filename upload dapat masuk ke shell command FFmpeg

**Severity: P0 — high-confidence command injection**

Rantai inputnya lengkap:

- `filename` menerima string bebas hingga 255 karakter dan `mime_type` juga string bebas (`InitiateDirectUploadRequest.php:17-25`);
- extension dari nama file dipakai mentah di storage key (`MediaStorageService.php:18-28`);
- filename tersebut menjadi bagian dari temporary input path (`ProcessVideoMediaJob.php:47-52`);
- path dimasukkan ke command string yang dieksekusi shell oleh Laravel Process (`FFmpegProcessorService.php:59-61`, `:130-132`, `:164-173`).

Nama file dengan shell substitution/quote di extension berpotensi mengeksekusi perintah sebagai user queue worker.

**Perbaikan wajib:**

- jangan pernah menjalankan command sebagai string; gunakan array argumen Process agar tidak melalui shell;
- map MIME yang diizinkan ke extension server-side, jangan percaya extension client;
- whitelist format video/image dan lakukan MIME sniffing dari bytes;
- buat nama file server-side sepenuhnya; simpan nama asli hanya sebagai metadata display;
- tambah regression test dengan filename berisi quote, `$()`, backtick, semicolon, slash, Unicode separator, dan extension ganda;
- jalankan worker dengan user non-root, filesystem minimal, dan sandbox resource limit.

---

## 5. Temuan P1 — blocker Release A

### SEC-02 — Admin biasa dapat membuat atau mempromosikan dirinya menjadi Owner

`UserManagementController` hanya mengecek role `OWNER` atau `ADMIN` (`:20-25`, `:48-53`, `:105-110`). Endpoint `store` dan `update` menerima role apa pun yang ada di tabel, lalu `syncRoles` tanpa larangan Owner (`:55-71`, `:112-140`). Admin juga dapat mengganti password/email/status user lain dan menonaktifkan Owner atau dirinya sendiri (`:124-169`).

Ini adalah privilege escalation dan account takeover yang bertentangan langsung dengan aturan “ordinary Admin cannot self-promote/grant Owner”. Operasi juga tidak dibungkus transaction sehingga create user dapat tersisa tanpa role jika langkah berikutnya gagal.

**Fix:** aksi role sensitif harus Owner-only; gunakan permission `users.manageRoles`; larang self-promotion, last-owner removal, self-disable, dan Admin mengubah Owner; lock target user; transaction + audit before/after; test semua kombinasi negatif.

### SEC-03 — User yang sudah login tetap aktif setelah status menjadi Suspended/Disabled

Status `ACTIVE` hanya diperiksa saat autentikasi di `FortifyServiceProvider.php:69-80`. Grup API memakai `auth:sanctum` tanpa middleware status aktif (`bootstrap/app.php:15-17`, `routes/api.php:68`). Test yang ada hanya membuktikan user suspended tidak bisa login dari awal (`AuthenticationTest.php:44-75`), bukan pencabutan sesi yang sudah ada.

**Impact:** akun yang dinonaktifkan dapat terus memakai cookie/session lama sampai sesi habis.

**Fix:** middleware `EnsureUserIsActive` pada semua route protected, revoke token/session saat status berubah, dan test “login → status changed → next request 401/403”.

### SEC-04 — Mapping Policy core kemungkinan salah class dan membuat endpoint sah gagal

`AuthServiceProvider` mendaftarkan policy untuk alias `App\Models\Client/Quotation/Invoice/Project/...` (`AuthServiceProvider.php:37-51`), tetapi controller dan route binding menggunakan model `App\Domains\...\Models\...`. Alias di `App\Models` adalah subclass dari model domain, bukan sebaliknya; instance model domain tidak otomatis cocok ke key alias. Selain itu, `User::class` secara eksplisit dipetakan ke `WorkerPolicy` (`:48`), yang tidak semestinya.

Karena runtime PHP tidak tersedia, efek aktual belum dapat dibuktikan di audit ini. Secara desain Laravel Gate, ini sangat mungkin menyebabkan “policy not defined”/denial pada banyak endpoint core, dan test suite seharusnya menangkapnya bila benar-benar dijalankan.

**Fix:** map exact domain model class ke policy, hapus mapping User→WorkerPolicy, atau pasang atribut/policy discovery yang eksplisit. Tambah boot test yang mengassert `Gate::getPolicyFor()` untuk setiap model domain.

### SEC-05 — Pemakaian Policy lintas portal dipasangkan dengan serializer Admin

Walau mapping core perlu diperbaiki, desain endpoint menunjukkan masalah kedua: endpoint Admin `show` memakai ability `view` yang memang mengizinkan Client/Worker atas resource mereka, tetapi mengembalikan Resource Admin.

Contoh:

- `ProjectPolicy::view` mengizinkan Client pemilik dan Worker assigned (`ProjectPolicy.php:18-37`), sedangkan `AdminProjectResource` mengembalikan `contract_value`, `assigned_admin_id`, `notes_internal`, dan assignment (`AdminProjectResource.php:16-40`).
- `QuotationPolicy::view` mengizinkan Client pemilik (`QuotationPolicy.php:18-28`), sedangkan `AdminQuotationResource` mengembalikan `notes_internal` dan inquiry Admin (`AdminQuotationResource.php:14-35`).
- `ClientPolicy::view` mengizinkan Client atas profil sendiri (`ClientPolicy.php:18-30`), sedangkan `AdminClientResource` memuat billing data, internal notes, dan daftar user (`AdminClientResource.php:12-31`).
- Admin delivery index hanya authorize Project sehingga Client pemilik dapat melihat package non-client-facing/internal via URL Admin (`DeliveryPackageController.php:18-27`).

**Fix:** route Admin harus punya boundary role; portal-specific Policy/ability dan Resource harus dipisahkan; tambah response-shape tests per role.

### AUTH-01 — Public self-registration aktif tanpa keputusan produk dan menghasilkan Client tanpa entitas Client

`config/fortify.php:20` mengaktifkan `Features::registration()`. `CreateNewUser` langsung membuat user `ACTIVE` dan memberi role `CLIENT` (`CreateNewUser.php:16-40`). Technical spec menyatakan self-registration tidak otomatis aktif dan hanya boleh di-enable jika PRD mengizinkan (`docs/TECHNICAL.md:988`). PRD justru menetapkan invitation/activation flow (`docs/PRD.md:581`).

User hasil register tidak ditautkan ke record `clients`, lalu frontend mengarahkannya ke `/client/dashboard` (`register/page.tsx:45-47`), sehingga portal kosong dan lifecycle client menjadi tidak konsisten.

**Fix:** matikan registration sampai ada keputusan eksplisit; gunakan invitation-only flow, atau desain onboarding client yang benar lengkap dengan anti-abuse, verification, dan linking rule.

### PAY-01 — Callback belum menjadi payment authority yang aman

Webhook hanya memverifikasi callback signature lalu langsung memakai `resultCode` callback (`DuitkuWebhookController.php:20-65`). Ia **tidak** memanggil `checkTransaction`, tidak membandingkan amount callback dengan amount transaksi/invoice, dan menerima merchant code kosong karena mismatch hanya ditolak bila field tidak kosong (`DuitkuPaymentGateway.php:130-159`).

Dokumen internal secara eksplisit meminta signature + merchant/order/amount + transaction-status verification. Dokumentasi resmi Duitku POP saat ini juga menyatakan callback HMAC-SHA256 mengikat merchant code, amount, dan merchant order ID. Signature valid hanya membuktikan payload dibuat pihak yang memegang key; aplikasi tetap harus mencocokkan event ke transaksi internal dan invariant jumlah/status.

**Fix:** parse payload dengan Form Request/DTO whitelist, wajibkan semua field, `hash_equals`, load transaksi di transaction, cocokkan provider/order/merchant/amount/reference, lakukan authoritative status check sesuai kontrak provider, lalu terapkan state transition monotonic.

### PAY-02 — Idempotency callback tidak aman terhadap concurrency dan event terlambat

Pre-check status terjadi sebelum transaction (`DuitkuWebhookController.php:45-48`). Setelah `lockForUpdate`, status tidak dicek ulang (`:50-54`). Dua callback paralel dapat sama-sama menulis history, mengirim email, dan mencoba activation. Callback terlambat juga dapat menurunkan `PAID` menjadi `FAILED/PENDING` karena tidak ada transition guard (`:53-65`).

Test “duplicate callback” hanya menguji transaksi yang dari awal sudah berada pada target status (`DuitkuPaymentTest.php:163-181`); tidak menguji dua request paralel atau event out-of-order.

**Fix:** lock lalu re-read/re-check di dalam transaction; state machine monotonic; event/idempotency key unik; unique constraints untuk side effect; outbox/after-commit job; test concurrent delivery dan reversed ordering.

### PAY-03 — Invoice reconciliation dan admin status check tidak konsisten

Baik callback maupun manual check menandai invoice `PAID` berdasarkan satu transaksi dan mengisi `paid_amount` dari amount transaksi (`DuitkuWebhookController.php:75-81`, `PaymentTransactionController.php:75-80`). Tidak ada recompute jumlah PAID, aturan partial payment, atau verifikasi bahwa jumlah sama dengan outstanding invoice.

Manual `checkStatus` memakai model transaksi yang tidak dikunci (`PaymentTransactionController.php:48-65`) dan tidak memicu project activation, sementara callback memicu activation. Hasil bisnis tergantung jalur mana yang menang.

**Fix:** satukan semua provider event dalam satu Action/handler; recompute invoice balance dari ledger; lock transaction+invoice; gunakan transition rule yang sama; activation dipicu dari domain event `InvoicePaymentSatisfied`, bukan controller tertentu.

### PAY-04 — Project activation belum exactly-once dan dapat memakai quotation yang tidak accepted

`ActivateProjectAction` melakukan “check then create” tanpa lock/unique constraint (`ActivateProjectAction.php:17-24`). Migration `projects` tidak membuat `quotation_id` unique dan `accepted_quotation_version_id` bahkan tidak memiliki FK (`create_projects_table.php:15-18`). Action memilih `acceptedVersion ?? currentVersion`, sehingga quotation tanpa accepted version tetap dapat dibuat menjadi project, dan tidak mengecek status Accepted atau DP satisfied.

**Fix:** unique index `projects.quotation_id` untuk activation berbasis quotation, FK accepted version, lock quotation/invoice, assert quotation Accepted + version immutable + payment rule satisfied, tangani duplicate-key sebagai idempotent success.

### MED-01 — Finalize upload tidak terikat ke pembuat intent dan tidak memverifikasi object

`FinalizeDirectUploadAction` mencari pending upload hanya dari `public_id`, tanpa memastikan `user_id === actor.id` dan tanpa re-authorize project (`:17-31`). Action juga tidak mengecek object ada, size aktual, MIME aktual, checksum, atau upload ETag sebelum membuat `MediaAsset` dan menandainya completed (`:34-60`).

Siapa pun yang memiliki permission create dan memperoleh public ID intent pengguna lain dapat memfinalisasi intent itu atas namanya sendiri. Asset palsu/tidak ada juga dapat menjadi `READY` untuk non-video.

**Fix:** bind intent ke actor/tenant/project, verify ownership dan capability lagi, HEAD object, validate size/MIME/checksum, status/expiry one-time, dan gunakan unique relation pending_upload→asset.

### MED-02 — Pipeline media menyembunyikan kegagalan sebagai keberhasilan

Temuan terkonfirmasi:

- file source hilang diganti payload video simulasi (`ProcessVideoMediaJob.php:55-69`);
- metadata file hilang/FFmpeg hilang/gagal diganti angka palsu 1080p/H.264 (`FFmpegProcessorService.php:34-56`, `:63-114`);
- thumbnail/preview gagal diganti string “MOCK/FALLBACK” lalu method mengembalikan `true` (`:121-145`, `:151-185`);
- job kemudian menandai asset `READY` (`ProcessVideoMediaJob.php:101-112`);
- stream object yang hilang mengembalikan HTTP 200 berisi teks dummy (`DirectStreamController.php:41-48`).

Ini melanggar aturan no-fake production behavior dan dapat membuat file rusak dirilis ke client.

**Fix:** hapus seluruh fallback palsu dari production path; missing source/FFmpeg/transcode harus `FAILED`, retry terukur, alert, dan tidak client-visible; mock hanya boleh lewat fake test service di environment test.

### MED-03 — Release/final delivery tidak menegakkan approval dan payment rule

Upload dengan kategori `CLIENT_PREVIEW` atau `FINAL_MASTER` otomatis diberi visibility client/final (`InitiateDirectUploadAction.php:37-41`), bukan Internal sampai explicit release. `ReleaseMediaAssetAction`/policy tidak membuktikan processing `READY`, approval revision, kategori, atau payment condition.

`CreateDeliveryPackageAction` langsung menandai package `READY` dan seluruh asset `FINAL_RELEASED` (`:45-68`) tanpa cek final payment/approval. Padahal PRD OD-003 menyatakan final-release-vs-payment masih open decision. “Package” dengan banyak file juga hanya menunjuk storage key asset pertama (`:38-50`); download URL pun hanya menghasilkan URL asset pertama (`GenerateDeliveryDownloadUrlAction.php:41-49`) walau response mengklaim `file_count > 1`.

**Fix:** default semua upload Internal; explicit release Action dengan policy produk yang sudah diputuskan; hanya asset READY+approved+eligible; buat manifest/ZIP nyata atau kembalikan array signed URL; pencatatan download terjadi saat bytes benar-benar disajikan, bukan saat URL dibuat.

### APP-01 — Helper API frontend tidak mengirim CSRF header pada mutation

Login/register/logout membaca cookie `XSRF-TOKEN` dan mengirim `X-XSRF-TOKEN`, tetapi helper umum `fetchApi` hanya mengirim Accept dan Content-Type (`apps/web/src/lib/api.ts:49-65`). Hampir semua POST/PATCH/DELETE portal memakai helper ini. Pada Sanctum SPA stateful, cookie CSRF perlu direfleksikan ke header/request token; browser tidak melakukannya otomatis.

**Impact:** mutation setelah login sangat mungkin menghasilkan HTTP 419 walau GET berjalan.

**Fix:** satu HTTP client yang bootstrap CSRF sekali, membaca/decodes cookie, menambah header untuk unsafe methods, dan menangani 401/403/419 secara berbeda. Tambah integration test browser nyata.

### APP-02 — Kontrak route memiliki endpoint mati dan frontend memanggil endpoint yang tidak ada

`Route::apiResource` otomatis mendaftarkan method yang tidak diimplementasikan:

- Quotation: `update` tidak ada;
- Invoice: `update` tidak ada;
- Project: `update` tidak ada;
- Task: `update` tidak ada;
- Schedule: `update` tidak ada;
- User: `destroy` tidak ada.

Bukti fungsi controller diperoleh dari class masing-masing, sementara pendaftaran resource ada di `routes/api.php:86-170`.

Selain itu:

- frontend `/client/schedule` memanggil `/api/v1/client/schedules` (`client/schedule/page.tsx:25-35`), tetapi route client tersebut tidak ada (`routes/api.php:201-235`);
- email invitation menuju `/auth/accept-invitation` (`ClientInvitationMail.php:30-39`), tetapi frontend tidak memiliki halaman tersebut;
- login menautkan `/forgot-password`, tetapi halaman forgot/reset/verify belum ada.

**Fix:** gunakan `only()/except()` pada resource route, implementasikan contract yang benar, tambah contract test dari OpenAPI, dan jangan render link sebelum endpoint+UI selesai.

### QA-01 — Gate kualitas frontend gagal

Hasil verifikasi aktual:

- `pnpm typecheck`: **PASS**;
- `pnpm build`: **PASS**, tetapi ada warning convention `middleware` deprecated pada Next 16;
- `pnpm lint`: **FAIL — 62 error, 34 warning**;
- `pnpm --filter @bdjg/web test`: **FAIL — no test files found**.

Lint menangkap `any` eksplisit, effect/state patterns, missing dependencies, variable accessed before declaration, dan unused value. CI saat ini tidak menjalankan frontend test (`.github/workflows/ci.yml:95-102`), jadi kegagalan Vitest tidak terlihat di CI.

---

## 6. Temuan P2 — arsitektur, kontrak, dan operasional

### DOM-01 — DP 50% dan aturan pelunasan/final release di-hardcode meski masih open decision

PRD menandai exact DP default dan final release vs final payment sebagai OD-002/OD-003 (`docs/PRD.md:1677-1678`). Implementasi menetapkan default `50.00` di migration (`create_quotation_versions_table.php:22-24`), controller (`QuotationController.php:81-105`, `:169-193`), dan template frontend. Ini adalah business rule yang diinventarisasi sebelum keputusan disahkan.

**Fix:** buat keputusan produk tertulis, lalu config/policy per service/package; migration sebaiknya tidak memaksakan default bisnis yang belum final.

### DOM-02 — State machine revision tidak sama dengan vocabulary canonical

Dokumen menetapkan `REQUESTED → TRIAGE → IN_PROGRESS → INTERNAL_REVIEW → READY_FOR_CLIENT → APPROVED → CLOSED`. Kode hanya memiliki `OPEN`, `IN_PROGRESS`, `SUBMITTED`, `RESOLVED`, `CLOSED` (`RevisionRoundStatus.php:5-21`) dan migration default `OPEN` (`create_revisions_table.php:16-20`). Akibatnya dashboard/status filter/audit tidak dapat memenuhi alur yang didokumentasikan.

### DOM-03 — Schema task dan schedule belum memenuhi requirement domain

- Task belum memiliki reviewer, start date, dependencies, attachment/comment context (`create_tasks_table.php:11-24`).
- Schedule belum memiliki visibility, status, assigned team/participant (`create_schedules_table.php:11-23`).
- Karena visibility tidak ada, `SchedulePolicy` mengizinkan Client melihat semua schedule proyeknya (`SchedulePolicy.php:18-35`), termasuk event yang semestinya internal.

### DOM-04 — Critical quotation mutation tidak memakai satu domain boundary yang atomic/auditable

Client accept/revision/decline langsung mengubah quotation dan inquiry di controller, tanpa transaction, row lock, atau audit (`ClientQuotationController.php:54-123`). Create revision memakai `max(version_number)+1` tanpa mengunci parent (`QuotationController.php:146-181`), sehingga dua request paralel dapat collision. Send quotation juga mengirim email sinkron dan tidak mencatat audit (`:221-253`).

**Fix:** pindahkan ke Action per transition, lock quotation, state guard, transaction, audit, after-commit notification, dan concurrency tests.

### API-01 — OpenAPI/generated client belum diwujudkan; ID internal tetap menjadi URL contract

Technical spec mewajibkan OpenAPI 3.1 dan generated TypeScript client. Scramble ada sebagai dependency, tetapi tidak ditemukan spec OpenAPI yang disimpan, `packages/api-client`, pipeline validation, atau generated type freshness check. Frontend menulis interface manual di banyak page dan `src/lib/api.ts`.

Dokumen juga mengatakan URL external memakai `public_id` (`docs/TECHNICAL.md:856`, `:1515-1544`). Semua model memiliki public ID tetapi tidak ada `getRouteKeyName()`/explicit `{model:public_id}`; API Resource dan frontend tetap memakai numeric `id`.

### OPS-01 — Runtime/queue/CI tidak sama dengan baseline technical

| Target dokumen | Implementasi saat ini |
|---|---|
| PHP 8.5 | Composer mengizinkan `^8.3`; CI memakai PHP 8.3 |
| Redis queue + Horizon | `QUEUE_CONNECTION=database`; package/config Horizon tidak ada |
| Redis cache | `.env.example` memakai database cache |
| OpenAPI validation | tidak ada di CI |
| Frontend tests | script ada, tetapi CI tidak menjalankan dan test file tidak ada |
| Playwright/E2E | dependency/config/test tidak ada |
| seeded proof | CI hanya `migrate:fresh`, tidak `--seed` |

Referensi: `apps/api/composer.json:8-17`, `.github/workflows/ci.yml:41-75`, `apps/api/.env.example:28-44`, `apps/web/package.json:5-29`.

### OPS-02 — Konfigurasi Duitku fail-open dan versi integrasi tidak tunggal

`DuitkuConfig` dan `config/services.php` memiliki credential placeholder dan localhost URL sebagai runtime default (`DuitkuConfig.php:5-27`, `config/services.php:38-45`) alih-alih menolak konfigurasi production yang kosong/salah.

Kode create memakai endpoint legacy-style `/v2/inquiry` dan signature MD5 (`DuitkuClient.php:14-37`, `DuitkuSignature.php:7-10`), tetapi callback mencoba HMAC-SHA256 lalu MD5 fallback (`DuitkuSignature.php:12-35`). Dokumentasi resmi POP saat audit memakai endpoint `api-sandbox.duitku.com/api/merchant/createInvoice`, HMAC header untuk create, dan HMAC callback. Ini mungkin karena merchant memakai produk/versi lama, tetapi pilihan versi tidak dikunci dan belum ada contract/sandbox proof.

**Fix:** pilih satu API Duitku yang benar untuk akun merchant, lock endpoint/auth/signature sesuai versi itu, hapus fallback algoritma yang tidak diperlukan, validate config saat boot/deploy, dan rekam sandbox evidence yang disanitasi.

### OPS-03 — Upload dan media processing belum punya guard operasional memadai

- signed local PUT dapat dipakai ulang sampai expiry, tidak mengecek pending status/expiry, size, atau MIME, dan membaca seluruh body ke memory (`DirectStreamController.php:18-29`);
- size limit intent mencapai 100 GB, sehingga implementasi local `getContent()` berisiko memory exhaustion;
- kegagalan presign S3 ditelan dan diam-diam jatuh ke local endpoint (`MediaStorageService.php:41-76`), berpotensi menutupi salah konfigurasi production;
- derivative di-upload memakai `File::get`, kembali memuat seluruh file ke memory (`ProcessVideoMediaJob.php:77-99`);
- exception mentah disimpan ke metadata (`:115-124`), sementara `MediaAssetResource` mengirim seluruh metadata ke Client (`MediaAssetResource.php:39-58`).

### SEC-06 — Public endpoint belum diberi throttle dan invitation token disimpan/ditampilkan mentah

Rate limiter hanya ada untuk login (`FortifyServiceProvider.php:83-87`; duplikat juga di `AppServiceProvider.php:26-32`). Public inquiry, invitation lookup/accept, upload signed endpoint, dan webhook tidak terlihat memakai throttle policy.

Invitation token 64 karakter disimpan plaintext (`create_client_invitations_table.php:11-19`), dicari plaintext (`ClientInvitationController.php:43-58`), dan dikembalikan lewat API Resource (`ClientInvitationResource.php:12-20`). Kebocoran database/log/response berarti bearer token dapat dipakai langsung.

**Fix:** hash token at rest, return token hanya saat penciptaan bila memang perlu, throttle berdasarkan endpoint semantics, dan tambahkan replay/expiry tests.

### UI-01 — Portal guard dan navigation tidak role/permission-aware

`useAuth` hanya memastikan ada user, tidak menerima required role (`use-auth.ts:6-30`). Layout Admin/Worker/Client tidak memeriksa `user.roles` dan selalu merender nav statis (`admin/layout.tsx:27-49`, `worker/layout.tsx:17-40`, `client/layout.tsx:18-41`). Admin nav menampilkan Finance, Users/Roles, Audit, dan CMS kepada seluruh Admin tanpa effective-permission filtering.

Backend tetap harus menjadi authority, tetapi UX ini bertentangan dengan blueprint dan memperbesar permukaan endpoint salah-konfigurasi.

### UI-02 — Banyak halaman production-looking sebenarnya hanya state in-memory/demo

Halaman Admin portfolio, testimonials, messages, dan templates memakai konstanta `INITIAL_*` dan mutation `useState`, tanpa persistence/backend. Settings menampilkan `D12345 (Sandbox)`, callback localhost, serta badge `S3 / LOCAL READY` dan `FFmpeg Queued Jobs` walau konfigurasi/worker sebenarnya belum dibuktikan (`admin/system/settings/page.tsx`). Global search hanya menyimpan `searchQuery` tanpa melakukan search; tombol Profile juga tidak bernavigasi (`dashboard-shell.tsx:154-203`).

Ini melanggar gate “no dummy completion” dan membuat UI terlihat operasional padahal perubahan hilang setelah refresh.

---

## 7. Temuan P3 — maintainability dan hygiene

### MAINT-01 — Repo sulit direview karena duplikasi model, strict typing absen, lockfile campur, dan line ending noise

- 243 file PHP di `apps/api/app`, tetapi tidak satu pun memakai `declare(strict_types=1)` walau technical baseline menyebut PHP strict typing.
- Terdapat model alias `App\Models\X extends App\Domains\...\Models\X` yang menjadi sumber mapping Policy ambigu.
- `pnpm-lock.yaml` hidup berdampingan dengan `apps/web/package-lock.json` walau workspace memilih pnpm.
- 42 penggunaan `any` ditemukan di source TypeScript.
- `git diff --check` meledak karena trailing whitespace/CRLF pada hampir seluruh snapshot.
- Dokumentasi inti berjumlah 24.620 baris / sekitar 499 KB dan banyak aturan yang berulang di PRD, Technical, Implementation, Checklist, dan AGENTS, sehingga drift menjadi sangat mudah.

**Fix:** pilih satu class model canonical, satu package manager, normalisasi `.gitattributes`/line ending dalam commit mekanis terpisah, aktifkan strict types untuk file baru lalu migrasi bertahap, dan jadikan docs lebih referensial daripada menduplikasi aturan.

---

## 8. Audit dokumentasi/blueprint

### Yang sudah bagus

- Hierarki source-of-truth dijelaskan jelas.
- Role, ownership, assignment, no-leak rule, payment authority, idempotency, dan release gate tertulis cukup kuat.
- Pemisahan Release A/B/C/D masuk akal sebagai strategi delivery.
- Checklist berusaha meminta evidence konkret: migration, test, browser, OpenAPI, sandbox ID, security review.
- Design system membedakan ekspresi Admin/Worker/Client dan memberi arah visual yang detail.

### Yang perlu dibenahi

1. **Nama dokumen canonical tidak konsisten.** PRD menyebut `DESIGN-v1.md` dan `DESIGN.md` sebagai source (`docs/PRD.md:8,27`), sementara file yang ada hanya `DESIGN.md`; metadata di dalamnya masih berkata `DESIGN-v1.md` (`docs/DESIGN.md:3,21`).
2. **Checklist bukan evidence ledger.** Centang tidak memiliki link commit/CI/test dan ada centang yang berlabel deferred. Gunakan satu evidence record per phase.
3. **Istilah MVP membingungkan.** PRD memuat scope besar, lalu Implementation menyebut Release A “Dashboard MVP” dan menunda modul lain. Tuliskan satu kalimat tegas bahwa Product MVP dan Release A operational slice berbeda.
4. **Open decision tidak memblokir hardcode.** OD-002/OD-003 masih terbuka tetapi kode dan template sudah menetapkan 50/50. Open decision harus punya owner, due date, dan enforcement di PR/CI.
5. **Dokumen terlalu panjang dan repetitif.** `AGENTS.md` sendiri 4.734 baris. Rule yang sama muncul berulang dan justru membuat sinkronisasi sulit. Pertahankan satu canonical statement, lalu link dari dokumen lain.
6. **README jauh di bawah kebutuhan blueprint.** Quick start tidak menjalankan seed, queue worker, Horizon, test frontend, sandbox callback, storage, atau langkah evidence; tidak ada troubleshooting dan environment matrix.

---

## 9. Hasil verifikasi yang dijalankan

| Pemeriksaan | Hasil | Catatan |
|---|---|---|
| Safe ZIP extraction | PASS | 847 entry; tidak ditemukan path traversal saat ekstraksi |
| `pnpm install --frozen-lockfile --ignore-scripts` | PASS | lockfile dapat di-resolve |
| `pnpm typecheck` | PASS | TypeScript selesai tanpa error |
| `pnpm build` | PASS WITH WARNING | 47 route dibangun; `middleware.ts` deprecated di Next 16, gunakan `proxy.ts` |
| `pnpm lint` | **FAIL** | 62 error, 34 warning |
| `pnpm --filter @bdjg/web test` | **FAIL** | tidak ada test file |
| Backend Pest | NOT RUN / BLOCKED | `php` dan `composer` tidak tersedia |
| Migration + seeder | NOT RUN / BLOCKED | runtime PHP/MySQL tidak tersedia |
| Duitku Sandbox | NOT RUN | tidak ada credential/evidence sandbox |
| Playwright/E2E | NOT AVAILABLE | dependency/config/test tidak ada |
| `git diff --check` | FAIL/NOISY | CRLF/trailing whitespace di snapshot dan worktree sudah sangat kotor |
| Secret pattern scan terbatas | PASS WITH CAVEAT | tidak ditemukan private key/API key nyata; placeholder Duitku ditemukan sesuai `.env.example` |

Build yang lulus **tidak** menghapus blocker lint, test, authorization, payment, atau runtime backend.

---

## 10. Roadmap perbaikan yang disarankan

### Paket 0 — containment (segera)

1. Jangan deploy snapshot ini.
2. Disable route media Admin yang bocor dan worker finance Admin sampai boundary diperbaiki.
3. Pause queue media/FFmpeg dan final delivery.
4. Batasi User & Role Management menjadi Owner-only sementara.
5. Matikan public registration.

### Paket 1 — authorization dan lifecycle

1. Tambahkan middleware alias Spatie dan boundary `role:OWNER|ADMIN`, `role:WORKER`, `role:CLIENT` per portal.
2. Tambahkan middleware active-user pada seluruh protected route.
3. Betulkan mapping exact domain model→policy.
4. Pisahkan ability/Resource Admin, Worker, Client.
5. Audit setiap nested route: authorize parent, lalu query melalui relasi parent.
6. Implement permission sensitive (`finance.view`, `audit.view`, `users.manageRoles`, `media.release`) dan jangan bypass dengan `hasRole('ADMIN')`.

### Paket 2 — payment correctness

1. Pilih dan kunci versi API Duitku.
2. Buat `ProcessPaymentProviderEventAction` tunggal untuk callback dan manual status check.
3. Lock transaction+invoice; verify merchant/order/amount/reference/status.
4. State transition monotonic dan idempotency key unique.
5. Recompute invoice balance dari transaksi PAID, bukan assign satu amount.
6. Trigger activation dari domain event setelah commit.
7. Tambah unique project quotation, FK accepted version, serta invariant Accepted+DP satisfied.
8. Queue receipt setelah commit; jangan kirim email di DB transaction.

### Paket 3 — media safety

1. Hilangkan shell string dan seluruh fake-success fallback.
2. Server-generated filename + whitelist extension/MIME.
3. Intent terikat actor/project; verify object HEAD/size/MIME/checksum sebelum finalize.
4. Stream upload, jangan `getContent()` untuk file besar; batasi upload size sesuai kebutuhan nyata.
5. Default visibility Internal; explicit review/release.
6. Final package harus benar-benar berisi semua file atau menyajikan manifest multi-URL yang konsisten.

### Paket 4 — contract dan frontend

1. Selesaikan/matikan route resource yang method-nya tidak ada.
2. Buat OpenAPI 3.1 sebagai contract dan generated TypeScript client.
3. Gunakan `public_id` untuk route external.
4. Perbaiki CSRF helper, portal role guard, forbidden/error state, dan permission-aware nav.
5. Implement invitation, forgot/reset/verify UI sebelum link ditampilkan.
6. Pindahkan CMS/messages/templates demo ke feature flag dev atau backend nyata.
7. Nolkan lint error, buat unit/component tests, lalu Playwright critical flows.

### Paket 5 — kembali ke phase gates

Urutan yang disarankan:

```text
Phase 1 evidence
→ Phase 2 authorization matrix
→ Phase 3 real portal shells
→ Phase 4–9 domain correctness
→ Phase 10 real Duitku sandbox
→ Phase 11 exactly-once activation
→ Phase 12–21 Release A
→ Release A integrated gate
→ baru hidupkan kembali Release B/C/D
```

---

## 11. Regression suite minimum sebelum Release A boleh dibahas lagi

### Authorization

- Client tidak dapat memanggil satu pun `/v1/admin/*`.
- Worker tidak dapat memanggil satu pun `/v1/admin/*` kecuali endpoint yang secara eksplisit dibuka dan scoped.
- Client A tidak dapat enumerate media/schedule/delivery/quotation/invoice milik Client B.
- Worker A hanya melihat project/task/schedule/media yang assigned dan tidak melihat contract value, margin, invoice, payment, internal notes.
- Admin tanpa permission finance/audit/role-management ditolak.
- Admin tidak dapat memberi/mengambil Owner, mengubah password Owner, menonaktifkan last Owner, atau self-promote.
- Suspended/Disabled user dengan sesi aktif ditolak pada request berikutnya.

### Payment

- merchant code kosong/mismatch ditolak;
- amount mismatch ditolak dan diaudit;
- callback signature valid tetapi transaction-status provider tidak PAID tidak boleh membayar invoice;
- dua callback paralel menghasilkan satu history transition/receipt/activation;
- callback PAID lalu FAILED tidak meregres status;
- status-check dan callback menghasilkan post-condition identik;
- partial/multiple payment menghitung paid/outstanding dengan benar;
- project activation hanya sekali dan hanya dari quotation accepted + payment condition satisfied.

### Media

- filename berbahaya tidak pernah masuk shell/path;
- finalize intent user lain ditolak;
- missing/size/MIME/checksum mismatch ditolak;
- missing FFmpeg/source/transcode menandai FAILED, bukan READY;
- Client tidak menerima URL asset Internal/Processing/Failed;
- final release ditolak sampai seluruh rule produk terpenuhi;
- package multi-file benar-benar mengunduh seluruh file.

### Frontend/E2E

- login, logout, session refresh, 401, 403, 419;
- forgot/reset/verify/invitation;
- quotation accept → DP invoice → Duitku sandbox → verified payment → exactly-one project;
- Admin/Worker/Client direct URL isolation;
- mutation POST/PATCH/DELETE dengan CSRF;
- empty/error/forbidden/loading states;
- no dead production navigation.

---

## 12. Penilaian akhir

Blueprint-nya lebih matang daripada implementasinya. Masalah utama bukan kurang banyak fitur, melainkan implementasi berjalan terlalu lebar sebelum invariant paling dasar—authorization boundary, payment authority, media integrity, dan evidence gate—dikunci.

Kalau scope dibekukan dan tim fokus pada akar masalah, proyek ini masih sangat bisa diselamatkan tanpa rewrite total. Domain structure, enum, Resource, migration, dan test skeleton yang sudah ada dapat dipertahankan. Yang perlu dirombak adalah boundary dan alur mutation kritis, bukan seluruh UI atau seluruh backend.

**Verdict final: BLOCKED. Earliest active gate: Phase 1. Release A belum selesai; Release B/C/D harus dianggap eksperimen/parsial sampai Release A benar-benar lulus.**

---

## 13. Referensi eksternal yang dipakai

- [Dokumentasi resmi Duitku POP — create invoice dan HMAC callback](https://docs.duitku.com/pop/en/)

Tidak ada klaim completion yang diambil dari dokumentasi eksternal; keputusan audit terutama diturunkan dari source-of-truth repo dan implementasi snapshot.
