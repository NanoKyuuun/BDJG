# BDJG — Implementation Plan

**Document:** `IMPLEMENTATION.md`  
**Project:** BDJG Creative Studio Website & Studio Management System  
**Status:** Execution Baseline  
**Primary Goal:** Dashboard-first operational MVP  
**Backend:** Laravel 13 / PHP 8.5  
**Frontend:** Next.js 16 / TypeScript  
**Database:** MySQL 8.4 LTS  
**Cache / Queue:** Redis + Laravel Horizon  
**Authentication:** Laravel Sanctum + Fortify  
**Authorization:** Spatie Laravel Permission + Laravel Policies/Gates  
**Payment:** Duitku Sandbox first, then production credentials  
**Last baseline review:** 18 August 2026

---

# 1. Purpose

Dokumen ini menjelaskan **urutan implementasi nyata** BDJG dari repository kosong sampai sistem dashboard-first dapat diuji end-to-end.

Dokumen ini dibuat untuk:

- developer manusia;
- AI coding agent;
- reviewer;
- QA;
- future maintainer.

`IMPLEMENTATION.md` tidak mengganti requirement produk atau technical architecture. Dokumen ini menjawab:

> **Apa yang harus dibangun lebih dahulu, dependency-nya apa, test apa yang wajib ada, dan kapan suatu phase dianggap selesai?**

Target awal bukan landing page.

Target awal adalah **Operational Dashboard MVP** yang membuktikan alur bisnis inti:

```text
Authentication
→ Role / Permission
→ Inquiry
→ Client
→ Quotation
→ Client Acceptance
→ Invoice
→ Duitku Sandbox
→ Verified Callback
→ Project Activation
→ Worker Assignment
→ Task / Progress
→ Admin Dashboard
→ Worker Dashboard
→ Client Dashboard
→ Audit Trail
```

Landing page, cinematic public experience, portfolio publication, dan CMS public-facing ditunda sampai operational core stabil.

---

# 2. Source-of-Truth Hierarchy

Agent wajib membaca dokumen dengan urutan tanggung jawab berikut:

```text
PRD.md
↓
blueprint.md
↓
DESIGN.md
↓
TECHNICAL.md
↓
IMPLEMENTATION.md
↓
AGENTS.md
```

Tanggung jawab:

| Dokumen | Authority |
|---|---|
| `PRD.md` | Product requirements dan acceptance criteria |
| `blueprint.md` | Business flow, role, status, domain relationship |
| `DESIGN.md` | UI/UX dan design language |
| `TECHNICAL.md` | Architecture dan technical rules |
| `IMPLEMENTATION.md` | Build order, checkpoints, implementation sequence |
| `AGENTS.md` | Cara AI agent bekerja di repository |

Jika terdapat konflik:

1. Requirement produk tidak boleh diubah oleh implementation plan.
2. Business rule tidak boleh diinvent oleh agent.
3. Technical architecture mengikuti `TECHNICAL.md`.
4. `IMPLEMENTATION.md` mengontrol **urutan implementasi**, bukan mengubah requirement.
5. Keputusan terbaru yang secara eksplisit dikunci di dokumen ini harus disinkronkan kembali ke dokumen lama bila terdapat build-order conflict.

---

# 3. Decisions Locked for This Implementation

Keputusan berikut dianggap **LOCKED** untuk dashboard-first MVP.

## 3.1 Dashboard First

Implementasi dimulai dari portal/dashboard dan business workflow.

Public landing page tidak menjadi dependency MVP pertama.

```text
FIRST
/admin/*
/worker/*
/client/*

LATER
/
/works
/services
/about
/studio
/contact
/book
```

Catatan:

`Book a Project` public form tetap merupakan requirement produk P0, tetapi untuk tahap dashboard-first awal dapat diuji melalui internal/dev entry point atau API fixture sampai public website phase dibangun.

Tidak boleh menganggap public landing selesai hanya karena backend inquiry sudah ada.

---

## 3.2 No Dummy Dashboard as Final Implementation

Dashboard tidak boleh dibangun sebagai kumpulan static card dengan angka hardcoded.

Contoh yang dilarang:

```ts
const activeProjects = 12;
const unpaidInvoices = 3;
```

Dashboard metric harus berasal dari database melalui query backend yang nyata.

Mock data hanya boleh digunakan dalam isolated component test / Storybook-like development context jika project menggunakannya.

---

## 3.3 Real Duitku Sandbox from MVP

Tidak menggunakan fake payment gateway sebagai runtime MVP.

Runtime development menggunakan:

```text
Duitku Sandbox
```

Automated test boleh menggunakan:

```text
Laravel Http::fake()
```

untuk membuat test deterministik dan tidak bergantung internet.

Pembayaran yang benar-benar diuji manual/E2E harus melewati sandbox Duitku.

---

## 3.4 Payment Authority

Return URL tidak boleh mengubah payment state.

Canonical payment update:

```text
Duitku callback
→ validate payload
→ identify payment transaction
→ verify signature
→ transaction status check
→ map provider state
→ database transaction
→ payment update
→ invoice update
→ project activation evaluation
→ audit
```

Browser redirect hanya:

```text
UX feedback
→ "Pembayaran sedang diperiksa"
→ refetch current status from BDJG API
```

---

## 3.5 Authorization Architecture

Gunakan:

```text
Spatie Laravel Permission
+
Laravel Gate
+
Laravel Policy
+
Resource relationship
+
Resource state
```

Jangan membuat custom RBAC engine dari nol.

---

## 3.6 Canonical Roles

Role MVP:

```text
OWNER
ADMIN
WORKER
CLIENT
```

Profesi worker seperti:

```text
Editor
Photographer
Videographer
Colorist
Motion Designer
```

adalah attribute/profile, **bukan security role**.

---

## 3.7 Single Guard

Gunakan:

```text
guard_name = web
```

Jangan membuat guard terpisah:

```text
owner
admin
worker
client
```

Semua first-party browser authentication menggunakan Sanctum stateful session dan Laravel `web` guard.

---

## 3.8 Owner as Super Admin

Owner menggunakan global authorization interception:

```text
Gate::before
```

Owner tetap harus mempunyai auditable action.

`Gate::before` tidak berarti audit boleh dilewati.

---

## 3.9 Spatie Scope

Spatie hanya menangani:

```text
Role
Permission
Role ↔ Permission
User ↔ Role
optional direct user permission if explicitly needed
```

Spatie tidak menangani:

```text
client owns project
worker assigned to project
quotation belongs to client
invoice belongs to client
project state
task assignment
```

Hal tersebut ditangani model relationship + Policy.

---

## 3.10 Modular Monolith

Tetap satu Laravel application.

Tidak membuat:

```text
payment-service
auth-service
project-service
worker-service
```

sebagai network microservice.

---

# 4. Operational MVP Definition

Dashboard-first MVP dianggap berhasil ketika alur berikut dapat dijalankan tanpa spreadsheet sebagai source of truth:

```text
1. Owner/Admin login.
2. Admin membuat atau menerima Inquiry.
3. Inquiry dikualifikasi.
4. Client record tersedia.
5. Admin membuat Quotation V1.
6. Quotation dikirim.
7. Client login dan melihat quotation miliknya.
8. Client menerima quotation.
9. Client account aktif/invited sesuai flow.
10. DP Invoice diterbitkan.
11. Client memulai pembayaran Duitku Sandbox.
12. Duitku mengirim callback ke BDJG.
13. Backend memverifikasi callback.
14. Backend melakukan transaction status check.
15. Payment menjadi canonical PAID bila provider mengonfirmasi.
16. Invoice dihitung ulang.
17. Activation rule dievaluasi.
18. Project dibuat/diaktifkan.
19. Admin assign Worker.
20. Worker login.
21. Worker hanya melihat project assigned.
22. Worker melihat dan mengubah task yang diizinkan.
23. Admin melihat progress.
24. Client melihat project miliknya.
25. Client tidak melihat internal finance / internal notes.
26. Semua critical state change tercatat di audit log.
```

---

# 5. MVP Boundaries

## 5.1 Included in Dashboard-First MVP

