# BDJG — Technical Specification

**Document:** `TECHNICAL.md`\
**Project:** BDJG Creative Studio Website & Studio Management System\
**Status:** Technical Baseline\
**Architecture:** Modular Monolith / Monorepo\
**Primary Backend:** Laravel 13\
**Primary Frontend:** Next.js\
**Database:** MySQL\
**Payment Gateway:** Duitku

---

# 1. Tujuan Dokumen

Dokumen ini menjelaskan **bagaimana sistem BDJG harus dibangun secara teknis**.

Dokumen ini tidak menggantikan:

- `PRD.md`
- `blueprint.md`
- `DESIGN.md`

Masing-masing mempunyai tanggung jawab berbeda.

| Dokumen        | Menjawab                                                        |
| -------------- | --------------------------------------------------------------- |
| `PRD.md`       | Apa yang harus dibuat dan apa requirement produk                |
| `blueprint.md` | Bagaimana bisnis, role, data, status, dan workflow BDJG bekerja |
| `DESIGN.md`    | Bagaimana BDJG terlihat dan berinteraksi                        |
| `TECHNICAL.md` | Bagaimana BDJG diimplementasikan secara teknis                  |
| `AGENTS.md`    | Bagaimana AI coding agent harus bekerja di repository           |

`TECHNICAL.md` **tidak boleh mengubah atau meniadakan requirement produk** yang sudah ditetapkan di `PRD.md`.

Jika terdapat konflik:

1. Product behavior → `PRD.md`
2. Business/domain behavior → `blueprint.md`
3. UI/UX → `DESIGN.md`
4. Technical implementation → `TECHNICAL.md`
5. Agent workflow → `AGENTS.md`

---

# 2. Technical Principles

Seluruh implementasi BDJG harus mengikuti prinsip berikut.

## 2.1 Simplicity First

Gunakan solusi paling sederhana yang memenuhi requirement.

Jangan menggunakan:

- microservices tanpa kebutuhan;
- event sourcing penuh;
- Kubernetes;
- distributed database;
- multiple backend frameworks;
- teknologi baru hanya karena sedang populer.

Kompleksitas hanya boleh ditambahkan jika terdapat kebutuhan nyata dan terukur.

---

## 2.2 Modular, Not Microservices

BDJG menggunakan:

**Modular Monolith**

Artinya domain sistem tetap dipisahkan menjadi module/bounded area yang jelas, tetapi tidak dipisah menjadi banyak service independen.

Contoh:

```text
CRM
Quotation
Billing
Payment
Project
Task
Schedule
File
Preview
Revision
Finance
Notification
Audit
```

semuanya tetap menjadi bagian dari satu aplikasi backend Laravel.

Pemisahan domain dilakukan melalui namespace, folder, Action/Service/Policy, event, job, dan integration boundary — bukan dengan membuat service network baru.

---

## 2.3 Backend Is Business Authority

Frontend tidak boleh menjadi sumber kebenaran business rule.

Contoh:

Frontend boleh menyembunyikan tombol:

```text
Release Final File
```

tetapi backend tetap wajib memeriksa:

```text
user permission
+
project relationship
+
project state
+
payment/release policy
```

Hidden button **bukan security**.

Semua perubahan state penting harus terjadi melalui action/use-case backend yang tervalidasi dan terotorisasi.

---

## 2.4 Deny by Default

Protected resource dianggap **tidak boleh diakses** sampai authorization membuktikan bahwa user berhak mengaksesnya.

Berlaku untuk:

- project;
- quotation;
- invoice;
- payment;
- files;
- preview;
- revision;
- client information;
- finance;
- audit log;
- worker assignment.

Laravel Policy/Gate harus mengikuti prinsip ini. Jangan membuat fallback "allow" hanya karena role terlihat benar.

---

## 2.5 Measured Scaling

Go, microservices, distributed queue, Octane, Reverb, atau service tambahan hanya boleh diperkenalkan berdasarkan hasil profiling atau kebutuhan operasional nyata.

Go **bukan bagian dari MVP backend BDJG**.

Go dapat digunakan di masa depan untuk specialized workload seperti:

- media processing dengan volume sangat tinggi;
- high-volume webhook processing;
- specialized worker;
- CPU intensive processing.

Business domain utama tetap berada di Laravel kecuali terdapat Architecture Decision Record/keputusan arsitektur baru yang terdokumentasi.

Laravel Octane juga **bukan baseline MVP**. Gunakan request lifecycle Laravel biasa terlebih dahulu. Octane hanya dipertimbangkan bila profiling membuktikan bahwa aplikasi membutuhkan long-lived application server dan tim memahami konsekuensi state antar-request.

---

# 3. Technology Baseline

Baseline teknologi BDJG per **18 Agustus 2026**:

| Layer | Technology |
| --- | --- |
| Frontend Runtime | Node.js 24 LTS |
| Frontend Package Manager | pnpm 11.x |
| Frontend Language | TypeScript |
| Public/Portal Frontend | Next.js 16.3.x |
| React | Version bawaan/supported Next.js |
| Backend Runtime | PHP 8.5.x |
| Backend Package Manager | Composer 2.x |
| Backend | Laravel 13.x |
| Backend Language | PHP 8.5 dengan strict typing |
| ORM | Eloquent ORM |
| Database | MySQL 8.4 LTS |
| Authentication | Laravel Sanctum + Laravel Fortify |
| Authorization | Laravel Policies + Gates + explicit permissions |
| Queue | Laravel Queue |
| Queue Backend | Redis |
| Queue Monitoring/Workers | Laravel Horizon |
| Object Storage | Laravel Filesystem + S3-compatible storage |
| Payment Gateway | Duitku |
| Video Processing | FFmpeg melalui queued job + Laravel Process |
| API Specification | OpenAPI 3.1 |
| OpenAPI Generator | Dedoc Scramble 0.13.x, pinned compatible version |
| Backend Testing | Pest + PHPUnit/Laravel test utilities |
| Backend Formatting | Laravel Pint |
| Frontend Unit Testing | Vitest + React Testing Library |
| Frontend E2E | Playwright |
| Local Infrastructure | Docker Compose |
| AI-assisted Development | Laravel Boost, development-only/recommended |

### 3.1 Version Policy

- Jangan melakukan hard-pin ke patch version di dokumen ini kecuali ada compatibility issue tertentu.
- Lockfile (`composer.lock`, `pnpm-lock.yaml`) adalah sumber exact version yang digunakan repository.
- Major version tidak boleh dinaikkan diam-diam oleh AI agent.
- Upgrade major harus disertai review migration/upgrade guide dan test penuh.

### 3.2 Mengapa PHP 8.5 + Laravel 13

Laravel 13 dirilis pada Maret 2026 dan mendukung PHP 8.3–8.5. Baseline BDJG menggunakan PHP 8.5 karena masih berada dalam active support yang lebih panjang daripada PHP 8.4 pada saat dokumen ini direvisi.

### 3.3 Frontend Runtime

Node.js 24 tetap digunakan karena frontend Next.js membutuhkan Node runtime dan Node 24 berada pada jalur LTS. Backend **tidak** menggunakan Node.js lagi.

### 3.4 MySQL

MySQL 8.4 tetap dipertahankan sebagai baseline relational database yang stabil untuk sistem transaksi BDJG.

### 3.5 Package Philosophy

Utamakan first-party Laravel capability sebelum menambah package pihak ketiga.

Package pihak ketiga yang menjadi baseline karena mempunyai fungsi khusus:

```text
Dedoc Scramble
→ OpenAPI generation
```

Package lain tidak boleh ditambahkan hanya untuk mengganti fitur native Laravel yang sudah memadai.

---

# 4. High-Level Architecture

Arsitektur BDJG:

```text
                         INTERNET
                            │
                            ▼
                    Reverse Proxy / CDN
                            │
                ┌───────────┴───────────┐
                │                       │
                ▼                       ▼
          Next.js Web              Laravel API
          Node.js 24               PHP 8.5
                │                       │
                └───────────┬───────────┘
                            │
              ┌─────────────┼──────────────┐
              │             │              │
              ▼             ▼              ▼
            MySQL         Redis        Object Storage
                            │              S3-compatible
                            ▼
                     Laravel Queue
                            │
                            ▼
                         Horizon
                            │
                            ▼
                    Queue Worker(s)
                            │
                ┌───────────┼────────────┐
                ▼           ▼            ▼
              Email       FFmpeg     Other Jobs


Laravel API
     │
     └──────────────► Duitku
```

### 4.1 Important Architectural Rule

API process dan queue worker **menggunakan codebase Laravel yang sama**.

Queue worker bukan aplikasi backend kedua dan bukan microservice. Production cukup menjalankan proses berbeda dari artifact/image aplikasi API yang sama:

```text
HTTP process
→ Laravel application

Queue process
→ php artisan horizon
→ Laravel application yang sama
```

Dengan demikian business rule, model, policy, integration, dan configuration tidak diduplikasi.

---

# 5. Monorepo Structure

BDJG menggunakan satu repository, tetapi dependency frontend dan backend dikelola oleh package manager masing-masing.

```text
bdjg/
│
├── apps/
│   │
│   ├── web/
│   │   └── Next.js 16 + TypeScript
│   │
│   └── api/
│       └── Laravel 13 + PHP 8.5
│
├── packages/
│   │
│   ├── api-client/
│   │   └── generated TypeScript API client/types dari OpenAPI
│   │
│   ├── ui/
│   │   └── reusable frontend UI components
│   │
│   └── config/
│       └── shared frontend tooling config bila benar-benar diperlukan
│
├── docs/
│   ├── PRD.md
│   ├── blueprint.md
│   ├── DESIGN.md
│   └── TECHNICAL.md
│
├── AGENTS.md
├── package.json
├── pnpm-workspace.yaml
├── docker-compose.yml
└── README.md
```

### 5.1 Backend Dependency Boundary

Laravel mempunyai dependency sendiri:

```text
apps/api/composer.json
apps/api/composer.lock
```

Frontend/TypeScript mempunyai dependency sendiri:

```text
package.json
pnpm-lock.yaml
```

Jangan mencoba mengelola package PHP melalui pnpm atau package JavaScript melalui Composer.

### 5.2 No Shared ORM Package

Tidak ada lagi `packages/database` seperti pada desain Prisma.

Eloquent model, migration, factory, seeder, database transaction, dan query logic berada di dalam `apps/api` karena Laravel API adalah authority backend.

### 5.3 Generated API Contract

`packages/api-client` boleh berisi generated TypeScript client/types dari OpenAPI.

Generated file:

- tidak diedit manual;
- dihasilkan dari backend contract;
- diregenerate ketika contract API berubah;
- dapat dicommit bila workflow repository memilih generated artifacts di Git.

---

# 6. Deployment Units

Walaupun satu repository, production mempunyai tiga **process/deployment responsibility** utama.

## 6.1 Web

```text
apps/web
```

Menangani:

- public website;
- Client Portal;
- Worker Portal;
- Admin Portal;
- Owner Portal;
- rendering;
- UI;
- navigation;
- SEO;
- frontend state.

---

## 6.2 API

```text
apps/api
```

Menangani:

- authentication;
- authorization;
- business logic;
- database access;
- Duitku;
- signed file access;
- REST API;
- audit;
- validation;
- queue dispatch.

Production HTTP runtime baseline:

```text
Reverse Proxy / Nginx
→ PHP-FPM
→ Laravel
```

FrankenPHP dapat dipakai sebagai alternatif deployment runtime jika dipilih secara sadar, tetapi bukan requirement domain dan tidak boleh mengubah application architecture.

---

## 6.3 Queue Worker

Queue worker **bukan folder `apps/worker` terpisah**.

Worker menjalankan codebase:

```text
apps/api
```

dengan process:

```bash
php artisan horizon
```

Menangani pekerjaan asynchronous seperti:

- email;
- notification;
- video processing;
- image derivatives;
- file processing;
- cleanup;
- retryable integration jobs.

Untuk media job, runtime worker harus mempunyai binary FFmpeg yang tersedia pada environment/container.

Worker menggunakan:

- Eloquent model yang sama;
- Actions/Services yang sama jika sesuai;
- integration boundary yang sama;
- configuration yang sama;
- database yang sama.

Worker bukan microservice independen.

---

# 7. Frontend Architecture

Frontend menggunakan:

```text
Next.js 16
App Router
TypeScript
Tailwind CSS
BDJG Design System
```

## 7.1 Public Website

Public website mengutamakan:

- SEO;
- fast initial rendering;
- optimized media;
- progressive loading;
- responsive behavior;
- cinematic visual sesuai `DESIGN.md`.

Halaman seperti:

```text
/
 /works
 /works/[slug]
 /services
 /about
 /studio
 /contact
 /book
```

harus mengutamakan Server Components jika tidak membutuhkan browser interactivity.

Client Component hanya digunakan bila memang membutuhkan:

- event handler;
- browser API;
- local interactive state;
- drag/drop;
- complex forms;
- interactive gallery;
- real-time behavior.

---

# 8. Portal Architecture

Protected portal tetap menggunakan Next.js tetapi mempunyai layout terpisah.

```text
/client/*
/worker/*
/admin/*
```

Owner portal dapat berada di bawah `/admin/*` dengan authority lebih tinggi atau layout terpisah sesuai `DESIGN.md`; keputusan route UI tidak boleh mengubah role model.

Portal harus mengambil data dari Laravel API.

Frontend **tidak boleh mengakses MySQL langsung**.

```text
Next.js
   │
   ▼
Laravel API
   │
   ▼
Eloquent / Query Builder
   │
   ▼
MySQL
```

### 8.1 Server Components and Authentication

Server Component boleh memanggil Laravel API jika diperlukan, tetapi harus meneruskan authentication context dengan aman dan tidak menyimpan secret backend ke browser bundle.

Client-side request ke protected API menggunakan session cookie Laravel/Sanctum dan CSRF mechanism yang ditentukan di bagian authentication.

---

# 9. Backend Architecture

Laravel backend dipisahkan berdasarkan domain, tetapi tetap mengikuti konvensi Laravel agar mudah dipahami developer dan AI agent.

Recommended structure:

```text
apps/api/
│
├── app/
│   ├── Domains/
│   │   ├── Auth/
│   │   ├── Users/
│   │   ├── Access/
│   │   ├── CRM/
│   │   ├── Clients/
│   │   ├── Catalog/
│   │   ├── Quotations/
│   │   ├── Billing/
│   │   ├── Payments/
│   │   ├── Projects/
│   │   ├── Assignments/
│   │   ├── Tasks/
│   │   ├── Schedules/
│   │   ├── Files/
│   │   ├── Previews/
│   │   ├── Revisions/
│   │   ├── PhotoSelection/
│   │   ├── Communications/
│   │   ├── Notifications/
│   │   ├── Finance/
│   │   ├── Cms/
│   │   └── Audit/
│   │
│   ├── Http/
│   │   ├── Controllers/Api/V1/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   │
│   ├── Integrations/
│   │   ├── Duitku/
│   │   ├── Storage/
│   │   ├── Mail/
│   │   └── WhatsApp/
│   │
│   ├── Jobs/
│   ├── Notifications/
│   ├── Providers/
│   └── Support/
│
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── routes/
│   ├── api.php
│   ├── web.php
│   └── console.php
│
└── tests/
    ├── Feature/
    └── Unit/
```

### 9.1 Domain Folder Rule

Satu domain boleh berisi:

```text
Actions/
Enums/
Events/
Exceptions/
Models/
Policies/
Queries/
Services/
ValueObjects/
```

Tidak semua subfolder harus dibuat sejak awal. Buat hanya bila ada class nyata yang membutuhkannya.

### 9.2 HTTP Is an Adapter

Controller, Form Request, dan API Resource adalah HTTP boundary.

Business rule tidak boleh bergantung pada bentuk request HTTP tertentu.

### 9.3 Repository Pattern Is Optional

Jangan otomatis membuat repository untuk setiap Eloquent model.

Gunakan Eloquent/Query Builder secara langsung di Action/Query/Service jika cukup jelas. Repository hanya dibuat jika ada kebutuhan abstraction nyata, misalnya:

- external data source;
- complex persistence boundary;
- multiple implementations;
- testing seam yang benar-benar dibutuhkan.

---

# 10. Module Pattern

Domain tidak boleh menjadi sekadar kumpulan Controller dan Eloquent Model.

Contoh pattern untuk Project:

```text
app/Domains/Projects/
│
├── Actions/
│   ├── ActivateProject.php
│   ├── CompleteProject.php
│   └── ReleaseFinalDelivery.php
│
├── Enums/
│   └── ProjectStatus.php
│
├── Events/
│   └── ProjectActivated.php
│
├── Models/
│   └── Project.php
│
├── Policies/
│   └── ProjectPolicy.php
│
├── Queries/
│   └── ListProjects.php
│
└── Services/
    └── ProjectNumberGenerator.php
```

HTTP classes:

```text
app/Http/Controllers/Api/V1/Projects/
app/Http/Requests/Projects/
app/Http/Resources/Projects/
```

Business logic kompleks harus berada di:

```text
Action
Service
Domain method
Value Object
Query object
```

bukan langsung di Controller.

Controller bertanggung jawab untuk:

```text
HTTP request
→ validated FormRequest
→ authenticated user
→ authorization
→ action/service call
→ API Resource/response
```

### 10.1 Action Rule

Gunakan Action untuk business operation yang mempunyai intention jelas.

Contoh:

```text
AcceptQuotation
IssueInvoice
CreatePaymentAttempt
AssignWorker
ReleasePreview
SubmitRevision
ReleaseFinalDelivery
```

Action lebih disukai daripada generic service method seperti:

```text
updateStatus()
process()
handleThing()
```

yang menyembunyikan maksud domain.

### 10.2 Service Container

Dependency antar integration/service harus menggunakan constructor injection dan Laravel service container.

Untuk interface yang mempunyai implementation tertentu, bind di Service Provider.

---

# 11. HTTP Runtime

Laravel 13 menangani HTTP request melalui framework HTTP kernel/middleware pipeline.

Production baseline:

```text
Reverse Proxy / CDN
      ↓
Nginx
      ↓
PHP-FPM
      ↓
Laravel public/index.php
```

### 11.1 No Octane by Default

MVP tidak menggunakan Laravel Octane hanya untuk mengejar benchmark.

Alasan:

- mayoritas workload BDJG adalah database/business workflow;
- heavy media processing dipindahkan ke queue;
- public media dilayani object storage/CDN;
- request lifecycle standar lebih sederhana untuk development dan operasi.

Jika profiling di masa depan menunjukkan bottleneck HTTP framework yang signifikan, Octane/FrankenPHP dapat dievaluasi melalui ADR.

### 11.2 Web Server Rule

Web server harus mengarahkan Laravel ke folder:

```text
apps/api/public
```

Jangan expose root project Laravel ke public web root karena dapat membocorkan file konfigurasi/dependency.

---

# 12. API Strategy

BDJG menggunakan:

**REST API**

bukan GraphQL untuk MVP.

Business API base path:

```text
/api/v1
```

Contoh:

```text
GET    /api/v1/projects
GET    /api/v1/projects/{projectPublicId}

GET    /api/v1/projects/{projectPublicId}/tasks

POST   /api/v1/quotations/{quotationPublicId}/accept

GET    /api/v1/invoices/{invoicePublicId}

POST   /api/v1/payments/duitku/create

POST   /api/v1/webhooks/duitku

POST   /api/v1/projects/{projectPublicId}/revisions
```

### 12.1 Authentication Routes

First-party browser authentication menggunakan Laravel Sanctum + Fortify.

Karena Fortify adalah headless authentication backend, authentication bootstrap/flow dapat menggunakan endpoint framework seperti:

```text
GET  /sanctum/csrf-cookie
POST /login
POST /logout
POST /forgot-password
POST /reset-password
POST /email/verification-notification
```

Jangan membuat duplicate JWT auth API hanya agar seluruh route terlihat berada di bawah `/api/v1`.

Jika suatu hari route auth perlu diprefix secara khusus, lakukan sebagai satu keputusan arsitektur konsisten dan update OpenAPI + frontend contract.

### 12.2 Route Model Binding

Route public identifier harus resolve resource berdasarkan `public_id`, bukan numeric internal ID.

Implementasi dapat menggunakan explicit binding atau custom route key sesuai kebutuhan.

---

# 13. OpenAPI

Laravel API harus menghasilkan **OpenAPI 3.1 specification**.

Baseline generator:

```text
Dedoc Scramble 0.13.x
```

Gunakan exact compatible release di `composer.lock`; jangan mengandalkan floating version di production.

API specification menjadi sumber untuk:

- dokumentasi API;
- frontend API types;
- API client generation;
- contract verification;
- integration tests.

Frontend dan backend tidak boleh memelihara tipe request/response yang sama secara manual di dua tempat bila dapat digenerate.

### 13.1 Contract Rule

Form Request + API Resource + explicit return shape harus cukup jelas agar OpenAPI yang dihasilkan tidak ambigu.

Jika generator tidak dapat menginfer contract dengan benar, tambahkan annotation/configuration yang diperlukan **atau** tulis contract secara eksplisit. Jangan menerima OpenAPI yang salah hanya karena "auto-generated".

### 13.2 CI Rule

CI harus dapat:

```text
boot Laravel
→ generate/export OpenAPI
→ memastikan generation berhasil
→ generate/check TypeScript API client bila workflow repository mengharuskannya
```

Perubahan endpoint yang mengubah contract harus terlihat pada diff OpenAPI/generated client.

---

# 14. API Response Convention

Successful response:

```json
{
  "data": {}
}
```

Collection:

```json
{
  "data": [],
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 120
  }
}
```

Error:

```json
{
  "error": {
    "code": "PROJECT_ACCESS_DENIED",
    "message": "You do not have access to this project.",
    "details": null,
    "requestId": "req_..."
  }
}
```

Frontend boleh menerjemahkan pesan error untuk UX.

Business code harus stabil.

---

# 15. Authentication

Authentication MVP:

```text
Email
+
Password
```

Wajib menyediakan:

- login;
- logout;
- email verification;
- forgot password;
- reset password;
- session/device revocation;
- account suspension.

Baseline implementation:

```text
Laravel Fortify
+
Laravel Sanctum
+
Laravel Session Authentication
```

### 15.1 Fortify Responsibility

Fortify digunakan sebagai **headless authentication backend** untuk fitur seperti:

- login flow;
- password reset;
- email verification;
- password confirmation bila dibutuhkan.

Fortify view harus dinonaktifkan karena UI authentication berada di Next.js.

Public self-registration **tidak otomatis diaktifkan**. Enable registration hanya jika `PRD.md` memang mengizinkan self-registration client.

### 15.2 Sanctum Responsibility

Sanctum digunakan untuk stateful SPA authentication antara Next.js dan Laravel.

Protected route menggunakan:

```text
auth:sanctum
```

### 15.3 Account Suspension

Authentication success saja tidak cukup.

User yang suspended/disabled harus gagal melewati authentication/authorization boundary sesuai canonical account status.

Jangan hanya menyembunyikan portal di frontend.

---

# 16. Session & Token Strategy

Untuk first-party browser portal, BDJG **tidak menggunakan custom JWT access token + refresh token** sebagai baseline.

Gunakan:

```text
Laravel stateful session cookie
+
Sanctum SPA authentication
```

Alasan:

- frontend dan API dirancang same-origin;
- mengurangi custom token rotation/revocation logic;
- memanfaatkan session security bawaan Laravel;
- lebih sederhana untuk browser authentication.

### 16.1 Session Storage

Baseline:

```text
SESSION_DRIVER=database
```

Database-backed session dipilih agar:

- session dapat direvoke server-side;
- active session dapat ditelusuri terhadap user;
- logout from other devices dapat diimplementasikan dengan jelas;
- tidak bergantung pada memory satu API instance.

Redis session dapat dievaluasi kemudian bila volume session membutuhkan throughput lebih tinggi, tetapi database session cukup untuk MVP.

### 16.2 Cookie Requirements

Session cookie production harus:

```text
Secure = true
HttpOnly = true
SameSite = Lax atau kebijakan yang sesuai arsitektur same-site
```

Cookie domain/path harus dikonfigurasi seminimal mungkin.

### 16.3 Session Rotation

Setelah login berhasil:

```text
regenerate session ID
```

untuk mencegah session fixation.

Logout harus menginvalidasi session aktif dan meregenerasi CSRF token sesuai mekanisme Laravel.

### 16.4 API Tokens

Sanctum personal access token **bukan mekanisme utama portal browser**.

Token baru boleh digunakan untuk future non-browser client/integration jika requirement muncul. Token tersebut harus:

- mempunyai ability/scope minimum;
- dapat direvoke;
- tidak disimpan plaintext setelah issuance bila framework tidak memerlukannya;
- tidak dimasukkan ke log.

---

# 17. CSRF Protection

Karena authentication menggunakan cookie/session, mutation endpoint harus mempunyai CSRF/request-forgery protection.

Flow SPA:

```text
Next.js browser
   │
   ├── GET /sanctum/csrf-cookie
   │
   └── mutation request dengan session cookie + XSRF token
```

Laravel/Sanctum menangani mekanisme `XSRF-TOKEN`/`X-XSRF-TOKEN` untuk SPA stateful authentication.

CSRF/request-forgery protection harus berlaku untuk state-changing browser request seperti:

```text
POST
PUT
PATCH
DELETE
```

### 17.1 Webhook Exception

Webhook provider tidak menggunakan browser CSRF mechanism.

Endpoint seperti:

```text
POST /api/v1/webhooks/duitku
```

boleh dikecualikan dari CSRF/request-forgery middleware **hanya** karena mempunyai provider verification sendiri.

Duitku webhook tetap wajib melewati:

```text
signature verification
+
merchant/order validation
+
idempotency
+
provider state reconciliation
```

CSRF exception **bukan** authorization bypass.

---

# 18. Password Security

Password wajib di-hash menggunakan Laravel Hash facade.

Baseline algorithm:

```text
Argon2id
```

Konfigurasi hashing harus menggunakan driver yang mendukung Argon2id dan cost yang sesuai environment production.

Password tidak pernah:

- disimpan plaintext;
- dikirim melalui log;
- masuk activity log;
- dikirim balik melalui API.

Gunakan:

```php
Hash::make($password)
Hash::check($plain, $hash)
```

atau API Laravel setara melalui authentication flow.

Jangan membuat hashing helper custom jika Laravel sudah menyediakan capability yang diperlukan.

Authentication secret dan cryptographic key harus berasal dari environment/secrets management.

---

# 19. Role Model

Canonical system role:

```text
OWNER
ADMIN
CLIENT
WORKER
```

Visitor bukan user role database.

Visitor merupakan unauthenticated actor.

---

# 20. Permission Model

BDJG tidak menggunakan role-only authorization.

Model:

```text
ROLE
+
PERMISSION
+
RESOURCE RELATIONSHIP
```

Contoh:

```text
ADMIN
+
project.update
+
admin mempunyai scope yang dibutuhkan
```

atau:

```text
WORKER
+
project.view_assigned
+
worker mempunyai active assignment
```

---

# 21. Authorization Flow

Setiap protected request mengikuti flow:

```text
Authentication (auth:sanctum)
      │
      ▼
Permission / Role Middleware bila relevan
      │
      ▼
Laravel Gate / Policy
      │
      ▼
Resource Relationship
      │
      ▼
Business Rule / State Rule
      │
      ▼
Action
```

Tidak setiap check harus menjadi middleware. Resource-specific authorization lebih tepat berada pada Policy/Gate atau Action yang memanggil authorization secara eksplisit.

Contoh:

Worker membuka project:

```text
Is authenticated?
      ↓ yes

Account active?
      ↓ yes

Role WORKER?
      ↓ yes

Has required permission?
      ↓ yes

Active ProjectAssignment exists?
      ↓ yes

Requested file visible to worker?
      ↓ yes

ALLOW
```

Jika salah satu gagal:

```text
DENY
```

### 21.1 Policy Rule

Gunakan Policy untuk authorization terhadap model/resource seperti:

```text
ProjectPolicy
FilePolicy
InvoicePolicy
PaymentPolicy
RevisionPolicy
PreviewPolicy
```

Gunakan Gate untuk ability global yang tidak terikat langsung ke satu resource, misalnya membuka dashboard finance jika memang dibutuhkan.

### 21.2 Do Not Trust Role Alone

`WORKER` bukan bukti bahwa user boleh membuka semua Project.

`ADMIN` bukan bukti bahwa user boleh melihat finance.

`CLIENT` bukan bukti bahwa user boleh membuka semua client-owned file.

---

# 22. Authorization Rule Examples

## Client

Client hanya boleh mengakses resource miliknya.

```text
project.clientId === currentUser.clientId
```

Client tidak boleh melihat:

- project client lain;
- internal note;
- worker cost;
- profit;
- internal working file;
- unreleased preview;
- unreleased final file.

---

## Worker

Worker hanya mempunyai project access jika terdapat:

```text
active ProjectAssignment
```

Assignment ke satu project tidak memberikan akses ke project lain.

Worker tidak boleh memperoleh finance access hanya karena mendapat project assignment.

---

## Admin

Admin mengikuti permission eksplisit.

Contoh:

```text
project.view
project.update
quotation.manage
invoice.manage
finance.view
finance.manage
worker.manage
cms.manage
final.release
```

---

## Owner

Owner mempunyai system-wide authority.

Namun critical action tetap harus masuk audit log.

---

# 23. Database

Database:

```text
MySQL 8.4 LTS
```

ORM:

```text
Laravel Eloquent ORM
```

Query Builder boleh digunakan untuk query yang lebih tepat/efisien dibanding Eloquent object hydration.

### 23.1 Source of Truth

MySQL adalah persistent relational source of truth untuk:

- identity/domain records;
- transaction state;
- project workflow;
- billing/payment metadata;
- authorization data;
- audit metadata;
- file metadata.

Redis bukan pengganti persistent database.

### 23.2 Database Access Rule

Frontend tidak boleh mempunyai credential database.

Semua mutation dan protected read melalui Laravel business/authorization boundary.

---

# 24. Database Rules

Gunakan:

```text
Storage Engine: InnoDB
Character Set : utf8mb4
Timezone      : UTC
```

Frontend menampilkan waktu menggunakan:

```text
Asia/Jakarta
```

Database tidak menyimpan string seperti:

```text
10:00 WIB
```

Gunakan timestamp.

---

# 25. Database Naming

Table:

```text
snake_case
plural
```

Contoh:

```text
users
projects
project_assignments
payment_transactions
revision_items
activity_logs
```

Column:

```text
snake_case
```

Eloquent Model menggunakan:

```text
StudlyCase / PascalCase class name
```

Contoh:

```text
ProjectAssignment
PaymentTransaction
RevisionItem
```

Jika table mengikuti konvensi Laravel, tidak perlu mendefinisikan `$table` secara manual.

Jangan membuat custom table mapping tanpa alasan.

---

# 26. ID Strategy

Setiap business entity utama menggunakan dua identifier.

```text
id
```

Internal relational primary key:

```text
BIGINT UNSIGNED AUTO_INCREMENT
```

dan:

```text
public_id
```

External identifier:

```text
ULID
```

Contoh:

```text
id:
152

public_id:
01K2KABF3XYF0...
```

URL harus menggunakan `public_id`.

```text
/projects/01K2KABF3XYF0...
```

bukan:

```text
/projects/152
```

Project juga mempunyai human-readable ID.

```text
BDJG-2026-0042
```

### 26.1 Laravel Implementation

Gunakan Laravel ULID utility/cast yang sesuai untuk menghasilkan `public_id` pada saat create.

`public_id` harus:

- unique indexed;
- immutable setelah entity dibuat;
- digunakan pada public route/API identifier;
- tidak menggantikan internal foreign key relational.

Route model binding harus diarahkan ke `public_id` untuk endpoint external bila sesuai.

---

# 27. Money

Semua monetary amount disimpan dalam integer Rupiah.

Contoh:

```text
Rp2.500.000
```

database:

```text
2500000
```

Gunakan:

```text
BIGINT
```

Jangan menggunakan:

```text
FLOAT
DOUBLE
```

untuk uang.

API boleh mengirim nilai monetary sebagai string untuk menghindari precision issue JavaScript:

```json
{
  "amount": "2500000",
  "currency": "IDR"
}
```

---

# 28. Canonical Status

Status di `PRD.md` dan `blueprint.md` adalah canonical.

Agent tidak boleh membuat status baru secara sembarangan.

Contoh payment:

```text
UNPAID
PENDING
PAID
FAILED
EXPIRED
REFUNDED
PARTIALLY_REFUNDED
```

Invoice:

```text
DRAFT
ISSUED
PARTIALLY_PAID
PAID
OVERDUE
VOID
REFUNDED
```

Status harus diwujudkan melalui PHP backed enum atau domain constant yang typed.

Recommended:

```php
enum PaymentStatus: string
{
    case Pending = 'PENDING';
    case Paid = 'PAID';
    // ...
}
```

Eloquent model harus menggunakan cast enum jika appropriate.

Jangan menggunakan arbitrary string yang tersebar di Controller/Job/Policy.

---

# 29. State Transition

Frontend tidak boleh bebas mengganti status.

Tidak boleh:

```text
PATCH project
{
  "status": "COMPLETED"
}
```

tanpa business action.

Gunakan action:

```text
ActivateProject
CompleteTask
ReleasePreview
SubmitRevision
ConfirmPayment
ReleaseFinalDelivery
```

Dalam Laravel, action ditempatkan pada domain yang sesuai, misalnya:

```text
App\Domains\Projects\Actions\ActivateProject
```

Action tersebut harus:

1. menerima typed/validated input;
2. mengotorisasi actor;
3. membaca current state;
4. memvalidasi allowed transition;
5. menjalankan mutation dalam transaction bila diperlukan;
6. menulis audit/event yang required;
7. dispatch follow-up job setelah state persistence aman.

Jangan membuat generic endpoint `setStatus` untuk state machine penting.

---

# 30. Critical Database Transactions

Database transaction wajib digunakan pada operasi seperti:

- quotation acceptance;
- invoice issuance;
- payment update;
- project activation;
- refund recording;
- final release;
- finance mutation;
- permission mutation.

Gunakan Laravel database transaction:

```php
DB::transaction(function () {
    // atomic business mutation
});
```

Jika satu bagian gagal, perubahan related state harus rollback.

### 30.1 External Call Rule

Jangan menahan database transaction terbuka selama network call panjang bila dapat dihindari.

Pattern yang lebih aman:

```text
persist local intent/state
→ commit transaction
→ call/queue external work sesuai consistency design
```

Untuk payment callback, locking/transaction boleh digunakan pada bagian state reconciliation agar callback paralel tidak menghasilkan double effect.

---

# 31. Delete Policy

Financial dan audit record tidak boleh hard-delete melalui aplikasi.

Contoh:

```text
Invoice
Payment
Refund
Expense
Audit Log
```

gunakan state:

```text
VOID
CANCELLED
REFUNDED
REJECTED
```