```text
Repository foundation
Local infrastructure
Authentication
Email verification architecture
Password reset
User status
Spatie roles & permissions
Policies / Gates
Owner override
Audit foundation

Internal service/package master data
Inquiry
Client directory
Quotation + version history
Quotation client decision
Client invitation/activation

Invoice
Payment transaction
Payment history
Duitku Sandbox create transaction
Duitku callback
Duitku transaction check
Payment idempotency
Invoice state update

Project activation
Project list/detail
Project stage
Worker profile
Assignment
Tasks
Basic schedule

Admin portal
Owner capability
Worker portal
Client portal
Dashboard summaries
Search/filter basic core datasets

Automated tests
Playwright golden path
CI baseline
```

---

## 5.2 Deferred from Dashboard-First MVP

Deferred tidak berarti dihapus dari product MVP.

```text
Full landing page
Public works/portfolio experience
Public service pages
Advanced CMS
FFmpeg processing
large video upload
timestamp preview comments
formal revision rounds
final media release
photo selection
worker expense claim
advanced finance reporting
WhatsApp integration
advanced notifications
advanced analytics
production workload forecasting
```

Media/revision/final delivery akan menjadi milestone berikutnya setelah operational dashboard MVP stabil.

---

# 6. Release Milestones

Implementasi dibagi menjadi empat release slice.

## Release A — Operational Dashboard MVP

```text
Foundation
Identity
RBAC
Admin/Owner portal
Sales
Quotation
Client portal commercial actions
Invoice
Duitku Sandbox
Project activation
Worker assignment
Task
Worker portal
Client project view
Audit
Core E2E tests
```

## Release B — Media Workflow MVP

```text
Object storage
direct upload
protected file access
file visibility
preview
FFmpeg
internal review
client preview release
timestamp feedback
revision rounds
final delivery
```

## Release C — Operations Completion

```text
notifications
email
WhatsApp
schedule expansion
finance expansion
worker expense
reconciliation UI
operational alerts
```

## Release D — Public Experience

```text
landing page
works
work detail
services
book project public UI
CMS
SEO
cinematic design
public performance hardening
```

---

# 7. Repository Target Structure

Repository:

```text
bdjg/
│
├── apps/
│   ├── web/
│   │   └── Next.js
│   │
│   └── api/
│       └── Laravel
│
├── packages/
│   ├── api-client/
│   ├── ui/
│   └── config/
│
├── docs/
│   ├── PRD.md
│   ├── blueprint.md
│   ├── DESIGN.md
│   ├── TECHNICAL.md
│   └── IMPLEMENTATION.md
│
├── AGENTS.md
├── package.json
├── pnpm-workspace.yaml
├── pnpm-lock.yaml
├── docker-compose.yml
├── .env.example
└── README.md
```

Tidak membuat:

```text
apps/worker
packages/database
```

Queue worker menggunakan codebase:

```text
apps/api
```

melalui:

```bash
php artisan horizon
```

---

# 8. Backend Domain Structure

Target domain awal:

```text
apps/api/app/Domains/
├── Auth/
├── Users/
├── Access/
├── Catalog/
├── CRM/
├── Clients/
├── Quotations/
├── Billing/
├── Payments/
├── Projects/
├── Assignments/
├── Tasks/
├── Schedules/
└── Audit/
```

Integration:

```text
apps/api/app/Integrations/
└── Duitku/
```

Jangan membuat semua subfolder kosong.

Buat class/folder hanya ketika benar-benar dipakai.

---

# 9. Phase Execution Rule

AI agent harus mengerjakan phase secara berurutan.

Untuk setiap phase:

```text
Read requirements
→ identify affected domain
→ migration/schema
→ enum/value object
→ model/relationship
→ action/service
→ policy/permission
→ validation
→ controller/API resource
→ OpenAPI
→ generated frontend client
→ UI
→ automated tests
→ manual verification
→ phase gate
```

Agent tidak boleh melompat ke UI berikutnya bila backend contract phase saat ini belum stabil.

---

# 10. Phase 0 — Repository Bootstrap

## Goal

Membuat repository dapat dijalankan developer baru dengan satu prosedur yang terdokumentasi.

## Backend

Create:

```text
apps/api
Laravel 13
PHP 8.5
```

Install baseline:

```text
laravel/sanctum
laravel/fortify
laravel/horizon
spatie/laravel-permission
dedoc/scramble
pestphp/pest
```

Gunakan package version yang kompatibel dengan Laravel 13 dan lock melalui `composer.lock`.

## Frontend

Create:

```text
apps/web
Next.js 16
TypeScript
App Router
```

Workspace:

```text
pnpm
```

## Local infrastructure

Minimum:

```text
MySQL 8.4
Redis
Mail testing service if selected
```

Object storage boleh disiapkan sejak awal tetapi belum menjadi blocker Release A.

## Deliverables

```text
docker-compose.yml
.env.example
README local setup
apps/web boot
apps/api boot
MySQL connectivity
Redis connectivity
health endpoint
```

## Required health endpoints

```text
GET /api/health
```

Response minimum:

```json
{
  "status": "ok"
}
```

Optional protected/deeper health may verify database and Redis.

## Phase 0 Tests

- Laravel test runner works.
- Frontend test runner works.
- API boots.
- Web boots.
- MySQL migration works.
- Redis connection works.
- CI can install Composer and pnpm dependencies.

## Exit Gate

Tidak boleh lanjut bila fresh clone tidak dapat dijalankan berdasarkan README.

---

# 11. Phase 1 — Authentication Foundation

## Goal

Mendapatkan first-party secure session authentication antara Next.js dan Laravel.

## Use

```text
Laravel Fortify
+
Laravel Sanctum
+
web guard
+
database session
```

## Required flows

```text
GET /sanctum/csrf-cookie
POST /login
POST /logout
GET /api/v1/me
POST /forgot-password
POST /reset-password
email verification flow
```

Exact Fortify route behavior harus mengikuti configuration resmi Laravel.

## User States

Canonical application state:

```text
INVITED
ACTIVE
SUSPENDED
DISABLED
```

Do not confuse user lifecycle with worker lifecycle.

## Rules

### ACTIVE

May authenticate when credentials valid.

### INVITED

Can complete invitation/activation flow.

### SUSPENDED

Authentication/resource access must be denied or invalidated according to implementation.

### DISABLED

No normal portal access.

## User Minimum Fields

```text
id
public_id
name
email
email_verified_at
password
phone
status
last_login_at
remember_token
created_at
updated_at
```

Avoid storing security role as a free-text `users.role` field if Spatie role assignment is authoritative.

## Authentication Tests

Required:

```text
UserCanLogin
InvalidCredentialsRejected
SuspendedUserCannotUsePortal
DisabledUserCannotUsePortal
UserCanLogout
UnauthenticatedApiRequestRejected
CsrfProtectedStateChangingRequest
PasswordResetFlow
EmailVerificationBehavior
```

## Frontend

Routes:

```text
/login
/forgot-password
/reset-password
/verify-email
```

Create authenticated root layouts:

```text
/admin
/worker
/client
```

Frontend route guard is UX only.

Backend remains security authority.

## Exit Gate

- Session persists correctly.
- Logout invalidates access.
- CSRF behavior works.
- `/api/v1/me` returns canonical current user representation.
- No JWT access/refresh architecture exists for first-party portal.

---

# 12. Phase 2 — Spatie RBAC + Laravel Policies

## Goal

Create explicit permission system before business data becomes accessible.

## Install / Configure

Package:

```text
spatie/laravel-permission
```

Use:

```text
guard_name = web
```

Add:

```php
Spatie\Permission\Traits\HasRoles
```

to User model.

Do not enable Spatie Teams for MVP.

---

# 13. Canonical Role Seeder

Seed roles idempotently:

```text
OWNER
ADMIN
WORKER
CLIENT
```

Seeder must be rerunnable.

Clear/refresh Spatie permission cache correctly during seed process.

---

# 14. Permission Naming Convention

Use:

```text
resource.action
```

Use predictable lowercase names.

Baseline:

```text
dashboard.view

clients.viewAny
clients.view
clients.create
clients.update
clients.archive

inquiries.viewAny
inquiries.view
inquiries.create
inquiries.update
inquiries.assign
inquiries.changeStatus
inquiries.convertToQuotation

quotations.viewAny
quotations.view
quotations.create
quotations.update
quotations.send
quotations.cancel
quotations.approveDiscount
quotations.viewHistory

invoices.viewAny
invoices.view
invoices.create
invoices.issue
invoices.void

payments.viewAny
payments.view
payments.createTransaction
payments.reconcile
payments.refund

projects.viewAny
projects.view
projects.create
projects.update
projects.changeStatus
projects.assignWorker

tasks.viewAny
tasks.view
tasks.create
tasks.update
tasks.assign

schedules.viewAny
schedules.view
schedules.create
schedules.update

workers.viewAny
workers.view
workers.create
workers.update
workers.assign

users.viewAny
users.view
users.create
users.update
users.manageRoles

audit.viewAny
audit.view

finance.view
finance.viewProfit

settings.view
settings.update
integrations.manage
```

Permission list may grow when later modules are implemented.

Do not create permission names for unimplemented feature unless required by a known future migration strategy.

---

# 15. Baseline Role Permission Strategy

## OWNER

Owner is Super Admin through `Gate::before`.

Owner does not need every permission manually assigned to obtain access, although a visible role/permission catalog may still show effective capabilities.

## ADMIN

Admin receives operational permissions according to seed baseline.

Sensitive permissions can initially be Owner-only:

```text
users.manageRoles
integrations.manage
settings.update
finance.viewProfit
payments.refund
```

unless PRD/owner decision says otherwise.

## CLIENT

Client permission only permits client-facing abilities.

Relationship policy must still validate ownership.

## WORKER

Worker permission only permits worker-facing abilities.

Assignment policy must still validate active project assignment.

---

# 16. Owner Gate

Implement conceptually:

```php
Gate::before(function (User $user, string $ability) {
    return $user->hasRole('OWNER') ? true : null;
});
```

Do not return `false` for non-owner in `Gate::before`; allow normal authorization flow to continue.

Owner override action must still create audit records when action is audit-sensitive.

---

# 17. Policy Rule

Every protected business resource requires policy logic where resource relationship matters.

Initial policies:

```text
ClientPolicy
InquiryPolicy
QuotationPolicy
InvoicePolicy
PaymentTransactionPolicy
ProjectPolicy
WorkerPolicy
ProjectAssignmentPolicy
TaskPolicy
SchedulePolicy
AuditLogPolicy
```

Example Project access:

```text
OWNER
→ allow through Gate::before

ADMIN
→ requires permission

CLIENT
→ requires client-facing permission
→ project.client_id must belong to authenticated client's identity

WORKER
→ requires worker-facing permission
→ active ProjectAssignment must exist

otherwise
→ deny
```

---

# 18. Authorization Test Gate

Before proceeding, tests must prove:

```text
Client A cannot access Client B resource
Worker A cannot access unassigned Project B
Worker cannot access invoice/payment
Client cannot access internal finance
Admin without permission cannot perform restricted action
Owner can perform authorized override
Role/permission changes are audited
Suspended user cannot bypass resource policies
```

No dashboard business feature should proceed before these tests have a reusable pattern.

---

# 19. Phase 3 — Portal Shells

## Goal

Build navigation/layout early without fake business content.

## Admin Route Baseline

```text
/admin/dashboard

/admin/sales/inquiries
/admin/sales/quotations
/admin/sales/clients

/admin/projects
/admin/projects/board
/admin/projects/calendar

/admin/team/workers
/admin/team/assignments

/admin/finance/invoices
/admin/finance/payments

/admin/system/users
/admin/system/roles
/admin/system/activity
```

Items not implemented yet:

- hide them; or
- mark clearly as not available only in internal development mode.

Do not create a clickable dead production navigation.

## Worker Route Baseline

```text
/worker/dashboard
/worker/projects
/worker/tasks
/worker/schedule
/worker/profile
```

## Client Route Baseline

```text
/client/dashboard
/client/projects
/client/quotations
/client/invoices
/client/profile
```

## Permission-Aware Navigation

Frontend navigation may use effective permissions from `/api/v1/me`.

But:

```text
hidden menu != authorization
```

Backend still checks Policy/Gate.

## Portal Loading States

Every portal screen must support:

```text
loading
empty
error
forbidden
normal
```

---

# 20. Phase 4 — Catalog Master Data for Internal Operations

Landing page is deferred, but quotation/inquiry needs service context.

Implement minimal internal catalog:

```text
Service
Package
AddOn
```

## Service

Minimum:

```text
id
public_id
name
slug
description_internal
status
created_at
updated_at
```

## Package

Minimum:

```text
id
service_id
name
description_internal
base_price
currency
default_dp_type
default_dp_value
status
created_at
updated_at
```

## AddOn

Minimum:

```text
id
service_id nullable
name
description_internal
price
status
created_at
updated_at
```

Public publishing fields may be added in later CMS phase.

Historical quotation must snapshot price/name and must not depend on current catalog values.

## Admin UI

```text
/admin/catalog/services
/admin/catalog/packages
/admin/catalog/add-ons
```

These can be placed under a temporary internal configuration group until public CMS is implemented.

## Tests

- Admin with permission can CRUD.
- Unauthorized Admin cannot.
- Worker/Client cannot access internal catalog admin endpoints.
- Archived/inactive package cannot be selected for new quotation unless explicit behavior exists.

---

# 21. Phase 5 — Client Domain

## Goal

Establish client identity separate from authentication account.

A client business entity and login User are related but not conceptually identical.

Recommended conceptual model:

```text
clients
client_users
users
```

For MVP, one main client user may be enough, but schema should not unnecessarily prevent a future company having multiple users if PRD later requires it.

## Client Fields

Minimum:

```text
id
public_id
display_name
company_or_institution
email
phone
billing_name
billing_email
billing_phone
billing_address
status
created_at
updated_at
```

If implementation keeps authentication email only on `users`, make clear which field is canonical for billing/business contact.

## Client UI

Admin:

```text
/admin/sales/clients
/admin/sales/clients/[id]
```

Client:

```text
/client/profile
```

## Required behavior

- Owner/Admin can create client according to permission.
- Inquiry conversion can find/create client safely.
- Duplicate email handling is explicit.
- Client can only access own profile.
- No client can enumerate another client.

---

# 22. Phase 6 — Inquiry / CRM

## Canonical Status

```text
NEW
CONTACTED
QUALIFIED
QUOTATION
WON
LOST
```

Use PHP backed enum.

## Inquiry Data

Minimum from PRD:

```text
public_id
client_name
email
phone
company_or_institution
service_id nullable
package_id nullable
preferred_date nullable
alternative_date nullable
location nullable
project_brief
reference_links nullable
estimated_budget nullable
source
status
assigned_admin_id nullable
lost_reason nullable
created_at
updated_at
```

Add-ons should use relation/pivot or snapshot representation as appropriate.

Internal notes must not be serialized to visitor/client responses.

## Status Transition

Baseline:

```text
NEW → CONTACTED
NEW → LOST

CONTACTED → QUALIFIED
CONTACTED → LOST

QUALIFIED → QUOTATION
QUALIFIED → LOST

QUOTATION → WON
QUOTATION → LOST
```

Do not allow arbitrary status mutation through generic update endpoint.

Use explicit action:

```text
ChangeInquiryStatus
```

## Admin UI

```text
/admin/sales/inquiries
/admin/sales/inquiries/[id]
```

List must support:

```text
search
status filter
assigned admin
service
date range
source when present
pagination
```

## Dashboard integration

Admin dashboard starts showing real:

```text
New Inquiries
```

## Tests

```text
AuthorizedAdminCanCreateInquiry
AuthorizedAdminCanAssignInquiry
InvalidInquiryTransitionRejected
InternalInquiryNoteNotExposedToClient
InquiryListCanFilterByStatus
UnauthorizedRoleCannotReadInquiry
```

Public unauthenticated inquiry endpoint can be implemented now at API level even before public Book page exists.

---

# 23. Phase 7 — Quotation

## Canonical Status

```text
DRAFT
SENT
VIEWED
REVISION_REQUESTED
ACCEPTED
DECLINED
EXPIRED
CANCELLED
```

## Core Tables

Recommended conceptual:

```text
quotations
quotation_versions
quotation_items
quotation_events or status history
```