bukan menghapus histori.

Untuk content biasa dapat digunakan Laravel SoftDeletes:

```text
deleted_at
```

jika memang requirement membutuhkan reversible deletion.

Soft delete **bukan default otomatis untuk semua table**. Gunakan hanya bila lifecycle entity memang membutuhkannya.

---

# 32. Database Migration

Gunakan Laravel migrations.

Development:

```bash
php artisan make:migration ...
php artisan migrate
```

Fresh local/test environment dapat menggunakan:

```bash
php artisan migrate:fresh --seed
```

Production:

```bash
php artisan migrate --force
```

Agent **tidak boleh mengubah production schema secara manual sebagai pengganti migration**.

Migration production harus:

1. dibuat;
2. direview;
3. diuji pada database kompatibel MySQL;
4. committed;
5. dijalankan sebagai deployment step;
6. dipertimbangkan backward compatibility-nya jika deployment mempunyai overlap antara versi aplikasi.

### 32.1 Destructive Migration Rule

Drop/rename column besar atau perubahan tipe berisiko tidak boleh dilakukan tanpa review dampak data dan deployment.

Untuk perubahan berisiko, gunakan expand/migrate/contract pattern bila diperlukan.

---

# 33. Database Indexing

Minimal index:

- primary key;
- `public_id` unique index;
- foreign key;
- email unique/index sesuai model identity;
- project number;
- invoice number;
- merchant order ID unique;
- provider reference bila uniqueness provider memungkinkan;
- common status yang memang sering difilter;
- frequently filtered date;
- assignment relationship.

Index dibuat melalui Laravel migration/Schema Builder atau raw statement yang terdokumentasi bila fitur khusus MySQL dibutuhkan.

Query performance harus diukur sebelum membuat complex/composite index.

Jangan membuat index untuk setiap column tanpa bukti query pattern.

---

# 34. Payment Architecture

Payment provider MVP:

**Duitku**

Namun business layer tidak boleh bergantung langsung pada Duitku.

Gunakan abstraction:

```text
PaymentGateway
```

Contoh PHP contract:

```php
interface PaymentGateway
{
    public function createTransaction(CreatePaymentCommand $command): PaymentGatewayResult;

    public function checkTransaction(string $merchantOrderId): PaymentGatewayStatus;

    public function verifyCallback(array $payload, array $headers = []): VerifiedPaymentEvent;
}
```

Implementasi:

```text
DuitkuPaymentGateway
```

Binding interface → implementation dilakukan melalui Laravel service container/Service Provider.

### 34.1 HTTP Client

Duitku client menggunakan Laravel HTTP Client atau HTTP client abstraction yang disetujui.

External request wajib mempunyai:

- explicit timeout;
- connect timeout;
- bounded retry hanya untuk failure yang aman diretry;
- structured error mapping;
- sanitized logging.

Jangan retry operation yang dapat menciptakan duplicate transaction tanpa idempotency strategy.

---

# 35. Duitku Module

Struktur:

```text
app/Integrations/Duitku/
│
├── DuitkuClient.php
├── DuitkuPaymentGateway.php
├── DuitkuSignature.php
├── DuitkuStatusMapper.php
├── DuitkuConfig.php              # bila typed wrapper memang diperlukan
│
└── Data/
    ├── DuitkuCreatePaymentData.php
    ├── DuitkuCallbackData.php
    └── DuitkuStatusData.php
```

Business-facing contract dapat berada di domain Payment:

```text
app/Domains/Payments/Contracts/PaymentGateway.php
```

Duitku-specific terminology tidak boleh tersebar ke business module lain.

### 35.1 Boundary Example

Business layer mengetahui:

```text
PaymentStatus::Paid
```

bukan:

```text
resultCode === "00"
```

Mapping provider state → canonical BDJG state hanya terjadi di integration boundary.

---

# 36. Payment Flow

```text
Client
  │
  │ Click Pay
  ▼
BDJG API
  │
  │ Create Payment Attempt
  ▼
MySQL
  │
  ▼
Duitku API
  │
  ▼
paymentUrl
  │
  ▼
Client Browser
  │
  ▼
Duitku
  │
  ├────────────► Return URL
  │
  └────────────► Callback URL
                    │
                    ▼
                BDJG API
                    │
             Verify Signature
                    │
             Check Transaction
                    │
             Update Payment
                    │
              Update Invoice
                    │
                Audit Log
                    │
               Notification
```

---

# 37. Duitku Callback Is Payment Authority

Browser redirect **tidak boleh** langsung mengubah payment menjadi `PAID`.

Dokumentasi Duitku secara eksplisit memperingatkan agar `resultCode` pada redirect tidak digunakan untuk mengubah status transaksi karena URL browser dapat dimanipulasi.

Return URL hanya digunakan untuk UX:

```text
"Pembayaran sedang diperiksa..."
```

Frontend kemudian meminta status terbaru ke BDJG API.

---

# 38. Duitku Callback Verification

Current Duitku callback menggunakan **HMAC-SHA256**.

Konsep verifikasi:

```text
stringToSign =
merchantCode
+ amount
+ merchantOrderId

signature =
HMAC_SHA256(stringToSign, apiKey)
```

Implementasi PHP dapat menggunakan primitive cryptography standar seperti:

```php
hash_hmac('sha256', $stringToSign, $apiKey)
```

dan comparison yang aman seperti `hash_equals` untuk membandingkan signature expected vs received.

Dokumentasi Duitku versi 2.0 pada 2026 menandai mekanisme signature lama sebagai obsolete dan menggunakan HMAC-SHA256 pada callback.

Agent harus selalu mengikuti dokumentasi Duitku versi aktif jika formula resmi berubah.

### 38.1 Validation Order

Callback handler minimal:

```text
validate required fields
→ identify payment attempt
→ verify merchant/order consistency
→ verify signature
→ reconcile/check provider state bila policy membutuhkan
→ transaction + lock
→ map provider status
→ apply idempotent state transition
→ audit
→ dispatch notification
→ return HTTP 200 only after handler safely accepts event
```

Payload callback tidak boleh dipercaya hanya karena berasal dari endpoint yang tidak diketahui publik.

---

# 39. Duitku Transaction Check

Setelah callback tervalidasi, backend dapat melakukan:

```text
Check Transaction
```

untuk memastikan current provider state.

Duitku menyediakan transaction-status API dan mendokumentasikan bahwa status check dapat digunakan ketika callback diterima untuk memastikan perubahan status transaksi.

Namun API ini **tidak boleh dipoll terus menerus**.

Duitku memperingatkan terhadap automated repeated hitting pada transaction-status endpoint karena terdapat hit-rate limitation.

### 39.1 Laravel Integration Rule

Gunakan Duitku client dengan:

- explicit timeout;
- failure mapping;
- no secret logging;
- bounded retry;
- circuit/operational alert bila provider berulang kali gagal.

Jika transaction check gagal sementara callback signature valid, jangan asal menandai `PAID` atau `FAILED`. Simpan state yang aman dan buat reconciliation path sesuai business policy.

---

# 40. Payment Attempt

Satu invoice dapat mempunyai lebih dari satu payment attempt.

Contoh:

```text
Invoice
   │
   ├── Attempt 1 → EXPIRED
   │
   └── Attempt 2 → PAID
```

Jangan menggunakan invoice ID langsung sebagai payment transaction ID.

Setiap attempt mempunyai:

```text
merchant_order_id
```

unik.

---

# 41. Payment Tables

Contoh model:

```text
invoices

id
public_id
invoice_number
project_id
client_id
amount
paid_amount
status
issued_at
due_at
paid_at
created_at
updated_at
```

```text
payment_transactions

id
public_id
invoice_id

provider
merchant_order_id
provider_reference

amount
payment_method

status

payment_url
expires_at
paid_at

raw_provider_response
created_at
updated_at
```

```text
payment_status_histories

id
payment_transaction_id
from_status
to_status
source
provider_payload
created_at
```

---

# 42. Payment Idempotency

Duitku callback dapat dikirim ulang.

Dokumentasi Duitku menyatakan callback akan dikirim ulang ketika server merchant belum memberikan HTTP 200, dengan jumlah retry terbatas.

Karena itu handler harus idempotent.

Callback yang sama dua kali:

```text
PAID
PAID
```

tidak boleh menghasilkan:

```text
invoice paid dua kali
receipt dua kali
project activated dua kali
notification kritis dua kali
```

Gunakan:

- unique merchant order ID;
- provider reference;
- database transaction;
- `lockForUpdate()` bila diperlukan;
- current-state comparison;
- unique constraint untuk side effect yang harus exactly-once secara business;
- idempotent queued follow-up job.

### 42.1 Side Effect Rule

Payment state mutation dan enqueue side effect harus didesain supaya crash/retry tidak membuat duplicate business effect.

Jika event/job dapat terkirim ulang, consumer harus aman menerima event yang sama lebih dari sekali.

---

# 43. Payment Status Mapping

External provider state tidak langsung menjadi internal state.

```text
Duitku Status
      │
      ▼
DuitkuStatusMapper
      │
      ▼
BDJG PaymentStatus
```

Dengan demikian business system tetap independen dari provider.

---

# 44. File Architecture

File binary **tidak disimpan di MySQL**.

MySQL menyimpan metadata.

Binary disimpan di:

```text
Object Storage (S3-compatible)
```

Laravel mengakses storage melalui:

```text
Storage facade
+
Laravel Filesystem / Flysystem
```

Business code tidak boleh menggunakan vendor SDK secara acak di berbagai domain.

Untuk kebutuhan BDJG, baseline mempunyai **2 opsi storage environment**:

## 1. Local Storage (Development / Self-hosted)

```text
MinIO (S3-compatible)
```

Digunakan untuk:

- development environment;
- staging sederhana;
- deployment awal dengan biaya minimal jika memang dipilih;
- testing upload/download flow.

Keunggulan:

- self-hosted;
- mudah di-run via Docker;
- S3-compatible;
- dapat dipakai tanpa cloud vendor.

---

## 2. Cloud Storage (Production)

```text
S3-compatible Cloud Storage
```

Default preference saat ini:

- Cloudflare R2;
- provider S3-compatible lain bila dibutuhkan.

Digunakan untuk:

- production environment;
- scalable file storage;
- long-term media storage;
- high availability access.

Provider tidak dikunci sebagai domain decision.

---

Pilihan storage tidak boleh mengubah business domain.

Business layer mengenal:

```text
File/Object Storage abstraction
```

bukan vendor spesifik.

### 44.1 Laravel Disk Naming

Gunakan named disk yang jelas, misalnya:

```text
media
```

dengan configuration berbeda per environment.

Jangan hardcode bucket/endpoint di domain code.

---

# 45. File Metadata

Minimal:

```text
id
public_id
project_id

original_name
storage_key
mime_type
size

category
visibility
version

uploaded_by
released_by
released_at

created_at
updated_at
```

---

# 46. File Visibility

Canonical visibility:

```text
PUBLIC

INTERNAL

ASSIGNED_WORKERS

CLIENT_SHARED

FINAL_RELEASED
```

Default protected upload:

```text
INTERNAL
```

bukan public.

---

# 47. Protected File Access

Protected file tidak diberikan permanent public URL.

Gunakan short-lived temporary/signed URL.

Flow:

```text
User requests file
        │
        ▼
Laravel authorization check
        │
        ▼
Storage::temporaryUrl(...)
atau storage-specific presigned URL abstraction
        │
        ▼
Object Storage
```

Jika user tidak mempunyai permission:

```text
403
```

### 47.1 Authorization Before URL Generation

Signed URL tidak boleh dihasilkan hanya berdasarkan `storage_key` dari request.

Backend wajib resolve database File entity lalu memeriksa:

```text
actor
+
project relationship
+
file visibility
+
release state
```

baru menghasilkan URL.

### 47.2 Expiration

TTL signed URL harus pendek dan sesuai use case. Jangan membuat URL protected file berlaku berhari-hari tanpa business reason.

---

# 48. Large Upload Strategy

File besar tidak sebaiknya melewati Laravel PHP process secara penuh.

Gunakan:

```text
Direct-to-Object-Storage Upload
```

melalui:

```text
presigned / temporary upload URL
```

Laravel Filesystem mendukung temporary upload URL untuk S3-compatible flow yang mendukung fitur tersebut.

Flow:

```text
Browser
   │
   │ ask upload permission + metadata intent
   ▼
Laravel API
   │
   │ authorize + validate declared metadata
   ▼
Create pending upload record
   │
   ▼
Signed Upload URL
   │
   ▼
Browser ─────────► Object Storage
```

Setelah upload selesai:

```text
Browser
  │
  ▼
Laravel API
  │
  ▼
Verify/finalize object metadata
  │
  ├── persist file record/state
  └── dispatch processing job jika diperlukan
```

### 48.1 Pending Upload

Presigned URL issuance harus mempunyai server-side upload intent/pending record agar backend mengetahui:

- siapa uploader;
- project mana;
- expected category;
- expected size/type constraints;
- object key;
- expiration;
- finalization state.

Jangan menerima arbitrary object key dari browser saat finalize tanpa mencocokkannya dengan pending upload intent.

---

# 49. Upload Security

Backend harus memvalidasi sebelum presign/finalize:

- authenticated user;
- active account;
- project relationship;
- permission;
- file category;
- maximum size;
- allowed MIME;
- extension;
- declared visibility;
- object key ownership terhadap pending upload.

Storage key harus random/non-guessable.

Original filename tidak digunakan sebagai storage path.

### 49.1 Post-upload Verification

Client-declared MIME/size tidak boleh dianggap sepenuhnya trusted.

Saat finalize/processing, backend/worker harus memeriksa metadata object yang tersedia dan menolak mismatch yang berbahaya.

Untuk file yang memerlukan deeper inspection, lakukan di queue agar HTTP request tidak tertahan.

### 49.2 Public Files

File hanya menjadi public jika business rule memang menyatakan `PUBLIC`.

Upload default tidak pernah otomatis public.

---

# 50. Video Architecture

Original video:

```text
private object storage
```

Workflow:

```text
Upload Original
      │
      ▼
Finalize Upload
      │
      ▼
Dispatch ProcessVideo Job
      │
      ▼
Redis Queue
      │
      ▼
Laravel Horizon Worker
      │
      ▼
Laravel Process → FFmpeg
      │
      ├── preview.mp4
      │
      └── thumbnail
      │
      ▼
Object Storage
      │
      ▼
Persist derivative metadata/status
```

Preview digunakan client untuk review.

Original/master file tidak otomatis menjadi client-visible.

### 50.1 FFmpeg Rule

FFmpeg dipanggil dari queued job melalui Laravel Process atau wrapper tipis yang tetap mengeksekusi binary server-side.

Jangan menjalankan transcode berat di Controller HTTP.

Process harus mempunyai:

- timeout yang masuk akal;
- exit-code validation;
- sanitized command arguments;
- temporary working directory;
- cleanup `finally`/failure path;
- log tanpa membocorkan signed URL/secret;
- bounded retry berdasarkan jenis failure.

### 50.2 Input/Output Path

Jika FFmpeg membutuhkan local file, worker boleh download object ke temporary local path, process, upload derivative, lalu menghapus temporary file.

Jangan menganggap object storage mounted sebagai local filesystem kecuali deployment memang menjamin hal tersebut.

---

# 51. Video Feedback

Timestamp komentar disimpan sebagai numeric time.

Contoh:

```text
01:36
```

database:

```text
timestamp_ms = 96000
```

Minimal:

```text
preview_id
author_id
timestamp_ms
comment
status
created_at
resolved_at
```

---

# 52. Queue Architecture

Queue menggunakan:

```text
Laravel Queue
+
Redis
+
Laravel Horizon
```

Horizon digunakan untuk:

- menjalankan/manage Redis queue workers;
- mengatur worker configuration;
- melihat throughput;
- melihat runtime;
- melihat failed jobs;
- membantu operasi/retry yang terkontrol.

Queue logical names:

```text
notifications
email
media
files
maintenance
integrations
```

### 52.1 Queue Routing

Job class harus diarahkan ke queue yang sesuai secara eksplisit atau melalui centralized queue routing/configuration.

Contoh:

```text
ProcessVideo
→ media

SendInvoiceEmail
→ email

DuitkuReconciliation
→ integrations
```

### 52.2 Same Codebase

Semua queued job merupakan class dalam `apps/api`.

Tidak ada duplicate "worker business model".

---

# 53. Queue Rules

Setiap critical job harus:

- mempunyai unique/business identifier bila memungkinkan;
- idempotent;
- mempunyai bounded retry;
- mempunyai backoff;
- mempunyai timeout;
- mencatat final failure;
- aman terhadap worker crash/restart.

Laravel queue/Horizon mendukung attempts, timeout, backoff, failed job handling, dan worker supervision.

### 53.1 Do Not Serialize Excessive State

Queued job sebaiknya membawa identifier dan input minimum yang dibutuhkan, bukan snapshot object besar/sensitive yang mudah stale.

Contoh lebih baik:

```text
ProcessVideo(filePublicId)
```

lalu worker membaca current state dari database.

### 53.2 After-Commit Dispatch

Job yang bergantung pada data baru/updated harus didispatch setelah database transaction commit atau menggunakan mechanism after-commit yang sesuai.

Jangan biarkan worker membaca record sebelum transaksi pembuatannya committed.

### 53.3 Failed Jobs

Final failure harus menghasilkan:

```text
failed job record/operational visibility
+
structured log
+
alert bila critical
```

Jangan silent-fail.

---

# 54. Notification Architecture

Database merupakan source of truth notification.

Channel dapat berupa:

```text
IN_APP
EMAIL
WHATSAPP
```

MVP wajib:

```text
IN_APP
+
EMAIL
+
WHATSAPP untuk event yang ditentukan PRD/operasional
```

Laravel Notification/Mail capability boleh digunakan sebagai delivery mechanism, tetapi canonical notification record tetap harus sesuai domain BDJG bila PRD membutuhkan notification history/inbox.

### 54.1 Notification Pattern

Recommended flow:

```text
Business Action
→ commit business state
→ create/dispatch notification intent
→ queued delivery
→ channel adapter/provider
→ record delivery result
```

External provider failure tidak boleh menghapus notification record atau mengubah business truth.

---

# 55. Notification Failure

External notification failure tidak boleh membatalkan successful business transaction.

Contoh:

```text
Payment = PAID
Email = FAILED
```

hasil:

```text
Payment tetap PAID
Email masuk retry
```

Bukan rollback payment.

---

# 56. Email Provider

Email provider dibuat melalui abstraction/configuration boundary.

Business module memicu notification/mailable intent, bukan memanggil vendor SDK secara langsung.

Laravel Mail digunakan sebagai framework delivery abstraction.

Provider dapat menggunakan:

- SMTP;
- supported transactional mail transport/provider.

Business module tidak boleh mengetahui vendor-specific credential/endpoint.

Email yang tidak harus sinkron dikirim melalui queue.

---

# 57. WhatsApp

WhatsApp adalah **channel notifikasi wajib** dalam sistem BDJG karena komunikasi pelanggan dan customer operasional utama menggunakan WhatsApp sebagai media utama.

Gunakan contract:

```text
MessagingProvider
```

Contoh:

```php
interface MessagingProvider
{
    public function sendWhatsApp(WhatsAppMessage $message): DeliveryResult;
}
```

Sistem harus mendukung WhatsApp sebagai channel inti bersama email dan in-app notification.

Jika provider WhatsApp dipilih kemudian, hanya integration adapter/configuration yang berubah, tanpa mengubah business logic/domain.

### 57.1 Queue and Auditability

WhatsApp delivery dilakukan melalui queue untuk event yang tidak memerlukan response sinkron.

Minimal simpan status delivery yang dibutuhkan operasional:

```text
PENDING
SENT
FAILED
```

Provider payload sensitif tidak boleh disimpan mentah tanpa kebutuhan.

---

# 58. Audit Log

Critical business action harus masuk:

```text
activity_logs
```

Minimal data:

```text
id
actor_id
action

subject_type
subject_id

before_data
after_data

request_id
ip_address
user_agent

created_at
```

Audit log bersifat append-only dari sisi aplikasi.

---

# 59. Mandatory Audit Events

Minimal:

```text
login sensitive events
role changed
permission changed

quotation sent
quotation accepted
quotation revised

invoice issued
payment changed
payment reconciled
refund recorded

project activated
worker assigned

preview released
revision submitted

final delivery released

expense created/approved/rejected

CMS content published/unpublished
```

---

# 60. Security Headers

Production wajib menggunakan security headers pada layer aplikasi/reverse proxy secara terkontrol.

Minimum yang harus dievaluasi:

```text
Content-Security-Policy
X-Content-Type-Options
Referrer-Policy
Permissions-Policy
Strict-Transport-Security (HTTPS production)
frame-ancestors / anti-clickjacking policy
```

Jangan menambahkan header secara buta sehingga merusak Next.js asset, object-storage media, payment flow, atau third-party integrations.

### 60.1 Ownership

Header dapat diterapkan pada:

- reverse proxy/CDN;
- Next.js;
- Laravel response middleware;

namun harus ada **satu policy terdokumentasi** agar konfigurasi tidak bertentangan.

Laravel tidak membutuhkan package middleware besar hanya untuk header yang dapat diatur jelas pada reverse proxy/application middleware.

---

# 61. Rate Limiting

Rate limiting wajib untuk:

```text
login
forgot password
public inquiry
booking
email verification resend
sensitive API
payment creation
manual reconciliation
```

Gunakan Laravel RateLimiter / throttle middleware.

Fortify juga mempunyai authentication throttling; jangan membuat limiter kedua yang konflik tanpa alasan.

Rate-limit key harus mempertimbangkan use case, misalnya:

```text
IP
email + IP
user ID
client ID
```

### 61.1 Webhook Rule

Webhook provider menggunakan strategy berbeda agar legitimate callback tidak terblokir secara tidak tepat.

Jangan hanya menerapkan generic low-rate throttle ke Duitku callback.

Security utama webhook adalah:

```text
signature verification
+
idempotency
+
payload validation
```

Rate limit/WAF dapat menjadi lapisan tambahan jika tidak mengganggu legitimate retries.

---

# 62. CORS

Target production menggunakan same-origin:

```text
https://bdjg.id
```

Frontend:

```text
/
```

Business API:

```text
/api/v1
```

Authentication support route seperti `/sanctum/csrf-cookie` dan Fortify auth route juga diproxy ke Laravel pada origin yang sama.

Reverse proxy mengarahkan request Laravel ke backend API runtime.

Keuntungan:

- simpler session cookies;
- simpler CSRF/Sanctum stateful configuration;
- simpler CORS;
- single public origin.

Jika API menggunakan origin berbeda, CORS harus menggunakan explicit allowlist dan credential policy yang benar.

Jangan gunakan:

```text
Access-Control-Allow-Origin: *
```

untuk credentialed/authenticated endpoint.

---

# 63. Secrets

Secret hanya berada di:

```text
environment variable
atau
secret manager
```

Tidak boleh berada di:

- Git;
- source code;
- frontend bundle;
- screenshot;
- log.

Contoh backend secret/config:

```text
APP_KEY
DATABASE_URL atau DB_*
REDIS_URL / REDIS_*

DUITKU_MERCHANT_CODE
DUITKU_API_KEY

AWS_ACCESS_KEY_ID / S3_ACCESS_KEY
AWS_SECRET_ACCESS_KEY / S3_SECRET_KEY
```