Accepted commercial data must remain immutable as snapshot.

## Quotation Base Record

Minimum:

```text
id
public_id
quotation_number
inquiry_id nullable
client_id
current_version_id
status
accepted_version_id nullable
sent_at nullable
viewed_at nullable
accepted_at nullable
declined_at nullable
expires_at
created_by
created_at
updated_at
```

## Quotation Version

Minimum:

```text
id
quotation_id
version_number
project_name
service_name_snapshot
package_name_snapshot
subtotal
discount
tax
grand_total
dp_type
dp_value
dp_amount
remaining_amount
terms
notes
created_by
created_at
```

## Quotation Items

Minimum:

```text
quotation_version_id
type
description
quantity
unit_price
line_total
sort_order
```

## Version Rule

Never mutate accepted/sent historical commercial content silently.

Revision:

```text
V1 SENT
→ client requests revision
→ V2 created
→ V1 preserved
```

## Send Action

Use explicit:

```text
SendQuotation
```

No generic status assignment.

## Client Decision

Client endpoints:

```text
POST /api/v1/client/quotations/{quotation}/accept
POST /api/v1/client/quotations/{quotation}/request-revision
POST /api/v1/client/quotations/{quotation}/decline
```

Exact REST design may vary but action semantics must remain explicit.

## Client Portal

Routes:

```text
/client/quotations
/client/quotations/[id]
```

## On Acceptance

A successful quotation acceptance must:

```text
lock quotation/version
record actor
record timestamp
preserve accepted snapshot
ensure client identity/account flow exists
mark inquiry commercial conversion appropriately
prepare DP invoice workflow
audit
```

Do not activate project yet if required DP is not satisfied.

## Tests

```text
ClientCanOnlySeeOwnQuotation
SentQuotationCanBeAccepted
ExpiredQuotationCannotBeAccepted
CancelledQuotationCannotBeAccepted
QuotationRevisionCreatesNewVersion
AcceptedVersionCannotBeOverwritten
AcceptanceStoresActorAndTimestamp
InquiryBecomesWonAtCorrectCommercialPoint
```

---

# 24. Phase 8 — Client Invitation / Activation

## Trigger

After quotation acceptance or other approved business trigger.

## Rules

- Do not require account before inquiry.
- Avoid duplicate login account creation.
- Invitation token must expire.
- Invitation token must not be stored plaintext if a safer hashed representation is practical.
- User must set password securely.
- Email verification policy follows product/security decision.
- Client association must be explicit.

## Client Portal Access

After activation:

```text
/client/dashboard
/client/quotations
/client/invoices
/client/projects
```

Before data exists, use real empty states.

---

# 25. Phase 9 — Invoice Domain

## Canonical Status

```text
DRAFT
ISSUED
PARTIALLY_PAID
PAID
OVERDUE
VOID
REFUNDED
```

## Important Commercial Flow

Blueprint requires:

```text
Quotation Accepted
→ DP Invoice
→ Payment
→ Project Activated
```

Therefore initial DP invoice may exist **before active project creation**.

Implementation must support this cleanly.

Recommended Release A rule:

```text
invoice.client_id          required
invoice.quotation_id       required for pre-project DP invoice
invoice.project_id         nullable until project activation
```

After project activation, relevant invoice may be linked to the created project.

This avoids creating an active project before payment rule is satisfied.

Any schema difference from `TECHNICAL.md` example table must be documented when implemented.

## Invoice Fields

Minimum:

```text
id
public_id
invoice_number
client_id
quotation_id nullable
project_id nullable
invoice_type
amount
paid_amount
currency
status
issued_at
due_at
paid_at
voided_at nullable
created_by
created_at
updated_at
```

`invoice_type` may start with:

```text
DP
PROGRESS
FINAL
ADDITIONAL
```

if consistent with business rules.

## Invoice Actions

```text
CreateInvoice
IssueInvoice
VoidInvoice
RecalculateInvoicePaymentState
```

Do not allow client to edit invoice.

## Client UI

```text
/client/invoices
/client/invoices/[id]
```

## Admin UI

```text
/admin/finance/invoices
/admin/finance/invoices/[id]
```

---

# 26. Phase 10 — Duitku Sandbox Integration

## Goal

Use real sandbox for manual/integration verification.

## Integration Boundary

```text
app/Domains/Payments/Contracts/PaymentGateway.php
app/Integrations/Duitku/
```

Contract:

```php
interface PaymentGateway
{
    public function createTransaction(
        CreatePaymentCommand $command
    ): PaymentGatewayResult;

    public function checkTransaction(
        string $merchantOrderId
    ): PaymentGatewayStatus;

    public function verifyCallback(
        array $payload,
        array $headers = []
    ): VerifiedPaymentEvent;
}
```

Runtime binding:

```text
PaymentGateway
→ DuitkuPaymentGateway
```

No fake runtime implementation is required.

## Duitku Environment Variables

Minimum conceptual configuration:

```text
DUITKU_ENV=sandbox
DUITKU_MERCHANT_CODE=
DUITKU_API_KEY=
DUITKU_BASE_URL=
DUITKU_CALLBACK_URL=
DUITKU_RETURN_URL=
DUITKU_CONNECT_TIMEOUT=
DUITKU_TIMEOUT=
```

Secrets must not be committed.

Sandbox and production base URL must be configuration driven.

## Create Transaction Flow

```text
Client selects eligible invoice
→ POST pay action
→ authorize invoice ownership
→ verify invoice payable
→ create PaymentTransaction row
→ generate unique merchant_order_id
→ call Duitku create invoice
→ store provider reference
→ store payment URL
→ update canonical transaction status
→ return safe payment URL/data
```

## Payment Transaction Fields

```text
id
public_id
invoice_id
provider
merchant_order_id
provider_reference nullable
amount
payment_method nullable
status
payment_url nullable
expires_at nullable
paid_at nullable
raw_provider_response encrypted/json with safe retention policy
created_at
updated_at
```

Do not log API key.

## Canonical Payment Status

```text
UNPAID
PENDING
PAID
FAILED
EXPIRED
REFUNDED
PARTIALLY_REFUNDED
```

Use enum.

---

# 27. Duitku Callback Endpoint

Use a provider-specific webhook route.

Example:

```text
POST /api/webhooks/duitku
```

Do not place it behind browser session authentication.

Protect it through provider validation.

## Callback Processing Order

Mandatory:

```text
1. Parse required fields.
2. Reject malformed payload safely.
3. Resolve merchant_order_id.
4. Resolve PaymentTransaction.
5. Validate merchant/order/amount consistency.
6. Compute expected HMAC-SHA256 signature.
7. Compare using timing-safe comparison.
8. Perform Duitku transaction-status check.
9. Map provider state to canonical BDJG PaymentStatus.
10. Open DB transaction.
11. lock payment/invoice rows as needed.
12. Check current state for idempotency.
13. Apply valid forward transition.
14. Write PaymentStatusHistory.
15. Recalculate invoice paid_amount/status.
16. Evaluate project activation rule.
17. Write audit events.
18. Commit.
19. Dispatch safe side effects.
20. Return HTTP 200 only after event has been safely accepted.
```

## Never

```text
returnUrl resultCode → mark PAID
client screenshot → mark PAID
client JavaScript callback → mark PAID
```

---

# 28. Payment Status History

Table minimum:

```text
id
payment_transaction_id
from_status
to_status
source
provider_payload
provider_reference nullable
created_at
```

`source` examples:

```text
CREATE_TRANSACTION
DUITKU_CALLBACK
DUITKU_STATUS_CHECK
MANUAL_RECONCILIATION
REFUND
```

---

# 29. Duitku Idempotency

Required database safeguards:

```text
unique merchant_order_id
provider reference indexed/unique where appropriate
```

Callback must be safe for repeated delivery.

If current transaction already:

```text
PAID
```

and verified provider event resolves to same state:

```text
no duplicate paid side effect
no duplicate project
no duplicate receipt
no duplicate critical notification
```

Out-of-order stale event must not regress trusted confirmed state without explicit provider-supported transition.

---

# 30. Transaction Status Check Failure

If:

```text
callback signature valid
BUT
transaction-status request temporarily fails
```

do not guess.

Safe behavior:

```text
retain non-final safe state
log sanitized operational error
store callback evidence
schedule/manual reconciliation path if needed
do not mark PAID solely from browser
```

---

# 31. Duitku Return Page

Frontend:

```text
/payment/return
```

or client-scoped equivalent.

Behavior:

```text
show "Payment is being verified"
→ call BDJG API for invoice/payment state
→ poll with bounded short-lived UI retry if needed
→ stop after reasonable limit
→ never mutate payment state client-side
```

Do not aggressively poll Duitku directly from browser.

---

# 32. Duitku Sandbox Development Requirement

Duitku callback must reach developer environment.

Use a publicly reachable HTTPS callback URL via an approved tunnel or deployed development environment.

Requirements:

```text
HTTPS
publicly reachable
routes to local/dev Laravel callback
no secret in URL
logs sanitized
```

Provider choice for tunnel is not locked.

---

# 33. Automated Payment Testing

CI and feature tests must not require live Duitku availability.

Use Laravel:

```text
Http::fake()
```

Test:

```text
CreateDuitkuTransactionSuccess
CreateDuitkuTransactionTimeout
CreateDuitkuTransactionProviderError
ValidCallbackPaid
InvalidCallbackSignatureRejected
CallbackWrongAmountRejected
UnknownMerchantOrderRejected
DuplicatePaidCallbackIsIdempotent
OutOfOrderCallbackDoesNotRegressPaid
TransactionStatusCheckFailureDoesNotGuessPaid
InvoiceBecomesPaidAfterVerifiedPayment
ReturnUrlCannotMarkInvoicePaid
```

Manual sandbox test remains mandatory before Release A sign-off.

---

# 34. Phase 11 — Project Activation

## Activation Rule

Default:

```text
Accepted Quotation
+
Required DP Satisfied
=
Project can be activated
```

Use explicit action:

```text
ActivateProject
```

Do not spread activation logic across webhook/controller/UI.

## Activation Execution

On verified payment change:

```text
Payment updated
→ Invoice recomputed
→ EvaluateProjectActivation
→ if satisfied:
     ActivateProject
```

## Project Creation

Recommended Release A behavior:

```text
No active project before activation requirement is satisfied.
```

When activation is satisfied:

```text
create Project
→ snapshot accepted commercial values
→ link quotation
→ link client
→ link relevant existing invoice(s)
→ set initial project status
→ assign responsible admin if known
→ audit
```

If implementation chooses to create a pre-activation `DRAFT` Project earlier, this must be reconciled explicitly with PRD/blueprint before coding; AI agent must not make that choice silently.

Baseline for this plan: **create project when activation rule is satisfied**.

## Initial Project Status

Use:

```text
PRE_PRODUCTION
```

after successful activation unless domain rule requires a short internal `DRAFT` creation transition inside the same transaction/action.

Do not expose a misleading active DRAFT to client before payment.

---

# 35. Project Model

Minimum:

```text
id
public_id
project_number
client_id
quotation_id
accepted_quotation_version_id
name
service_name_snapshot
package_name_snapshot
contract_value
status
start_date nullable
shoot_date nullable
deadline nullable
location nullable
brief
assigned_admin_id nullable
activated_at
completed_at nullable
created_at
updated_at
```

Snapshot fields preserve accepted commercial context.

## Canonical Status

```text
DRAFT
PRE_PRODUCTION
PRODUCTION
POST_PRODUCTION
INTERNAL_REVIEW
CLIENT_REVIEW
REVISION
FINAL_APPROVAL
FINAL_DELIVERY
COMPLETED
ARCHIVED
CANCELLED
```

Release A only needs to exercise a subset, but enum must use canonical status vocabulary.

---

# 36. Project State Transition Service

Do not expose generic:

```text
PATCH project.status = anything
```

Use:

```text
ChangeProjectStatus
```

with allowed transition map.

Initial baseline:

```text
DRAFT → PRE_PRODUCTION
PRE_PRODUCTION → PRODUCTION
PRODUCTION → POST_PRODUCTION
POST_PRODUCTION → INTERNAL_REVIEW
INTERNAL_REVIEW → CLIENT_REVIEW
CLIENT_REVIEW → REVISION
CLIENT_REVIEW → FINAL_APPROVAL
REVISION → INTERNAL_REVIEW
FINAL_APPROVAL → FINAL_DELIVERY
FINAL_DELIVERY → COMPLETED
COMPLETED → ARCHIVED
```

Cancellation rules require explicit permission and audit.

Admin/Owner controls main project stage by default.

Worker does not directly move global project stage unless explicitly granted a future action.

---

# 37. Project UI — Admin

Routes:

```text
/admin/projects
/admin/projects/[id]
/admin/projects/board
```

Project detail Release A tabs:

```text
Overview
Commercial Summary
Team
Tasks
Schedule
Activity
```

Do not expose unimplemented media/revision tabs as functional if backend is absent.

Admin project list filters:

```text
status
client
assigned admin
service
date
search
```

Production board grouped by major project status.

---

# 38. Phase 12 — Worker Profile

Worker lifecycle status:

```text
ACTIVE
INACTIVE
ON_LEAVE
```

User account lifecycle remains separate.

Worker fields:

```text
id
public_id
user_id
profession
skills nullable
phone
status
notes_internal nullable
created_at
updated_at
```

Profession is not Spatie role.

All workers still have:

```text
WORKER
```

security role.

---

# 39. Phase 13 — Project Assignment

Table:

```text
project_assignments
```

Minimum:

```text
id
project_id
worker_id
assignment_role
is_active
assigned_at
removed_at nullable
assigned_by
created_at
updated_at
```

Policy uses active assignment.

## Assignment actions

```text
AssignWorkerToProject
RemoveWorkerFromProject
```

Assignment change must audit:

```text
actor
project
worker
assignment role
timestamp
```

Removing assignment must revoke resource access immediately or predictably.

---

# 40. Phase 14 — Tasks

## Canonical Status

```text
TODO
IN_PROGRESS
REVIEW
BLOCKED
DONE
CANCELLED
```

## Task fields

```text
id
public_id
project_id
title
description nullable
status
priority
assigned_worker_id nullable
due_at nullable
completed_at nullable
created_by
created_at
updated_at
```

## Worker Permission

Worker can update eligible assigned task state.

Worker must not arbitrarily edit:

```text
project finance
client billing
project ownership
global stage
```

## Task state baseline

```text
TODO → IN_PROGRESS
IN_PROGRESS → REVIEW
IN_PROGRESS → BLOCKED
BLOCKED → IN_PROGRESS
REVIEW → DONE
REVIEW → IN_PROGRESS
```

Admin/Owner can perform broader controlled transitions according to permission.

---

# 41. Phase 15 — Basic Schedule

Release A needs enough schedule data for dashboard context.

Event types may include:

```text
MEETING
SHOOT
DEADLINE
INTERNAL_REVIEW
CLIENT_REVIEW
```

Minimum fields:

```text
id
public_id
project_id
type
title
starts_at
ends_at nullable
location nullable
visibility
created_by
created_at
updated_at
```

Visibility must distinguish client-visible vs internal when appropriate.

---

# 42. Phase 16 — Worker Portal

## Dashboard

Real metrics:

```text
Active Projects
Tasks Today
Tasks Overdue
Upcoming Shoot
Deadline This Week
```

`Revision Assigned` and `Pending Internal Review` can be added when related module exists.

Do not fake unavailable metrics.

## My Projects

Worker query:

```text
active assignment only
```

Worker cannot enumerate all project IDs.

## Project Detail

Release A shows:

```text
Overview
Brief
Schedule
Team
My Tasks
Relevant non-media metadata if available
Activity relevant to worker
```

Must not return:

```text
invoice
payment
profit
worker cost unrelated to current worker
general client directory
other projects
```

## Worker Tests

```text
WorkerSeesAssignedProject
WorkerCannotSeeUnassignedProject
RemovedWorkerLosesProjectAccess
WorkerCannotSeeInvoice
WorkerCanUpdateAssignedTask
WorkerCannotUpdateAnotherWorkersTaskUnlessPermitted
WorkerCannotChangeProjectCommercialState
```

---

# 43. Phase 17 — Client Portal Project View

## Dashboard

Real widgets:

```text
Active Projects
Payment Due
Next Shoot
```

Only add:

```text
Waiting for Review
Recent Files
```

after media module exists.

## My Projects

Filter:

```text
ACTIVE
COMPLETED
ARCHIVED
```

`WAITING REVIEW` may be computed after review lifecycle is implemented.

## Project Detail Release A

Client may see:

```text
Overview
high-level status
client-safe timeline
client-visible schedule
quotation reference
invoice/payment summary
```

Client must not see:

```text
internal expense
worker cost
profit
internal notes
private draft
sensitive admin activity
unreleased media
```

## Client Tests

```text
ClientSeesOwnProject
ClientCannotSeeOtherProject
ClientCannotSeeInternalFinance
ClientCannotSeeWorkerInternalNotes
ClientCanSeeOwnInvoice
ClientCannotSeeOtherInvoice
```

---

# 44. Phase 18 — Admin Dashboard

Admin dashboard is built **after enough real domain data exists**.

## Summary Cards

Release A:

```text
New Inquiries
Quotation Waiting
Quotation Accepted
Active Projects
Invoices Due
Today Shoots
```

Later:

```text
Client Review Waiting
Revisions Open
```

## Operational Sections

Release A:

```text
Today's Schedule
Upcoming Production
Overdue Tasks
Payment Attention
Recent Activity
```

Later:

```text
Waiting Client Approval
Worker Conflict
Revision Attention
```

## Finance Summary

Only display if user has permission.

Possible Release A:

```text
Cash Received
Outstanding
```

Do not display project profit until finance model is sufficiently implemented.

## Query Rules

Create dedicated query objects/services for dashboard aggregate if queries become complex.

Avoid loading entire tables into PHP and counting in memory.

Use database aggregation.

---

# 45. Phase 19 — Users & Role Management UI

Owner route:

```text
/admin/system/users
/admin/system/roles
```

Admin access only if explicitly permitted.

## User Management

Actions:

```text
invite
activate
suspend
disable
assign role
remove role
```

Role change is sensitive.

## Audit

Record:

```text
actor
target user
old role
new role
timestamp
request context if appropriate
```

Do not allow a normal Admin to promote themselves to Owner.

Owner role assignment must require explicit trusted path.

---

# 46. Phase 20 — Audit Log

Audit is not a late feature.

Core table:

```text
audit_logs
```

Minimum:

```text
id
public_id
event_type
actor_user_id nullable
subject_type
subject_id
before_data nullable
after_data nullable
metadata nullable
ip_address nullable
user_agent nullable
created_at
```

Append-only from normal application flow.

## Mandatory Release A audit events

```text
login sensitive event where required
role assigned/removed
permission-sensitive user change

inquiry assigned
inquiry status changed

quotation sent
quotation revision created
quotation accepted
quotation declined

invoice issued
payment status changed
manual reconciliation

project activated
project status changed

worker assigned
worker removed

task significant status change
```

## Audit UI

```text
/admin/system/activity
```

Filter:

```text
actor
event type
resource
date
```

Only Owner/authorized Admin.

---

# 47. Phase 21 — Search, Filter, Pagination

Core admin datasets must not become endless unfiltered lists.

Implement server-side:

```text
pagination
search
sort whitelist
filter whitelist
```

Release A targets:

```text
inquiries
clients
quotations
invoices
payments
projects
workers
audit
```

Do not allow arbitrary user-controlled database column/order injection.

---

# 48. API Route Baseline

All business API routes use:

```text
/api/v1
```

Provider webhook can use:

```text
/api/webhooks
```

Example conceptual routes:

```text
GET    /api/v1/me

GET    /api/v1/admin/dashboard

GET    /api/v1/clients
POST   /api/v1/clients
GET    /api/v1/clients/{client}
PATCH  /api/v1/clients/{client}

GET    /api/v1/inquiries
POST   /api/v1/inquiries
GET    /api/v1/inquiries/{inquiry}
PATCH  /api/v1/inquiries/{inquiry}
POST   /api/v1/inquiries/{inquiry}/assign
POST   /api/v1/inquiries/{inquiry}/change-status

GET    /api/v1/quotations
POST   /api/v1/quotations
GET    /api/v1/quotations/{quotation}
POST   /api/v1/quotations/{quotation}/versions
POST   /api/v1/quotations/{quotation}/send

GET    /api/v1/client/quotations
GET    /api/v1/client/quotations/{quotation}
POST   /api/v1/client/quotations/{quotation}/accept
POST   /api/v1/client/quotations/{quotation}/request-revision
POST   /api/v1/client/quotations/{quotation}/decline

GET    /api/v1/invoices
POST   /api/v1/invoices
GET    /api/v1/invoices/{invoice}
POST   /api/v1/invoices/{invoice}/issue

GET    /api/v1/client/invoices
GET    /api/v1/client/invoices/{invoice}
POST   /api/v1/client/invoices/{invoice}/payments

POST   /api/webhooks/duitku

GET    /api/v1/projects
GET    /api/v1/projects/{project}
POST   /api/v1/projects/{project}/change-status
POST   /api/v1/projects/{project}/assignments

GET    /api/v1/worker/projects
GET    /api/v1/worker/projects/{project}

GET    /api/v1/tasks
POST   /api/v1/tasks
PATCH  /api/v1/tasks/{task}

GET    /api/v1/client/projects
GET    /api/v1/client/projects/{project}
```

Exact route naming may change to fit controller design, but no generic endpoint should bypass action semantics for critical transitions.

---

# 49. API Response Rules

Use Laravel API Resources.

Do not return raw model blindly.

Avoid:

```php
return $project;
```

when model contains internal relations/fields.

Use resource projection based on endpoint audience.

Examples:

```text
AdminProjectResource
WorkerProjectResource
ClientProjectResource
```

or equivalent explicit projection.

This prevents finance/internal data leakage.

---

# 50. OpenAPI Workflow

Backend contract is authority.

Workflow:

```text
Laravel route/controller/request/resource
→ Scramble OpenAPI
→ OpenAPI document
→ generate TypeScript client/types
→ packages/api-client
→ Next.js uses generated contract
```

Generated API files:

```text
do not edit manually
```

CI should detect stale generated contract if repository commits generated output.

---

# 51. Database Migration Order

Recommended order:

```text
001 users/session/auth foundation
002 Spatie permission tables
003 clients
004 client-user relationship if used
005 worker profiles
006 services
007 packages
008 add-ons
009 inquiries
010 inquiry notes/history
011 quotations
012 quotation versions
013 quotation items
014 quotation status/history
015 invoices
016 payment transactions
017 payment status histories
018 projects
019 project assignments
020 tasks
021 schedules
022 audit logs
```

Exact migration timestamp filenames are generated by Laravel.

Foreign keys and indexes must be deliberate.

---

# 52. Database Constraints

Use constraints for invariant that database can safely enforce.

Examples:

```text
users.email unique
merchant_order_id unique
quotation_number unique
invoice_number unique
project_number unique
public_id unique
quotation version unique by quotation_id + version_number
active assignment uniqueness where implementation supports it safely
```

Money fields use integer minor unit or validated fixed decimal policy defined consistently.

For IDR, avoid floating point.

---

# 53. Public ID

Protected/public URLs should use stable non-sequential public identifiers according to `TECHNICAL.md` ID strategy.

Do not expose internal autoincrement IDs as the only public identifier if project standard uses ULID/UUID/public_id.

---

# 54. State Transition Ownership

Critical state changes must use Actions.

Examples:

```text
ChangeInquiryStatus
SendQuotation
CreateQuotationVersion
AcceptQuotation
IssueInvoice
CreatePaymentTransaction
ProcessDuitkuCallback
RecalculateInvoicePaymentState
ActivateProject
ChangeProjectStatus
AssignWorkerToProject
RemoveWorkerFromProject
ChangeTaskStatus
```

Controllers remain thin.

---

# 55. Database Transaction Boundaries

Must use DB transaction around multi-record invariant changes.

Examples:

## Quotation acceptance

```text
quotation accepted
accepted_version locked
inquiry conversion update
client/account trigger
invoice preparation
audit
```

## Payment callback

```text
payment
payment history
invoice
project activation
audit
```

## Worker assignment

```text
assignment
access relationship
audit
```