Custom JWT secret **tidak diperlukan** untuk first-party browser authentication baseline.

### 63.1 APP_KEY

`APP_KEY` adalah sensitive Laravel application key dan wajib unik per environment.

Jangan reuse production `APP_KEY` di local/test jika tidak ada alasan khusus.

### 63.2 Frontend Variables

Environment variable yang diekspos ke browser melalui prefix/public mechanism Next.js harus dianggap **public**.

Secret provider tidak boleh berada pada variable browser-exposed.

---

# 64. Environment

Gunakan environment:

```text
development
test
staging
production
```

Duitku juga harus mempunyai environment terpisah:

```text
sandbox
production
```

Sandbox credential tidak boleh digunakan sebagai production credential.

---

# 65. Environment Variables

Minimum backend/frontend configuration harus didokumentasikan dalam `.env.example` tanpa secret nyata.

Contoh baseline:

```text
# Common URLs
APP_URL=https://bdjg.id
WEB_URL=https://bdjg.id
API_URL=https://bdjg.id/api/v1

# Laravel
APP_ENV=production
APP_KEY=...
APP_DEBUG=false
LOG_LEVEL=info

# Session / Sanctum
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=bdjg.id
SANCTUM_STATEFUL_DOMAINS=bdjg.id

# Database
DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

# Redis / Queue
REDIS_HOST=...
REDIS_PORT=6379
REDIS_PASSWORD=...
QUEUE_CONNECTION=redis
CACHE_STORE=redis

# Duitku
DUITKU_ENV=production
DUITKU_MERCHANT_CODE=...
DUITKU_API_KEY=...
DUITKU_CALLBACK_URL=https://bdjg.id/api/v1/webhooks/duitku
DUITKU_RETURN_URL=https://bdjg.id/client/payments/return

# Object Storage
FILESYSTEM_DISK=media
S3_ENDPOINT=...
S3_REGION=...
S3_BUCKET=...
S3_ACCESS_KEY=...
S3_SECRET_KEY=...
S3_PATH_STYLE=false

# Mail
MAIL_MAILER=...
MAIL_FROM_ADDRESS=...
MAIL_FROM_NAME=BDJG
MAIL_*=...

# WhatsApp
WHATSAPP_PROVIDER=...
WHATSAPP_*=...
```

Nama env final harus mengikuti config Laravel yang sebenarnya. Jika framework/package menggunakan canonical variable berbeda, pilih satu convention dan update `.env.example` + config; jangan mempertahankan dua nama untuk secret yang sama tanpa alasan.

Frontend environment tetap mempunyai variable sendiri di `apps/web` dan tidak boleh menerima backend secret.

---

# 66. Logging

Production log harus structured dan machine-readable.

Minimal setiap request mempunyai context:

```text
request_id
correlation_id bila ada
 timestamp
method
path
status_code
duration
user_id jika tersedia
```

Laravel logging channel dapat dikonfigurasi ke JSON/structured formatter yang sesuai deployment.

Jangan log:

- password;
- session cookie;
- CSRF token bila tidak diperlukan;
- API token;
- API key;
- presigned URL lengkap bila mengandung credential/signature;
- complete sensitive billing data;
- raw provider payload tanpa redaction policy.

### 66.1 Correlation

`request_id` harus ikut masuk ke:

- API error response;
- server log;
- audit log bila relevant;
- queued job metadata/context bila membantu tracing.

Jangan menjadikan request ID sebagai security credential.

---

# 67. Error Handling

Gunakan centralized Laravel exception handling melalui application exception configuration/handler.

Internal stack trace tidak boleh diberikan ke production client.

Client menerima stable error shape:

```json
{
  "error": {
    "code": "PAYMENT_NOT_FOUND",
    "message": "Payment was not found.",
    "details": null,
    "requestId": "..."
  }
}
```

Stack trace hanya masuk server log/error monitoring.

### 67.1 Exception Categories

Bedakan minimal:

```text
Validation error       → 422
Unauthenticated        → 401
Forbidden              → 403
Not found              → 404
Conflict/state invalid → 409 bila appropriate
Rate limited           → 429
Unexpected server      → 500
```

Business exception harus mempunyai stable machine-readable code.

Jangan mengirim raw exception message dari database/provider langsung ke client.

---

# 68. Validation

Semua request input divalidasi server-side.

Frontend validation hanya untuk UX.

Gunakan Laravel **Form Request** sebagai HTTP validation boundary untuk request non-trivial.

Contoh:

```text
CreateInquiryRequest
CreateQuotationRequest
AcceptQuotationRequest
CreateProjectRequest
AssignWorkerRequest
CreateRevisionRequest
```

Form Request dapat menggunakan `authorize()` bila authorization request-specific sederhana, tetapi complex resource authorization tetap harus konsisten dengan Policies/Actions.

Tidak boleh menerima Eloquent model mentah sebagai request body.

### 68.1 Validated Data Only

Action/service menerima data yang sudah tervalidasi/typed.

Jangan pass seluruh `$request->all()` ke create/update model.

Gunakan:

```text
$request->validated()
```

atau explicit data object/command bila flow kompleks.

### 68.2 Mass Assignment

Jangan menggunakan permissive mass assignment untuk field sensitif seperti:

```text
role
permission
payment status
invoice paid amount
file visibility
release state
```

yang seharusnya hanya diubah oleh business action tertentu.

---

# 69. Serialization

API response harus menggunakan explicit response shape.

Gunakan Laravel API Resources/Resource Collections atau explicit response mapper.

Sensitive field tidak boleh ikut response hanya karena tersedia di Eloquent model.

Contoh Worker response tidak boleh secara tidak sengaja menyertakan:

```text
project_profit
client_payment
internal_finance
```

### 69.1 Do Not Return Raw Model Blindly

Hindari menjadikan:

```php
return $project;
```

sebagai kebiasaan pada protected complex resource.

Gunakan Resource yang menentukan field dan relationship yang memang boleh keluar.

### 69.2 Prevent N+1

Resource serialization tidak boleh memicu query relationship tak terkontrol.

Controller/query/action harus eager-load relationship yang memang dibutuhkan response.

---

# 70. Finance Boundary

Finance permission dipisahkan dari general admin permission.

Contoh:

```text
project.manage
```

tidak otomatis berarti:

```text
finance.view
```

Worker tidak boleh menerima finance access melalui assignment.

---

# 71. Project File Boundary

Project access tidak otomatis memberi akses ke semua file.

Authorization:

```text
project access
+
file visibility
+
release state
```

Contoh client mempunyai project access tetapi:

```text
INTERNAL
```

file tetap tidak boleh dilihat.

---

# 72. Final Delivery

Final release harus melalui explicit action:

```text
ReleaseFinalDelivery
```

Action memeriksa:

```text
authorization
project state
file state
release policy
payment policy
```

Business policy mengenai kewajiban final payment sebelum final release harus mengikuti konfigurasi/decision di PRD dan blueprint.

Jangan hardcode rule yang belum final.

---

# 73. Caching

Redis dapat digunakan sebagai Laravel cache store untuk:

- public content cache;
- frequently-read configuration;
- rate-limit support;
- short-lived derived data;
- distributed lock bila benar-benar diperlukan.

Database tetap source of truth.

Financial state tidak boleh bergantung hanya pada cache.

### 73.1 Cache Rule

Setiap cache harus mempunyai:

```text
clear key ownership
TTL atau invalidation rule
fallback ke source of truth
```

Jangan cache authorization-sensitive response tanpa memasukkan identity/scope yang benar ke cache key.

---

# 74. Public Content Cache

Public content seperti:

```text
portfolio
services
public studio information
```

dapat dicache.

Ketika admin publish/update content:

```text
update DB
→ invalidate cache
→ refresh public content
```

---

# 75. Search / Filter

Admin/client list endpoint mendukung filter dari PRD.

Contoh:

```text
GET /projects?
status=...
&client=...
&admin=...
&from=...
&to=...
&page=1
&limit=20
```

Maximum page size harus dibatasi.

Contoh:

```text
100
```

Tidak boleh endpoint tanpa batas mengembalikan seluruh database.

---

# 76. Performance Principles

Prioritas performance BDJG:

1. optimized public media;
2. CDN/object storage;
3. database query efficiency;
4. pagination;
5. eager loading untuk mencegah N+1;
6. caching;
7. async heavy processing;
8. queue worker concurrency yang terukur;
9. PHP-FPM/web server tuning bila diperlukan;
10. horizontal scaling bila diperlukan.

Jangan melakukan premature microservice/Octane optimization.

### 76.1 Measure First

Gunakan data seperti:

```text
request latency
slow query
queue wait time
queue runtime
memory
CPU
storage throughput
FFmpeg runtime
```

sebelum membuat keputusan scaling.

---

# 77. Image Delivery

Public image harus mempunyai derivative sesuai kebutuhan.

Jangan mengirim:

```text
6000 × 4000 original image
```

untuk thumbnail kecil.

Gunakan responsive image dan media optimization.

---

# 78. Background Processing

Heavy operation tidak boleh menahan HTTP request terlalu lama.

Contoh:

```text
video transcode
thumbnail generation
large media analysis
mass email
WhatsApp delivery
retryable provider synchronization
cleanup
```

harus masuk Laravel Queue.

API pattern:

```text
accept request
→ validate + authorize
→ persist state
→ commit
→ dispatch job
→ respond
```

### 78.1 Process Execution

External executable seperti FFmpeg hanya dijalankan pada worker melalui Laravel Process/helper terkontrol.

Jangan membangun shell command dari untrusted input dengan string concatenation.

---

# 79. Health Check

API menyediakan:

```text
GET /api/health/live
GET /api/health/ready
```

Laravel juga mempunyai health route capability bawaan, tetapi BDJG tetap boleh mengekspos contract di atas agar deployment konsisten.

`live`:

```text
application process hidup
```

Tidak perlu memanggil seluruh external provider pada liveness check.

`ready`:

```text
database siap
critical Redis/queue dependency siap bila diperlukan untuk serving traffic
critical configuration tersedia
```

### 79.1 External Provider Health

Duitku, email, WhatsApp, dan object storage tidak semuanya harus dipanggil pada setiap readiness request.

Provider health dapat mempunyai operational probe/monitor terpisah agar health endpoint tidak memperparah outage eksternal.

---

# 80. Testing Strategy

Testing dibagi menjadi:

```text
Backend Unit
Backend Feature/API
Backend Integration
Authorization
Payment Integration
Queue/Job
Frontend Unit/Component
Browser E2E
PRD Acceptance
```

Laravel terminology:

- **Unit tests** untuk logic kecil yang tidak perlu boot full application;
- **Feature tests** untuk endpoint, authorization, database interaction, job dispatch, notification, dan flow yang membutuhkan Laravel application.

Mayoritas behavior kritis BDJG seharusnya mempunyai Feature test karena confidence-nya lebih tinggi untuk modular monolith.

---

# 81. Backend Unit Test

Backend menggunakan:

```text
Pest
+
PHPUnit-compatible Laravel testing stack
```

Unit test fokus pada logic yang dapat diuji tanpa full framework/database bila memungkinkan:

```text
business rule
status transition pure logic
payment status mapping
permission helper pure logic
quotation calculations
revision rules
finance calculations
value object
```

Jangan memaksa semua class menjadi mock-heavy unit test. Jika behavior bergantung pada policy, Eloquent, transaction, queue, atau HTTP boundary, Feature test biasanya lebih representatif.

---

# 82. Integration / Feature Test

Feature/integration test menggunakan test database.

Minimal meliputi:

```text
Eloquent
MySQL-compatible behavior
database constraints
transactions
route model binding
Form Request validation
Policy authorization
API Resource serialization
queue dispatch
filesystem fake/real integration sesuai level test
```

### 82.1 Database Engine

Unit/fast test boleh memakai substitute database hanya jika behavior tidak bergantung pada MySQL-specific semantics.

Critical transaction/index/constraint/payment tests harus diuji terhadap MySQL-compatible test environment agar perbedaan SQLite tidak menyembunyikan bug.

### 82.2 Laravel Test Utilities

Gunakan framework fakes secara tepat:

```text
Queue::fake()
Notification::fake()
Mail::fake()
Storage::fake()
Http::fake()
```

namun jangan fake bagian yang justru sedang diuji pada integration level.

---

# 83. Authorization Tests

Negative authorization testing **wajib**.

Contoh:

```text
Client A cannot open Project B
Worker A cannot open unassigned project
Worker cannot read invoice
Worker cannot read profit
Client cannot read internal file
Admin without finance permission cannot view profit
```

Tidak cukup hanya mengetes happy path.

---

# 84. Payment Tests

Minimum:

```text
create transaction
valid callback
invalid signature
merchant/order mismatch
duplicate callback
concurrent duplicate callback
expired payment
failed payment
pending → paid
callback mismatch
transaction-check failure
provider timeout
manual reconciliation authorization
invoice consistency
payment history creation
notification dispatched once secara business
```

Gunakan Laravel HTTP Client fake untuk deterministic application tests dan Duitku Sandbox untuk integration test yang benar-benar membutuhkan provider.

Jangan membuat seluruh CI bergantung pada availability Duitku Sandbox. Provider-live/sandbox test dapat dipisah dari deterministic PR checks bila perlu.

---

# 85. Frontend Testing

Frontend unit/component:

```text
Vitest
React Testing Library
```

Next.js menyediakan dokumentasi resmi untuk Vitest dan beberapa tool testing lain.

---

# 86. Browser E2E

Gunakan:

```text
Playwright
```

Next.js secara resmi menyediakan panduan penggunaan Playwright untuk E2E testing.

Critical scenario:

```text
Inquiry
→ Quotation
→ Client
→ Payment
→ Project
→ Preview
→ Revision
→ Final Delivery
```

---

# 87. PRD Acceptance Tests

Acceptance criteria dari `PRD.md` harus diterjemahkan menjadi test bila memungkinkan.

Contoh:

```text
AC-03
Client cannot access another client's project

→ automated authorization test
```

```text
AC-13
Payment notification consistency

→ integration/e2e test
```

---

# 88. Local Development

Recommended local development:

```text
Next.js
→ local Node.js process

Laravel API
→ local PHP process atau container

Laravel Horizon
→ local PHP process atau container

Infrastructure
→ Docker Compose
```

Docker Compose minimal menjalankan:

```text
MySQL 8.4
Redis
MinIO
```

MinIO digunakan sebagai local S3-compatible object storage.

FFmpeg harus tersedia pada environment yang menjalankan media queue worker.

Tidak wajib menjalankan seluruh frontend/backend di Docker saat development.

### 88.1 Suggested Local Process Layout

Terminal/process equivalent:

```text
1. Next.js dev server
2. Laravel API server
3. Laravel Horizon worker
4. Docker Compose infrastructure
```

Laravel `php artisan serve` cukup untuk local development; jangan menjadikannya production server.

---

# 89. Development Commands

Karena repository menggunakan dua ecosystem, root command boleh menjadi convenience wrapper tetapi command native harus tetap jelas.

## 89.1 First Install

Frontend:

```bash
pnpm install
```

Backend:

```bash
cd apps/api
composer install
```

Infrastructure:

```bash
docker compose up -d
```

Backend bootstrap:

```bash
cd apps/api
php artisan migrate
```

## 89.2 Development

Frontend:

```bash
pnpm --dir apps/web dev
```

Backend API:

```bash
cd apps/api
php artisan serve
```

Queue worker/Horizon:

```bash
cd apps/api
php artisan horizon
```

## 89.3 Quality

Backend:

```bash
cd apps/api
./vendor/bin/pint --test
php artisan test
```

Frontend:

```bash
pnpm --dir apps/web lint
pnpm --dir apps/web typecheck
pnpm --dir apps/web test
pnpm --dir apps/web build
```

E2E:

```bash
pnpm test:e2e
```

## 89.4 Root Convenience Scripts

Root `package.json` boleh menyediakan wrapper seperti:

```text
pnpm dev
pnpm dev:web
pnpm dev:api
pnpm dev:queue
pnpm lint
pnpm typecheck
pnpm test
pnpm test:e2e
pnpm build
```

Tetapi wrapper **tidak boleh menyembunyikan error** dari Composer/Artisan command yang dipanggil.

---

# 90. CI Pipeline

Setiap pull request minimal menjalankan:

```text
Checkout
↓
Install pnpm dependencies (frozen lockfile)
↓
Install Composer dependencies (locked, no interaction)
↓
Frontend lint
↓
Frontend typecheck
↓
Laravel Pint check
↓
Laravel configuration/bootstrap sanity
↓
Database migrations on test MySQL
↓
Backend unit + feature tests
↓
OpenAPI generation/validation
↓
Generated API contract drift check
↓
Frontend tests
↓
Build Next.js
```

Critical branch/release dapat menambahkan:

```text
Playwright E2E
Duitku sandbox integration suite
media/FFmpeg integration test
```

### 90.1 Composer Security / Validity

CI sebaiknya menjalankan Composer validation/audit sesuai policy repository.

Dependency vulnerability dengan dampak relevant tidak boleh diabaikan tanpa documented decision.

### 90.2 Migration Test

CI harus membuktikan migration dapat membangun database test dari clean state.

---

# 91. Production Deployment

Recommended provider-neutral architecture:

```text
                         Reverse Proxy / CDN
                                │
                  ┌─────────────┴─────────────┐
                  ▼                           ▼
             Next.js Web                 Laravel HTTP
                                            │
                              ┌─────────────┼──────────────┐
                              ▼             ▼              ▼
                            MySQL         Redis       Object Storage
                                            │
                                            ▼
                                         Horizon
                                            │
                                            ▼
                                      Queue Worker(s)
                                      (same API image/code)
                                            │
                                      ┌─────┼─────┐
                                      ▼     ▼     ▼
                                    Email FFmpeg Other Jobs

Laravel HTTP ───────────────────────────────► Duitku
```

Hosting vendor tidak dikunci dalam dokumen ini.

### 91.1 Laravel Runtime

Baseline server stack:

```text
Nginx + PHP-FPM
```

FrankenPHP dapat dipilih sebagai alternative runtime dengan deployment decision yang jelas.

Octane tidak wajib.

### 91.2 Queue Process Supervision

`php artisan horizon` harus dijalankan oleh process supervisor/container orchestrator yang dapat:

- restart process ketika crash;
- restart/deploy workers secara graceful;
- memastikan worker menggunakan code/config version yang benar.

### 91.3 Release Steps

Deployment Laravel minimal mempertimbangkan:

```text
install production dependencies
config/cache optimization sesuai deployment
run migrations
reload PHP/application process
terminate/restart Horizon gracefully
health check
```

---

# 92. Same-Origin Production

Preferred public architecture:

```text
https://bdjg.id/
```

Next.js.

```text
https://bdjg.id/api/v1/*
```

Laravel business API.

Laravel auth support routes seperti:

```text
/sanctum/csrf-cookie
/login
/logout
/forgot-password
/reset-password
```

juga diproxy ke Laravel pada origin yang sama.

Reverse proxy menentukan routing internal.

Keuntungannya:

- simpler cookies;
- simpler Sanctum stateful auth;
- simpler CSRF;
- simpler CORS;
- simpler client configuration;
- single public origin.

### 92.1 Cookie Scope

Session cookie tidak boleh dibuat lebih luas daripada yang diperlukan.

HTTPS wajib pada production authenticated traffic.

---

# 93. Database Backup

Production wajib mempunyai automated backup.

Minimal:

```text
daily database backup
+
retention
+
restore test
```

Backup dianggap valid hanya jika dapat direstore.

Object storage harus mempunyai lifecycle/backup policy sesuai kebutuhan file BDJG.

---

# 94. Dependency Rules

Agent tidak boleh menambah dependency tanpa alasan.

Sebelum menambahkan dependency:

1. apakah Laravel/PHP/Next.js/platform sudah menyediakan fitur tersebut?
2. apakah package aktif dipelihara?
3. apakah compatible dengan PHP 8.5 / Laravel 13 atau Node 24 / Next.js 16 sesuai sisi yang digunakan?
4. apakah security implications dipahami?
5. apakah package benar-benar dibutuhkan?
6. apakah license acceptable?
7. apakah package menambah runtime/operational complexity?

### 94.1 Preferred Laravel First-party Packages

Jika capability dibutuhkan dan first-party package sesuai, prefer package resmi Laravel seperti:

```text
Sanctum
Fortify
Horizon
Pint
Boost (development/AI assistance)
```

Tetapi jangan menginstall seluruh first-party package tanpa use case.

### 94.2 Third-party Package Rule

Third-party package harus mempunyai alasan eksplisit di PR/commit/implementation note bila menambah architectural behavior.

Contoh accepted baseline:

```text
Dedoc Scramble
→ OpenAPI generation
```

---

# 95. No Premature Microservices

MVP dilarang dipecah menjadi:

```text
auth-service
payment-service
project-service
file-service
notification-service
```

secara independen.

Tetap:

```text
Laravel modular monolith
```

Queue worker adalah process lain dari codebase Laravel yang sama, **bukan microservice**.

Jika nanti ada scaling issue, domain/module dapat diekstrak berdasarkan evidence dan ADR.

Extraction tidak boleh menjadi respons default terhadap "folder terlalu besar".

---

# 96. Future Go Boundary

Go boleh ditambahkan jika terdapat measured requirement.

Contoh future architecture:

```text
Laravel
   │
   ▼
message/job boundary yang terdokumentasi
   │
   ▼
Go Specialized Worker
```

Go tidak boleh:

- mengambil alih domain tanpa design decision;
- membuat duplicate business rules;
- bypass authorization;
- langsung menjadi second source of truth;
- membaca/mengubah database table arbitrary tanpa contract;
- menggantikan Horizon hanya karena dianggap "lebih cepat" tanpa profiling.

Jika specialized Go worker dibutuhkan, contract input/output, idempotency, failure semantics, dan ownership data harus terdokumentasi.

---

# 97. Code Quality

Backend PHP menggunakan:

```text
declare(strict_types=1);
```

pada application file baru yang sesuai project convention.

Gunakan:

- typed properties;
- parameter type;
- return type;
- backed enum;
- readonly/value object bila sesuai;
- constructor injection;
- small focused Action/Service;
- descriptive business naming.

Hindari:

```text
mixed
array tanpa shape pada boundary kompleks
magic string status
God Service
fat Controller
static global state custom
```

kecuali benar-benar diperlukan dan dijelaskan.

Formatting backend:

```text
Laravel Pint
```

Frontend TypeScript tetap menggunakan:

```text
strict mode
```

Hindari `any` kecuali benar-benar diperlukan dan dijelaskan.

Clarity lebih penting daripada clever abstraction.

### 97.1 Laravel Convention First

AI agent tidak boleh memindahkan semua konsep framework lain ke Laravel secara literal.

Contoh:

```text
Nest DTO          → Laravel Form Request / Data object
Nest Guard        → auth middleware + Gate/Policy
Nest Service      → Action/Service sesuai responsibility
Prisma schema     → Eloquent Models + Laravel Migrations
BullMQ processor  → Laravel Job + Horizon worker
```

Gunakan idiom Laravel, bukan "NestJS ditulis dengan PHP".

---

# 98. Business Naming

Gunakan terminology dari blueprint.

Jangan mengganti istilah secara acak.

Contoh canonical:

```text
Inquiry
Quotation
Invoice
Payment
Project
Project Assignment
Task
Preview
Revision
Final Delivery
```