Do not hold database transaction open while waiting on slow external HTTP request if avoidable.

Pattern:

```text
external provider verification
→ then DB transaction for local atomic mutation
```

with concurrency/idempotency safeguards.

---

# 56. Concurrency Rules

Use row locking where concurrent callbacks/actions can collide.

Potential targets:

```text
payment_transactions
invoices
quotation acceptance
project activation
```

Project activation must be exactly-once at business level even if two processes evaluate activation simultaneously.

Use:

```text
unique project commercial reference
lockForUpdate()
current state check
```

as appropriate.

---

# 57. Error Handling

API errors must be consistent.

Categories:

```text
401 unauthenticated
403 forbidden
404 not found
409 conflict / invalid state transition when appropriate
422 validation
429 rate limited
5xx unexpected/provider failure
```

Do not expose:

```text
stack trace
API key
database credential
internal filesystem path
raw sensitive provider payload
```

to browser.

---

# 58. Security Baseline

Release A must include:

```text
CSRF protection
secure cookies per environment
SameSite configuration
rate limiting
validation
authorization
mass-assignment protection
safe serialization
security headers
secret isolation
sanitized logging
password hashing
audit
```

Same-origin production deployment is preferred according to `TECHNICAL.md`.

---

# 59. Rate Limiting

Minimum distinct limiter categories:

```text
login
password reset
public inquiry
payment create
Duitku callback abuse protection where safe
general authenticated API
```

Do not rate-limit provider callback so aggressively that valid retries are discarded without thought.

Signature/provider validation remains primary callback trust mechanism.

---

# 60. Seed Strategy

Provide deterministic development seed.

Seed accounts:

```text
owner@...
admin@...
worker1@...
worker2@...
client1@...
client2@...
```

Use non-production known development password documented only for local environment.

Never seed production with default password.

Seed:

```text
roles
permissions
role mappings
services
packages
clients
workers
sample inquiry
sample quotation states
sample invoice states
sample project states
assignments
tasks
schedule
```

Seed must demonstrate authorization isolation:

```text
Client A → Project A
Client B → Project B

Worker A → assigned Project A
Worker B → assigned Project B
```

This makes cross-access tests easy.

---

# 61. Admin Dashboard Seed Scenarios

Development fixtures should include:

```text
NEW inquiry
QUALIFIED inquiry
SENT quotation
ACCEPTED quotation
ISSUED invoice
PENDING payment
PAID invoice
PRE_PRODUCTION project
PRODUCTION project
overdue task
today shoot
```

Dashboard should display numbers based on these fixtures.

---

# 62. Testing Strategy

Use majority Feature tests for business/API flows.

Unit test pure logic where useful.

## Backend test groups

```text
Auth
Authorization
CRM
Quotation
Billing
Payments
Projects
Assignments
Tasks
Schedules
Audit
```

## Frontend test groups

```text
portal shell
navigation
forms
status rendering
loading/error/empty
permission-based UI
```

## E2E

Use Playwright for cross-application golden paths.

---

# 63. Golden Path E2E — Release A

Test scenario:

```text
1. Owner logs in.
2. Creates/qualifies inquiry.
3. Creates client if needed.
4. Creates quotation.
5. Sends quotation.
6. Client logs in.
7. Client opens only own quotation.
8. Client accepts.
9. Admin/client sees DP invoice.
10. Client begins Duitku Sandbox payment.
11. Sandbox payment completed manually/test method.
12. Callback arrives.
13. Backend verifies transaction.
14. Invoice becomes PAID.
15. Project is activated.
16. Admin assigns Worker.
17. Worker logs in.
18. Worker sees project.
19. Worker changes assigned task.
20. Client sees project progress.
21. Client attempts another client's project URL → denied.
22. Worker attempts unassigned project URL → denied.
23. Admin/Owner views audit log.
```

Parts involving live sandbox can be tagged manual/sandbox E2E instead of blocking every CI run.

---

# 64. Required Authorization E2E

Must test IDOR-like access explicitly.

Examples:

```text
GET /client/projects/{otherClientProject}
→ 403 or safe 404

GET /worker/projects/{unassignedProject}
→ 403 or safe 404

GET /client/invoices/{otherClientInvoice}
→ denied
```

Response must not leak protected resource metadata.

---

# 65. Required Payment Manual Sandbox Checklist

Before Release A sign-off:

- [ ] Sandbox merchant credential configured.
- [ ] Create transaction request succeeds.
- [ ] `merchant_order_id` is unique.
- [ ] Provider reference stored.
- [ ] Sandbox payment URL opens.
- [ ] Callback URL reachable publicly by HTTPS.
- [ ] Valid callback signature passes.
- [ ] Wrong signature test is rejected.
- [ ] Transaction-status check succeeds.
- [ ] Payment maps to correct canonical status.
- [ ] Duplicate callback does not duplicate side effects.
- [ ] Return URL alone cannot mark invoice paid.
- [ ] Paid invoice triggers activation evaluation.
- [ ] Project activates only once.
- [ ] Payment history exists.
- [ ] Audit record exists.
- [ ] Provider/API key does not appear in logs.

---

# 66. CI Pipeline — Release A

Required checks:

```text
composer validate
composer install
php artisan test
Laravel Pint check

pnpm install --frozen-lockfile
frontend lint
frontend typecheck
frontend unit test
frontend build

OpenAPI generation validation
generated client freshness if committed
```

Optional/next:

```text
Playwright headless against composed environment
```

Live Duitku Sandbox must not be required for every commit CI.

---

# 67. Definition of Done — Per Backend Feature

A backend feature is not done until:

- [ ] Requirement ID / business rule identified.
- [ ] Migration exists if data changes.
- [ ] Foreign keys/indexes reviewed.
- [ ] Enum/status vocabulary canonical.
- [ ] Model relationship implemented.
- [ ] Business action/service exists for non-trivial mutation.
- [ ] Policy/Gate authorization exists.
- [ ] Form Request validation exists.
- [ ] API Resource prevents data leakage.
- [ ] Controller remains thin.
- [ ] Audit added when event is critical.
- [ ] Feature tests cover success.
- [ ] Feature tests cover unauthorized access.
- [ ] Invalid state transition tested.
- [ ] OpenAPI updated.
- [ ] No secret/raw sensitive payload logged.

---

# 68. Definition of Done — Per Frontend Feature

- [ ] Uses API contract, not duplicated handwritten types when generated types exist.
- [ ] Loading state.
- [ ] Empty state.
- [ ] Error state.
- [ ] Forbidden behavior.
- [ ] Responsive behavior.
- [ ] Keyboard usable for core action.
- [ ] Does not rely on hidden UI for security.
- [ ] Status label uses canonical status.
- [ ] Mutation shows pending/success/error feedback.
- [ ] No hardcoded production metric.
- [ ] No internal-only data rendered for Client/Worker.

---

# 69. Release A Exit Criteria

Operational Dashboard MVP is complete only when all are true.

## Identity

- [ ] Login/logout works.
- [ ] Sanctum stateful session works.
- [ ] CSRF works.
- [ ] password reset architecture works.
- [ ] suspended/disabled behavior works.

## Authorization

- [ ] Spatie roles seeded.
- [ ] Permission matrix works.
- [ ] Owner Super Admin works via Gate.
- [ ] Client ownership isolation tested.
- [ ] Worker assignment isolation tested.
- [ ] Admin granular permission tested.

## Sales

- [ ] Inquiry CRUD/workflow works.
- [ ] Client directory works.
- [ ] Quotation versions work.
- [ ] Client can accept/revise/decline eligible quote.
- [ ] Accepted version cannot be overwritten.

## Billing

- [ ] DP invoice works.
- [ ] Duitku Sandbox transaction creation works.
- [ ] Callback works.
- [ ] signature verification works.
- [ ] transaction status check works.
- [ ] payment idempotency works.
- [ ] invoice status updates correctly.

## Production

- [ ] Project activation rule works.
- [ ] Project activation is exactly-once.
- [ ] Worker assignment works.
- [ ] Task workflow works.
- [ ] basic schedule works.

## Portals

- [ ] Admin dashboard uses real data.
- [ ] Worker dashboard uses real scoped data.
- [ ] Client dashboard uses real owned data.
- [ ] No protected cross-role leak.