Jangan membuat sinonim lain seperti:

```text
Order
Job
Ticket
Deal
```

kecuali memang didefinisikan.

---

# 99. Technical Definition of Done

Sebuah feature belum dianggap selesai hanya karena UI terlihat bekerja.

Feature selesai jika:

## Requirement

- requirement PRD terpenuhi;
- business rule blueprint terpenuhi.

## Backend

- input validated melalui appropriate boundary;
- authentication/authorization enforced;
- business logic berada pada Action/Service/domain yang tepat;
- errors handled;
- transaction boundary benar;
- queue side effect aman bila digunakan.

## Database

- migration tersedia;
- constraints benar;
- index relevan tersedia;
- model cast/relationship benar;
- destructive change direview.

## Security

- positive permission tested;
- negative permission tested;
- sensitive data tidak bocor;
- CSRF/session behavior benar untuk protected mutation;
- secret tidak masuk client/log.

## Audit

- required audit event tercatat.

## Frontend

- loading state;
- empty state;
- error state;
- responsive state;
- accessibility dasar;
- tidak bergantung pada hidden UI sebagai security.

## API Contract

- OpenAPI updated/generated;
- frontend generated contract tidak drift;
- response tidak membocorkan field internal.

## Testing

- required backend tests lolos;
- frontend tests relevan lolos;
- E2E/acceptance test sesuai criticality.

## Quality

Backend minimal:

```text
Laravel Pint check
php artisan test
OpenAPI generation/validation
```

Frontend minimal:

```text
lint
typecheck
test
build
```

Semua quality gate yang dikonfigurasi CI harus lolos.

---

# 100. AI Agent Implementation Rules

AI coding agent harus mengikuti aturan berikut.

### Before Coding

Agent harus menentukan:

```text
Requirement mana yang dikerjakan?
```

Lalu membaca:

```text
PRD section
blueprint section
DESIGN section jika UI
TECHNICAL section
AGENTS.md
```

Jika Laravel Boost tersedia di development environment, agent **boleh dan dianjurkan** menggunakan version-aware Laravel guidance dari Boost, tetapi Boost tidak menggantikan requirement repository ini.

### During Coding

Agent tidak boleh:

- invent business rule;
- memperluas P1/P2 ketika hanya diminta P0;
- bypass authorization;
- hardcode secret;
- mengubah canonical status tanpa alasan;
- hard-delete finance/audit history;
- menjadikan frontend sebagai payment authority;
- menjadikan redirect Duitku sebagai payment truth;
- membuat protected object menjadi public;
- menambahkan microservice tanpa keputusan arsitektur;
- mengubah Sanctum session auth menjadi JWT custom tanpa ADR;
- membuat `apps/worker` backend kedua;
- memindahkan heavy FFmpeg processing ke HTTP request;
- mengedit generated API client secara manual.

---

### Database Changes

Jika database berubah:

Agent wajib:

```text
update/create Eloquent Model bila relevan
create Laravel migration
update casts/relationships
update factory/seeder bila relevan
update tests
run migration on test database
```

Agent tidak boleh hanya mengubah model tanpa migration atau hanya mengubah database manual tanpa source-controlled migration.

---

### API Changes

Jika API berubah:

Agent wajib:

```text
update route/controller
update Form Request
update Policy/authorization bila relevan
update API Resource/response shape
update OpenAPI
regenerate/check frontend contract
update tests
```

---

### Permission Changes

Jika permission berubah:

Agent wajib membuat:

```text
positive test
+
negative test
```

Dan memeriksa minimal:

```text
role
permission
resource relationship
resource state
```

sesuai kebutuhan action.

---

### Payment Changes

Jika payment berubah:

Agent wajib memeriksa:

```text
signature verification
merchant/order consistency
idempotency
concurrency/locking
status mapping
history
audit
invoice consistency
notification duplication
provider timeout/failure
```

---

### File / Media Changes

Jika file/media berubah:

Agent wajib memeriksa:

```text
authorization
visibility
pending upload ownership
size/MIME/extension constraints
object key generation
signed URL TTL
finalization
queue processing
FFmpeg failure/cleanup
final release rule
```

---

### Queue Changes

Jika menambah job:

Agent wajib menentukan:

```text
queue name
idempotency key/business identity
retry count
backoff
timeout
failure handling
after-commit behavior
```

---

### Dependency Changes

Jika dependency baru ditambahkan:

Agent wajib menjelaskan:

```text
why native Laravel/Next capability is insufficient
package maintenance status
compatibility
security/license implication
```

---

### Generated Code

Generated file tidak diedit manual kecuali tooling memang mendefinisikannya sebagai source file.

Jika generated contract salah, perbaiki source contract/generator configuration lalu regenerate.

---

# 101. MVP Technical Priority

Recommended build order:

## Phase 0 — Foundation

```text
Monorepo
Next.js 16
Node.js 24 LTS
Laravel 13
PHP 8.5
Composer
MySQL 8.4
Redis
Laravel Sanctum
Laravel Fortify
Laravel Queue
Laravel Horizon
Object Storage / MinIO local
OpenAPI/Scramble
CI
```

---

## Phase 1 — Identity & Security

```text
Users
Authentication
Database sessions
Email verification
Password reset
Roles
Permissions
Policies
Audit
Rate limiting
Security headers
```

---

## Phase 2 — Sales

```text
Services
Packages
Inquiry
CRM
Client
Quotation
```

---

## Phase 3 — Billing

```text
Invoice
Duitku integration
Payment Attempt
Payment
Payment History
Webhook idempotency
Reconciliation
```

---

## Phase 4 — Production Core

```text
Project
Worker
Assignment
Task
Schedule
```

---

## Phase 5 — Media Workflow

```text
Files
Pending Upload
Direct Upload
Protected Download
Preview
Video
FFmpeg Job
Revision
Internal Review
Final Delivery
```

---

## Phase 6 — Portals

```text
Client Portal
Worker Portal
Admin Portal
Owner Dashboard
Finance
In-app Notifications
Email
WhatsApp
```

---

## Phase 7 — Public/CMS

```text
Portfolio CMS
Services CMS
Public Website Integration
SEO
Performance Optimization
Public media derivatives
```

---

## Phase 8 — Production Hardening

```text
Security review
Authorization audit
Session/CSRF review
Performance test
Queue/Horizon failure test
FFmpeg/media failure test
Backup + restore test
Duitku production verification
Error monitoring
Accessibility review
PRD acceptance tests
```

---

# 102. Decisions Locked by This Document

The following are considered **LOCKED** unless deliberately revised.

```text
Architecture:
Modular Monolith

Repository:
Monorepo

Frontend:
Next.js 16

Frontend Runtime:
Node.js 24 LTS

Frontend Language:
TypeScript

Backend:
Laravel 13

Backend Runtime:
PHP 8.5

Backend Package Manager:
Composer

Backend HTTP Baseline:
Nginx + PHP-FPM

Database:
MySQL 8.4 LTS

ORM:
Eloquent ORM

Browser Authentication:
Laravel Sanctum stateful session

Authentication Features:
Laravel Fortify + Laravel authentication services

Authorization:
Laravel Policy/Gate + Role + Permission + Resource Relationship

Session Baseline:
Database-backed session

Payment:
Duitku

API:
REST + OpenAPI 3.1

OpenAPI Baseline:
Dedoc Scramble 0.13.x compatible pinned release

Queue:
Laravel Queue + Redis

Queue Operations:
Laravel Horizon

File Architecture:
Laravel Filesystem + S3-compatible Object Storage

Media Processing:
FFmpeg in queued worker via controlled process execution

Payment Authority:
Server-side verified provider state

Worker Architecture:
Same Laravel codebase, separate Horizon process
```

### 102.1 Explicitly Not Locked as MVP Requirement

```text
Laravel Octane
FrankenPHP
Reverb
Kubernetes
Microservices
Go services
```

Semua hanya boleh ditambahkan berdasarkan requirement/evidence baru.

---

# 103. Decisions Intentionally Left Configurable

Tidak perlu diputuskan sebelum coding core dimulai:

```text
Hosting provider

Exact reverse proxy/CDN vendor

Object storage vendor

CDN vendor

Email transport/provider

WhatsApp provider

Error monitoring provider

Backup provider

Nginx+PHP-FPM vs approved FrankenPHP runtime
```

Semua provider integration harus menggunakan boundary/configuration agar provider dapat diganti tanpa mengubah domain logic.

Tidak semua hal configurable harus dibuat menjadi abstraction sejak hari pertama. Buat abstraction ketika terdapat external/provider boundary nyata; jangan membuat interface kosong hanya karena suatu hal "mungkin berubah".

---

# 104. Product Decisions Still Controlled by PRD

TECHNICAL.md tidak menentukan sendiri:

```text
final payment vs final release policy

revision entitlement per package

partial payment business policy

public availability details

worker expense business policy

specific notification templates
```

Jika belum final, agent harus membaca PRD/blueprint atau menunggu keputusan produk.

---

# 105. Final Technical Architecture

```text
                              BDJG
                                │
                       Reverse Proxy / CDN
                                │
                  ┌─────────────┴─────────────┐
                  │                           │
                  ▼                           ▼
            NEXT.JS 16                  LARAVEL 13
            React / TS                    PHP 8.5
            Node 24 LTS               REST / OpenAPI
                  │                           │
                  │                    Sanctum/Fortify
                  │                           │
                  └─────────────┬─────────────┘
                                │
                             ELOQUENT
                                │
                         MYSQL 8.4 LTS
                                │
               ┌────────────────┼─────────────────┐
               │                │                 │
               ▼                ▼                 ▼
             Redis        Object Storage        Duitku
               │           S3-compatible
               ▼
        Laravel Queue
               │
               ▼
            Horizon
               │
               ▼
      Laravel Queue Worker(s)
        same application code
               │
         ┌─────┼─────┐
         ▼     ▼     ▼
       Email FFmpeg Other Jobs
```

Authentication browser path:

```text
Next.js browser
   │
   ├── /sanctum/csrf-cookie
   │
   ├── Fortify auth endpoints
   │
   └── /api/v1 protected endpoints
          │
          ▼
   auth:sanctum
          │
          ▼
   Policies / Gates
          │
          ▼
   Actions / Services
```

File path:

```text
Browser
→ Laravel authorization/presign
→ direct upload to object storage
→ Laravel finalize
→ Queue/Horizon
→ FFmpeg derivative
→ protected signed access/release
```

---

# 106. Final Principle

BDJG harus dibangun sebagai:

> **Satu sistem yang modular, aman, mudah dipahami, mudah diuji, dan mudah dikembangkan—bukan kumpulan teknologi yang kompleks.**

Laravel menjadi authority business application.

Next.js menjadi presentation layer.

MySQL menjadi persistent relational source of truth.

Laravel Sanctum + Fortify menangani first-party browser authentication tanpa custom JWT complexity.

Policies/Gates + permission + resource relationship menangani authorization.

Redis + Laravel Queue + Horizon menangani asynchronous work.

Object storage menangani media binary.

FFmpeg berjalan pada queued worker, bukan HTTP request.

Duitku berada di provider integration boundary dan status pembayaran hanya berubah berdasarkan server-side verified provider state.

Go/Octane/microservices hanya diperkenalkan jika suatu hari terdapat workload yang **terukur** membutuhkan specialized architecture.

Dan untuk setiap feature:

```text
PRD
  ↓
Blueprint
  ↓
Design
  ↓
Technical
  ↓
Implementation
  ↓
Tests
```

Urutan tersebut tidak boleh dibalik.

AI agent harus selalu mengutamakan:

```text
correct business rule
→ authorization
→ data integrity
→ idempotency/security
→ clear Laravel architecture
→ tests
→ performance optimization berdasarkan measurement
```

bukan sekadar membuat endpoint/UI terlihat bekerja.