## Audit

- [ ] Critical events auditable.

## QA

- [ ] Backend tests green.
- [ ] Frontend build green.
- [ ] core E2E green.
- [ ] manual Duitku sandbox checklist passed.

---

# 70. Release B — Media Workflow Implementation

Release B starts only after Release A exit gate passes.

## Domains

```text
Files
Pending Upload
Previews
Revisions
Final Delivery
```

## Infrastructure

```text
S3-compatible object storage
Redis
Horizon
FFmpeg worker runtime
```

Redis/Horizon may already run in Release A; media workload begins here.

---

# 71. Release B — File Upload

Implement:

```text
Create Pending Upload Intent
→ authorize
→ generate presigned upload URL
→ browser uploads directly
→ finalize upload
→ metadata record
→ queue derivative job if needed
```

Large binary must not transit Laravel API unnecessarily.

File visibility:

```text
INTERNAL
CLIENT_SHARED
CLIENT_PREVIEW
FINAL
```

or canonical equivalent from product documents.

---

# 72. Release B — Preview

Flow:

```text
Worker upload
→ internal file
→ processing
→ preview derivative
→ internal review
→ admin release
→ client can view
```

Client must not discover internal preview before release.

---

# 73. Release B — FFmpeg

Queued job only.

```text
Laravel Job
→ Horizon
→ Laravel Process
→ FFmpeg
```

Test:

```text
success
timeout
invalid media
job retry
cleanup
duplicate job
```

Do not run FFmpeg in request lifecycle.

---

# 74. Release B — Revision

Canonical status:

```text
REQUESTED
TRIAGE
IN_PROGRESS
INTERNAL_REVIEW
READY_FOR_CLIENT
APPROVED
CLOSED
```

Implement revision round limit according to accepted package snapshot.

Timestamp feedback stores:

```text
preview_version
round
timestamp
comment
actor
status
assignment
```

---

# 75. Release B — Final Delivery

Final delivery requires:

```text
approved final media
+
release authorization
+
applicable payment/release policy
```

Do not assume policy not finalized in PRD.

If final release rule remains open, stop and use documented product decision rather than inventing one.

---

# 76. Release C — Notification

Add:

```text
in-app
email
WhatsApp provider
```

Notification dispatch is asynchronous where appropriate.

Failure of external notification must not rollback an already committed payment/project transition.

Use out-of-band retry.

---

# 77. Release C — Finance Expansion

Add:

```text
project expense
estimated gross profit
worker expense claim
finance reports
manual reconciliation UI
refund workflow
```

Finance data remains Owner / explicitly permitted Admin only.

Never serialize profit to Client/Worker.

---

# 78. Release D — Public Site

Only after operational system is stable.

Implement:

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

Public Book form connects to the already stable Inquiry API/domain.

This is the advantage of dashboard-first:

```text
public form
→ existing CRM
```

not a second parallel lead system.

---

# 79. Release D — CMS

CMS manages:

```text
portfolio
services
packages
add-ons
basic contact content
```

Publishing a completed project to public Works must remain explicit.

Project completion does not automatically equal publication consent.

---

# 80. Performance Sequence

Do not optimize before measuring.

Release A:

```text
database indexes
pagination
N+1 detection
bounded queries
reasonable response size
```

Release B/D:

```text
media derivative
CDN/object storage
image/video optimization
public caching
```

Do not introduce Octane only to chase hypothetical performance.

---

# 81. Observability Baseline

Structured logs should include safe contextual identifiers:

```text
request_id
user_public_id
resource_public_id
merchant_order_id where safe
event
status
```

Never include:

```text
password
session cookie
API key
full sensitive payment payload
reset token
```

---

# 82. Implementation Checkpoint Format for AI Agent

At the end of every phase, AI agent should report:

```text
Phase:
Completed:
Files changed:
Migrations:
API added/changed:
Permissions added:
Policies added:
Tests added:
Tests passed:
Known remaining issue:
Next phase:
```

Do not report phase complete if tests are knowingly failing.

---

# 83. AI Agent Prohibitions

Agent MUST NOT:

- invent business status;
- rename canonical status without updating source documents;
- replace Laravel with another backend;
- add JWT for first-party session auth;
- create custom RBAC engine instead of Spatie;
- enable Spatie Teams without explicit decision;
- use multiple guards merely for roles;
- authorize only from frontend;
- hardcode Owner checks throughout controllers;
- mark payment paid from return URL;
- trust screenshot as payment authority;
- bypass Duitku signature verification;
- skip transaction status verification because callback says success;
- create project twice on repeated callback;
- overwrite accepted quotation version;
- expose client B data to client A;
- expose unassigned project to Worker;
- expose finance/profit to Client or Worker;
- create dummy dashboard metric as production implementation;
- create separate `apps/worker`;
- perform media processing inside HTTP request;
- expose protected object storage publicly;
- add package when native Laravel capability is sufficient without reason;
- edit generated API client manually;
- silently change DB schema contract without migration and tests.

---

# 84. Suggested First Coding Sequence

The coding agent should execute this exact high-level order unless a real dependency requires a documented adjustment.

```text
01 Repository bootstrap
02 Laravel + Next.js boot
03 MySQL + Redis
04 health checks
05 Sanctum + Fortify
06 User lifecycle
07 Spatie package
08 roles/permissions seed
09 Gate::before Owner
10 policy foundation
11 authorization tests
12 portal shells
13 internal catalog
14 client domain
15 inquiry
16 quotation
17 client invitation/activation
18 invoice
19 Duitku create transaction
20 Duitku callback
21 transaction check
22 payment idempotency
23 project activation
24 worker profile
25 assignment
26 task
27 schedule
28 worker portal data
29 client project portal data
30 admin dashboard aggregates
31 role/user management UI
32 audit UI
33 search/filter hardening
34 OpenAPI/client generation review
35 full test suite
36 Playwright golden path
37 manual Duitku Sandbox sign-off
```

---

# 85. Required Review Before Release B

Before beginning media implementation, review:

```text
Does admin dashboard reflect real operational data?
Can Client A ever read Client B?
Can Worker read unassigned project?
Can payment be marked paid without provider verification?
Can duplicate callback create duplicate project?
Can accepted quotation be mutated?
Can ordinary Admin escalate to Owner?
Are critical operations auditable?
```

If any answer reveals a flaw, fix Release A first.

---

# 86. Known Document Synchronization Required

The current `TECHNICAL.md` original build-order section may still describe Portal later than the dashboard-first decision.

After adoption of this plan:

```text
TECHNICAL.md Section 101
```

should be updated so that implementation order does not conflict.

Architecture itself remains unchanged.

`AGENTS.md` must also later be rewritten to reference:

```text
IMPLEMENTATION.md
```

and enforce phase gates.

---

# 87. External Technical References Verified for This Plan

Official references checked when this plan was prepared:

1. Laravel 13 Sanctum documentation — stateful SPA authentication.
2. Laravel 13 Fortify documentation — SPA should use default `web` guard with Sanctum.
3. Laravel 13 Authorization documentation — Gates and model Policies.
4. Spatie Laravel Permission v8 documentation — Laravel 12/13 compatibility, Gate integration, Super Admin pattern.
5. Duitku official POP/API documentation — sandbox transaction, callback, HMAC-SHA256, transaction status verification.
6. Laravel 13 HTTP Client documentation — `Http::fake()` for deterministic external integration tests.
7. Laravel 13 Horizon documentation — Redis queue operations and monitoring.
8. Laravel 13 Testing documentation — feature/integration test foundation.

---

# 88. Final Implementation Principle

Implementasi BDJG tidak dinilai dari banyaknya halaman yang sudah terlihat.

Implementasi dinilai dari apakah sistem mempunyai **business truth yang konsisten dan aman**.

Urutan prioritas:

```text
Correctness
→ Authorization
→ Transaction integrity
→ Payment correctness
→ Auditability
→ Operational usability
→ Media workflow
→ Public visual experience
```

Dashboard-first bukan berarti UI-first.

Dashboard-first berarti:

```text
build the real business engine
→ expose it through role-specific dashboards
→ test it end-to-end
→ then expand into media and public experience
```

Itulah baseline implementasi BDJG.
