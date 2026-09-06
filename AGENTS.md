# BDJG — AI Coding Agent Instructions

**File:** `AGENTS.md`  
**Project:** BDJG Creative Studio Website & Studio Management System  
**Status:** Repository Agent Execution Contract  
**Execution Model:** Dashboard-first, phase-gated implementation  
**Backend:** Laravel 13 / PHP 8.5  
**Frontend:** Next.js 16 / TypeScript  
**Database:** MySQL 8.4 LTS  
**Authentication:** Laravel Sanctum + Fortify  
**Authorization:** Spatie Laravel Permission + Laravel Policies / Gates  
**Payment:** Duitku Sandbox during MVP development  
**Last baseline review:** 18 August 2026

---

# 1. Purpose

This file defines **how an AI coding agent must work inside the BDJG repository**.

It exists to prevent an agent from:

- skipping required implementation phases;
- implementing later-release features before dependencies are ready;
- inventing product or business rules;
- silently changing architecture;
- treating a visually finished UI as a completed feature;
- weakening authorization;
- corrupting payment or commercial state;
- drifting away from the product, blueprint, design, technical, implementation, or completion documents;
- replacing approved technology because the agent prefers another stack;
- reporting work as complete without evidence.

This file is an **execution contract**.

It is not the product specification.

It is not the business blueprint.

It is not the design system.

It is not the technical architecture.

It is not the implementation plan.

It is not the completion checklist.

The agent must route every decision to the correct source document.

---

# 2. Absolute Agent Mandate

The primary rule is:

```text
UNDERSTAND
→ TRACE
→ IMPLEMENT CURRENT PHASE
→ AUTHORIZE
→ VALIDATE
→ TEST
→ VERIFY
→ RECORD EVIDENCE
→ PASS EXIT GATE
→ ONLY THEN CONTINUE
```

Never use:

```text
MAKE UI
→ ASSUME BACKEND
→ CALL IT DONE
```

Never use:

```text
BUILD WHAT LOOKS INTERESTING
→ BACKFILL DEPENDENCIES LATER
```

Never use:

```text
AGENT PREFERENCE
→ OVERRIDE REPOSITORY DECISION
```

The repository documents are authoritative.

---

# 3. Mandatory Source-of-Truth Hierarchy

The agent must use this hierarchy:

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
PHASE-COMPLETION-CHECKLIST.md
↓
AGENTS.md
```

Responsibilities:

| Document | Authority |
|---|---|
| `PRD.md` | Product scope, requirement priority, acceptance criteria, product behavior, release boundary |
| `blueprint.md` | Business flow, domain relationships, role behavior, status, lifecycle, operational rules |
| `DESIGN.md` | Visual language, layout, component behavior, responsive rules, accessibility, visual QA |
| `TECHNICAL.md` | Architecture, technology, backend/frontend conventions, security implementation, integration architecture |
| `IMPLEMENTATION.md` | Build order, phase dependency, locked execution decisions, release slices, tests required during implementation |
| `PHASE-COMPLETION-CHECKLIST.md` | Mandatory proof and exit gates before a phase/release may be called complete |
| `AGENTS.md` | Agent behavior, execution discipline, repository safety, and reporting contract |

`AGENTS.md` is last in authority.

It may make execution stricter.

It must never silently weaken or replace a requirement from a higher-authority document.

---

# 4. Canonical Repository Documentation Paths

Expected repository layout:

```text
/
├── AGENTS.md
│
├── docs/
│   ├── PRD.md
│   ├── blueprint.md
│   ├── DESIGN.md
│   ├── TECHNICAL.md
│   ├── IMPLEMENTATION.md
│   └── PHASE-COMPLETION-CHECKLIST.md
│
├── apps/
│   ├── web/
│   └── api/
│
├── packages/
│   ├── api-client/
│   ├── ui/
│   └── config/
│
├── package.json
├── pnpm-workspace.yaml
├── composer.json / backend composer files as defined by repository
├── docker-compose.yml
└── README.md
```

If the documents are temporarily elsewhere during repository setup, locate them before coding.

Do not assume a document is missing merely because its path changed.

Do not create duplicate authoritative copies such as:

```text
TECHNICAL-new.md
TECHNICAL-final.md
TECHNICAL-fixed.md
PRD-copy.md
```

The repository should converge on one canonical copy per source document.

---

# 5. First-Time Agent Reading Protocol

When an AI agent begins working in the repository for the first time, it must read all six project documents before implementing production code:

```text
docs/PRD.md
docs/blueprint.md
docs/DESIGN.md
docs/TECHNICAL.md
docs/IMPLEMENTATION.md
docs/PHASE-COMPLETION-CHECKLIST.md
```

Then read:

```text
AGENTS.md
```

The agent must understand at minimum:

```text
product goal
roles
scope boundaries
canonical business flow
canonical statuses
authorization model
technical architecture
release boundaries
current implementation phase
phase exit criteria
known open decisions
```

Do not rely on memory from another conversation or an older branch.

Repository files are the current authority.

---

# 6. Per-Task Reading Protocol

Before every implementation task, identify:

```text
1. requirement/domain being changed
2. current implementation phase
3. affected role(s)
4. affected resource(s)
5. affected state machine
6. affected API/data contract
7. applicable completion checklist
```

Then read the relevant portions of:

```text
PRD
blueprint
DESIGN, if UI/UX is affected
TECHNICAL
IMPLEMENTATION
PHASE-COMPLETION-CHECKLIST
AGENTS
```

Do not start coding until the relevant requirement can be traced to repository documentation.

---

# 7. Requirement Trace Rule

For every non-trivial feature, the agent should be able to state:

```text
Requirement:
PRD reference:
Blueprint reference:
Design reference:
Technical reference:
Implementation phase:
Checklist gate:
```

Example conceptually:

```text
Feature:
Client sees own project only

PRD:
Client Portal / authorization requirements

Blueprint:
Client scope = Own

Design:
Client project interface

Technical:
Policy + resource relationship + deny-by-default

Implementation:
Client Portal Project View phase

Checklist:
Client IDOR / ownership isolation gate
```

The agent must not replace this traceability with:

> "This is standard practice."

Repository-specific rules take priority.

---

# 8. Conflict Resolution

If documents appear to conflict, classify the conflict first.

## Product behavior conflict

Use:

```text
PRD.md
```

Do not let lower documents redefine product scope or acceptance criteria.

## Business/domain conflict

Use:

```text
blueprint.md
```

unless the PRD explicitly defines a different product requirement.

## UI/UX conflict

Use:

```text
DESIGN.md
```

unless it violates product/business/security rules.

## Technical implementation conflict

Use:

```text
TECHNICAL.md
```

unless it contradicts a higher product/domain requirement.

## Build-order conflict

Use:

```text
IMPLEMENTATION.md
```

Build order belongs to the implementation plan.

## Completion conflict

Use:

```text
PHASE-COMPLETION-CHECKLIST.md
```

A phase is not complete until its mandatory completion gate passes.

## Agent workflow conflict

Use:

```text
AGENTS.md
```

only for agent execution behavior.

---

# 9. Known Build-Order Synchronization Rule

`IMPLEMENTATION.md` is the execution authority for **what is built first**.

The dashboard-first execution plan is:

```text
Release A
Operational Dashboard MVP
↓
Release B
Media Workflow
↓
Release C
Operations Completion
↓
Release D
Public Experience
```

If an older section in `TECHNICAL.md` shows portal implementation later than media, do **not** use that older sequence to reorder the project.

Use:

```text
IMPLEMENTATION.md
```

for execution order.

Use:

```text
TECHNICAL.md
```

for technical architecture.

This is an ordering synchronization issue, not permission to change architecture.

---

# 10. Locked Dashboard-First Decision

The initial implementation target is **not** the public landing page.

The initial target is:

```text
Authentication
→ RBAC
→ Portal shells
→ Catalog
→ Client
→ Inquiry
→ Quotation
→ Client acceptance
→ Invoice
→ Duitku Sandbox
→ Verified payment
→ Project activation
→ Worker assignment
→ Tasks
→ Schedule
→ Worker portal
→ Client project portal
→ Admin dashboard
→ User/role management
→ Audit
→ Search/filter
→ Release A verification
```

Do not implement the public cinematic website merely because it is visually easier to demonstrate.

Public routes belong to Release D unless the implementation plan is explicitly revised.

---

# 11. Current Release Boundaries

## Release A — Operational Dashboard MVP

Includes:

```text
repository foundation
authentication
user lifecycle
Spatie role/permission
Policies/Gates
Owner super-admin
portal shells
catalog master data
client domain
inquiry/CRM
quotation/version history
client invitation/activation
invoice
Duitku Sandbox
payment history
payment idempotency
project activation
worker
assignment
task
basic schedule
worker portal
client project portal
admin dashboard
user/role management
audit
search/filter/pagination
OpenAPI
automated tests
Playwright golden path
manual Duitku sandbox sign-off
```

## Release B — Media Workflow

Includes:

```text
object storage
pending upload
direct upload
protected file access
preview
FFmpeg
internal review
client preview release
timestamp feedback
revision rounds
final delivery
```

## Release C — Operations Completion

Includes:

```text
in-app notification expansion
email
WhatsApp
finance expansion
worker expense
reconciliation UI
refund workflow if approved
operational alerts
```

## Release D — Public Experience

Includes:

```text
landing page
works
work detail
services
about
studio
contact
book
CMS
SEO
public performance/media experience
```

Do not move a feature earlier simply because an agent can implement it.

---

# 12. Phase Order Is Mandatory

For Release A, the implementation plan defines phases.

The agent must work in phase order.

A later phase may begin only when:

```text
previous mandatory dependency exists
+
previous phase exit gate passes
+
no applicable hard blocker remains
```

If the user asks to implement a later phase directly:

1. inspect dependency state;
2. inspect checklist state;
3. if dependencies already pass, proceed;
4. if dependencies do not pass, implement the earliest missing dependency first unless the user explicitly limits scope;
5. do not fake missing dependencies.

The agent must never create temporary production behavior that violates architecture merely to make a later UI appear functional.

---

# 13. Earliest-Incomplete-Phase Rule

For a broad request such as:

```text
"implement the project"
"continue the MVP"
"continue coding"
```

the agent must:

1. inspect repository state;
2. identify completed phases from actual code/tests/evidence;
3. find the earliest incomplete mandatory phase;
4. work from that phase.

Do not assume a phase is complete because:

- files exist;
- routes exist;
- screenshots exist;
- another agent claimed completion;
- UI renders;
- migrations exist.

Completion must be proven against `PHASE-COMPLETION-CHECKLIST.md`.

---

# 14. Phase Status Vocabulary

Use only:

```text
NOT_STARTED
IN_PROGRESS
BLOCKED
READY_FOR_REVIEW
COMPLETE
DEFERRED
```

Never invent ambiguous status labels such as:

```text
almost done
basically complete
mostly finished
good enough
90% done
```

A phase is either eligible for `COMPLETE` or it is not.

---

# 15. No-Skip Rule

The agent must not:

- skip a mandatory phase;
- silently merge several phases and omit tests;
- mark a phase complete to unblock another phase;
- leave mandatory checklist items unchecked without declaring the phase incomplete;
- convert a blocker into a "future improvement" when it affects security, payment, authorization, data integrity, or an exit criterion.

A skipped mandatory gate is a failed gate.

---

# 16. Deferral Rule

An item may be `DEFERRED` only when:

```text
IMPLEMENTATION.md permits deferral
+
security is not weakened
+
authorization is not weakened
+
data integrity is not weakened
+
payment correctness is not weakened
+
release gate is not violated
```

Every deferral must record:

```text
reason
risk
target phase/release
owner or follow-up responsibility
```

Do not use deferral to hide incomplete work.

---

# 17. Hard Blocker Rule

The agent must not call a phase complete while any applicable hard blocker from `PHASE-COMPLETION-CHECKLIST.md` remains.

Examples include:

```text
migration failure
required test failure
frontend build failure
missing backend authorization
cross-client access
unassigned worker access
finance leak
secret leak
return URL marking payment paid
duplicate callback creating duplicate project
unreviewed destructive migration
OpenAPI contract drift
hardcoded dashboard production metrics
open P0/P1 defect
```

---

# 18. Scope Discipline

Implement only:

```text
requested requirement
+
required dependencies
+
required tests
+
required documentation synchronization
```

Do not opportunistically build:

- unrelated P1/P2 features;
- future features;
- refactors unrelated to the task;
- alternate architectures;
- optional frameworks;
- speculative abstractions.

If a required dependency is larger than the requested change, explain the dependency in the completion report.

---

# 19. No Unrelated Refactoring

Do not refactor unrelated working code while implementing a feature.

Allowed:

- minimal cleanup necessary for correctness;
- extracting logic needed for the current business rule;
- fixing a directly exposed defect;
- removing dead code created by the same change.

Not allowed without explicit scope:

- renaming entire modules;
- changing folder conventions broadly;
- replacing libraries;
- rewriting existing stable pages;
- mass formatting unrelated files;
- migrating architecture for style preference.

Small diffs are easier to review and safer for phase gating.

---

# 20. Preserve Existing User Work

Before editing:

```text
inspect git status
inspect existing changes
inspect relevant files
```

Never:

```text
git reset --hard
git checkout -- .
git clean -fd
force-push
discard unknown changes
```

unless explicitly requested and safe.

Do not overwrite user changes merely because the agent would structure them differently.

---

# 21. Do Not Commit Unless Requested

The agent may edit working-tree files and run tests.

Do not:

- create Git commits;
- amend commits;
- push branches;
- force-push;
- tag releases;

unless the user or repository workflow explicitly requests it.

If commits are requested, keep phase/task boundaries clear.

---

# 22. Technology Baseline Is Locked

Do not silently replace the approved stack.

Baseline:

```text
Frontend Runtime:
Node.js 24 LTS

Frontend Package Manager:
pnpm 11.x

Frontend:
Next.js 16.x

Frontend Language:
TypeScript

Backend Runtime:
PHP 8.5

Backend:
Laravel 13

ORM:
Eloquent

Database:
MySQL 8.4 LTS

Authentication:
Laravel Sanctum + Laravel Fortify

Authorization:
Spatie Laravel Permission + Laravel Policies/Gates

Session:
database-backed Laravel session baseline

Queue:
Laravel Queue + Redis

Queue Operations:
Laravel Horizon

Storage:
Laravel Filesystem + S3-compatible object storage

Payment:
Duitku

Media:
FFmpeg through queued Laravel job / controlled process

API:
REST + OpenAPI 3.1

OpenAPI:
Dedoc Scramble compatible pinned release

Backend Tests:
Pest + Laravel/PHPUnit utilities

Frontend Tests:
Vitest + React Testing Library

E2E:
Playwright

Local Infrastructure:
Docker Compose
```

Exact patch versions come from lockfiles.

Do not silently major-upgrade dependencies.

---

# 23. Architecture Rule

BDJG is a:

```text
MODULAR MONOLITH
+
MONOREPO
```

Do not create:

```text
auth-service
payment-service
project-service
worker-service
media-service
```

as independent network microservices for the MVP.

Backend domain logic remains in one Laravel application.

Queue workers use the same Laravel codebase.

---

# 24. Backend Is Business Authority

The frontend is never the business authority.

Frontend may:

- display controls;
- hide irrelevant controls;
- perform client-side validation for UX;
- show optimistic/pending state where safe.

Backend must still enforce:

```text
authentication
permission
resource relationship
resource state
input validation
business transition
financial calculation
payment truth
file visibility
```

A hidden button is not security.

---

# 25. Deny by Default

Protected data is denied until authorization proves access.

Applies to:

```text
client
quotation
invoice
payment
project
task
schedule
worker data
files
preview
revision
finance
audit
settings
```

Do not implement:

```php
if ($user->role) {
    return $resource;
}
```

Resource access requires the appropriate authorization rule.

---

# 26. Canonical Security Roles

Canonical security roles:

```text
OWNER
ADMIN
WORKER
CLIENT
```

`Visitor` is unauthenticated/public scope, not a normal privileged application role.

Worker profession such as:

```text
Photographer
Videographer
Editor
Colorist
Motion Designer
```

is domain/profile metadata.

Profession is **not** a security role.

Do not create dozens of profession-based security roles.

---

# 27. Single Guard Rule

Use the canonical first-party browser authentication guard:

```text
web
```

Do not create separate guards merely for:

```text
owner
admin
worker
client
```

Role separation belongs to authorization, not separate browser guard duplication.

---

# 28. Authentication Rule

First-party Next.js portal authentication uses:

```text
Laravel Sanctum stateful SPA authentication
+
Laravel Fortify
+
Laravel web guard
+
session cookies
+
CSRF protection
```

Do not introduce custom browser JWT access/refresh tokens.

Do not move authentication authority into Next.js.

Do not store first-party auth tokens in localStorage as a replacement for the approved session architecture.

---

# 29. Fortify Responsibility

Use Fortify for the approved headless authentication capabilities such as:

```text
login
logout
password reset
email verification
other enabled account authentication features
```

Do not duplicate Fortify behavior manually unless the technical architecture explicitly requires customization.

Frontend owns UI.

Laravel owns authentication execution.

---

# 30. Account State Rule

Account lifecycle and worker lifecycle are different concepts.

User/account states may include:

```text
INVITED
ACTIVE
SUSPENDED
DISABLED
```

Worker profile states may include:

```text
ACTIVE
INACTIVE
ON_LEAVE
```

Do not use Worker profile status as a substitute for authentication account status.

Suspended/disabled account access must be enforced on the backend.

---

# 31. Authorization Architecture

Authorization is:

```text
Spatie Permission
+
Laravel Gate
+
Laravel Policy
+
resource relationship
+
resource state
```

Spatie handles:

```text
role
permission
role ↔ permission
user ↔ role
optional direct permission only if explicitly needed
```

Spatie does not decide:

```text
client owns project
worker assigned to project
quotation belongs to client
invoice belongs to client
task belongs to worker
project is in valid state
```

Those are domain relationships and Policy rules.

---

# 32. No Custom RBAC Engine

Do not build a custom role/permission engine from scratch.

Do not introduce parallel tables that duplicate Spatie concepts unless a documented requirement needs additional domain data.

Do not enable Spatie Teams for MVP without explicit architectural decision.

BDJG is initially one studio organization, not a multi-tenant SaaS for multiple studios.

---

# 33. Owner Super Admin Rule

`OWNER` is the highest system role.

Use the approved global Gate interception pattern:

```text
Gate::before
```

for Super Admin authorization behavior.

Do not scatter:

```php
if ($user->hasRole('OWNER')) { ... }
```

through controllers and services.

Owner override does not bypass audit requirements.

Sensitive Owner actions remain auditable.

---

# 34. Permission-First Application Code

Prefer:

```php
$user->can('projects.update')
```

and Policy authorization.

Avoid using role names as the primary feature check throughout application code.

Roles group permissions.

Permissions express capabilities.

Policies combine capabilities with resource/domain conditions.

---

# 35. Client Authorization Invariant

Client scope is:

```text
OWN
```

A Client must never receive another Client's protected:

```text
profile
quotation
invoice
payment
project
schedule
file
preview
revision
message
final delivery
```

Test direct URL / public-ID tampering.

Do not filter forbidden data only after retrieving it to the browser.

Scope the backend query/resource.

---

# 36. Worker Authorization Invariant

Worker scope is:

```text
ASSIGNED
```

A Worker gains project access through active assignment or another explicitly documented relationship.

Worker must not receive:

```text
unassigned projects
client payment
invoice
profit
general client directory
unrelated internal notes
unrelated worker cost
Owner/Admin system controls
```

When assignment is removed, protected project access must be revoked predictably.

---

# 37. Admin Authorization Invariant

Admin scope is operational and permission-driven.

Admin does not automatically receive every sensitive Owner capability.

Sensitive capabilities such as:

```text
finance
profit
user/role management
system settings
content publication
administrative override
```

must follow explicit permission design.

Do not equate:

```text
ADMIN = OWNER
```

---

# 38. Policy Rule

For resource authorization, evaluate what is applicable:

```text
authenticated?
+
permission?
+
relationship?
+
resource state?
+
visibility?
```

Example:

```text
Worker requests Project X
↓
has projects.view?
↓
active assignment to Project X?
↓
project/resource state permits action?
↓
ALLOW
```

If any mandatory condition fails:

```text
DENY
```

---

# 39. Negative Authorization Tests Are Mandatory

Every sensitive capability requires negative tests.

At minimum when applicable:

```text
unauthenticated user denied
wrong role denied
missing permission denied
wrong client denied
unassigned worker denied
wrong resource denied
wrong state denied
suspended/disabled user denied
```

Do not only test successful authorization.

---

# 40. Database Authority

Database schema changes must be source-controlled through Laravel migrations.

If persistence changes, update applicable:

```text
migration
model
casts
relationships
factory
seeder
test
API resource
OpenAPI
```

Do not make a manual database-only change and leave source code behind.

---

# 41. Migration Safety

Before considering a migration complete:

- fresh migration succeeds in test/development;
- seeders still work;
- foreign keys are correct;
- unique constraints are reviewed;
- indexes match real lookup patterns;
- destructive implications are reviewed.

Do not make destructive production-oriented migration changes casually.

If data loss is possible, stop and surface the risk.

---

# 42. Naming Rule

Use domain language from:

```text
PRD
blueprint
TECHNICAL
```

Do not rename canonical business concepts merely for developer preference.

Prefer existing names such as:

```text
Inquiry
Quotation
Invoice
PaymentTransaction
Project
ProjectAssignment
Task
Schedule
Revision
AuditLog
```

Avoid generic or misleading names such as:

```text
Thing
ItemData
GenericRecord
ManagerManager
```

Business names should remain recognizable to the product team.

---

# 43. Public Identifier Rule

Protected routes should follow the repository's public-ID strategy.

Do not expose sequential internal numeric IDs merely because it is convenient.

Authorization is still required even when public IDs are unguessable.

Unpredictable IDs are not an authorization control.

---

# 44. Money Rule

Money values must follow the canonical database/technical strategy.

Do not use floating-point arithmetic for authoritative financial values.

Authoritative amounts are calculated or validated server-side.

Do not trust browser-supplied:

```text
invoice total
discount
DP amount
paid amount
final amount
provider amount
```

without server-side business validation.

---

# 45. Canonical Status Rule

Use canonical status vocabulary from repository documents.

Do not:

- invent new status;
- rename status casually;
- use free-text status;
- silently collapse two distinct states;
- reuse provider status directly as domain status without mapping.

Canonical status changes require documentation review and migration/test impact review.

---

# 46. State Transition Rule

State changes must happen through explicit domain actions/services.

Do not implement generic:

```text
PATCH resource
status = anything
```

for controlled lifecycle state.

Use an allowed transition map or equivalent explicit logic.

Validate:

```text
actor
permission
resource state
allowed transition
required side effects
audit
```

---

# 47. Controller Rule

Controllers are HTTP adapters.

Controllers should:

```text
receive request
authorize
use validated input
invoke action/service
return API resource/response
```

Controllers should not contain large business workflows.

If a controller becomes the only place a commercial transition is defined, extract the domain action.

---

# 48. Action / Service Rule

Use an Action or approved service boundary for non-trivial business mutations.

Examples:

```text
ChangeInquiryStatus
AcceptQuotation
CreateInvoice
IssueInvoice
ProcessDuitkuPayment
ActivateProject
AssignWorkerToProject
ChangeTaskStatus
ReleasePreview
ReleaseFinalDelivery
```

Do not create interfaces/services with no actual abstraction requirement.

Keep architecture simple.

---

# 49. Repository Pattern Is Optional

Do not introduce repository classes merely because another architecture commonly uses them.

Use Eloquent directly through domain/action boundaries when that is sufficient.

Introduce a repository only when a real abstraction or data access boundary warrants it.

---

# 50. Validation Rule

Use Laravel validation/Form Requests or the project-approved equivalent.

Only validated input enters mutation logic.

Do not rely only on frontend validation.

Validation must cover applicable:

```text
type
requiredness
format
range
enum
ownership-related identifiers
date relationship
money constraints
file metadata
```

---

# 51. Mass Assignment Rule

Review all mutations for mass-assignment exposure.

Do not use unvalidated request payloads in:

```php
$model->update($request->all());
```

for protected business resources.

Explicitly control writable fields.

---

# 52. Serialization Rule

Do not return raw Eloquent models blindly.

Use API Resources / explicit response models to control fields.

Client and Worker serialization requires special review for:

```text
internal notes
finance
profit
worker cost
provider payload
private identifiers
private file metadata
audit internals
```

Data must be absent from unauthorized responses, not merely hidden by CSS.

---

# 53. API Version Rule

Business API routes use the approved version convention:

```text
/api/v1/...
```

Follow route/action semantics from `TECHNICAL.md` and `IMPLEMENTATION.md`.

Do not create duplicate alternative endpoints for the same use case without reason.

---

# 54. API Contract Rule

OpenAPI is part of the implementation contract.

When API behavior changes:

```text
route/controller
Form Request
authorization
API Resource
OpenAPI
generated frontend client/types
tests
```

must be reviewed together.

Do not knowingly leave API contract drift.

---

# 55. Generated API Client Rule

Generated code is not the source of truth.

Do not manually patch generated API client files.

If generated output is wrong:

```text
fix API/source annotation/configuration
↓
regenerate
```

Do not maintain handwritten duplicate response types when generated authoritative types exist.

---

# 56. External Provider Boundary Rule

Provider-specific code must stay behind an integration boundary where a real external provider exists.

Applicable:

```text
Duitku
email provider
WhatsApp provider
object storage
future monitoring provider
```

Do not let provider DTOs/status strings spread through core business models.

Map external state to canonical domain state.

---

# 57. Quotation Version Integrity

Quotation history is commercial history.

A sent/accepted commercial version must not be silently overwritten.

Revision should create a new version according to the approved quotation model.

Preserve:

```text
version
items
price snapshot
accepted version
decision actor
decision timestamp
```

Do not make historical quotations depend on mutable current catalog prices.

---

# 58. Catalog Snapshot Rule

Service/package/add-on master data may change over time.

Historical commercial records must preserve the agreed terms.

Do not make accepted quotation history change because:

```text
package renamed
price changed
add-on changed
service archived
```

Snapshot required commercial values.

---

# 59. Invoice Rule

Invoice state is controlled business state.

Do not allow Client to edit authoritative invoice content.

Do not create DP invoice architecture that requires an already-active Project if the approved flow is:

```text
Accepted Quotation
→ DP Invoice
→ Payment
→ Project Activation
```

Pre-project invoice relationships must follow the implementation plan.

---

# 60. Payment Authority Rule

Payment authority is server-side provider verification.

Never use as canonical payment authority:

```text
return URL
browser redirect
browser resultCode
JavaScript callback alone
client screenshot
manual client claim
```

Canonical payment processing follows:

```text
Duitku callback
→ validate
→ locate transaction
→ verify callback signature
→ verify merchant/order/amount
→ transaction-status check
→ map provider state
→ database transaction
→ update payment
→ update history
→ recompute invoice
→ evaluate project activation
→ audit
```

---

# 61. Duitku Sandbox Rule

Runtime MVP integration uses:

```text
REAL DUITKU SANDBOX
```

Do not create a fake payment gateway as the runtime implementation.

Automated tests may and should fake provider HTTP responses to remain deterministic.

Real sandbox verification is a separate required manual/integration gate.

---

# 62. Duitku Callback Rule

The callback endpoint is a provider endpoint, not a browser-authenticated SPA endpoint.

Implement according to current official Duitku contract and repository technical specification.

The agent must verify:

```text
required fields
signature
merchant order
amount
provider reference where applicable
transaction-status result
canonical mapping
idempotency
locking/concurrency
audit
safe response behavior
```

Never log Duitku API credentials.

---

# 63. Transaction Status Check Rule

When processing payment callback, the approved BDJG design requires a server-side transaction status verification.

Do not skip this because callback payload indicates success.

If verification fails or times out:

```text
do not guess PAID
retain safe canonical state
log sanitized diagnostic context
allow controlled reconciliation/retry
```

Do not aggressively poll Duitku from cron/browser without an approved design.

---

# 64. Payment Idempotency Rule

Provider delivery may occur more than once.

Business result must remain safe.

Duplicate payment callback must not:

```text
create second payment
create second project
double-increment paid amount
duplicate critical history incorrectly
send duplicate critical receipt/action
regress PAID to stale pending/failed state
```

Use appropriate:

```text
unique constraints
database transaction
row locking
idempotent action
business identity
```

---

# 65. External Calls and Database Transactions

Do not keep long external HTTP calls inside database transactions unnecessarily.

Preferred shape where applicable:

```text
validate/prepare
→ external request or provider verification
→ enter short critical DB transaction
→ lock/update canonical records
→ commit
→ dispatch non-critical side effects after commit
```

Payment callback may require provider verification before critical database mutation.

Keep transaction time bounded.

---

# 66. Project Activation Rule

Baseline Release A activation:

```text
Accepted Quotation
+
Required DP Satisfied
=
Project may activate
```

Project activation must be an explicit domain action.

Do not create Project twice if callback is repeated.

Do not activate from browser return URL.

Initial active workflow state follows the approved implementation plan.

---

# 67. Project Snapshot Rule

When a Project is created from an accepted quotation, preserve relevant accepted commercial context.

Do not make the Project's agreed commercial values drift with current catalog edits.

Link to the accepted quotation/version and snapshot fields as required.

---

# 68. Assignment Rule

Worker project access is based on active assignment.

Assignment mutation must be explicit and auditable.

Do not:

- let Worker self-assign;
- treat profession as permission;
- keep access after assignment removal unintentionally.

Assignment is a domain relation, not a Spatie Team.

---

# 69. Task Rule

Worker task updates are limited to permitted assigned work.

Task transition must follow canonical status rules.

Worker task actions do not grant authority over:

```text
invoice
payment
pricing
profit
project ownership
global commercial state
```

unless explicitly approved in a future permission design.

---

# 70. Schedule Visibility Rule

Schedule events can have different visibility.

Do not expose internal events to Client merely because they share a Project.

Worker visibility follows assignment/relevance.

Client visibility must be explicit.

Serialization must enforce visibility.

---

# 71. Audit Is Part of the Business System

Critical actions must be auditable.

At minimum follow the mandatory audit event families from PRD/blueprint/implementation/checklist.

Typical Release A events include:

```text
role assigned/removed
account sensitive state change
inquiry assigned/status changed
quotation sent
quotation revision
quotation accepted/declined
invoice issued/voided
payment status change
manual reconciliation
project activation
project status change
worker assignment/removal
significant task status change
```

Owner override remains auditable.

---

# 72. Audit Is Not Operational Logging

Keep concepts separate:

```text
Audit Log
= business/security history

Application Log
= diagnostics/operations
```

Do not rely on server logs as the only business audit history.

Do not place secrets in either.

---

# 73. Secret Rule

Never commit or expose:

```text
APP_KEY
database password
Redis credential
Duitku API key
session cookie
password
reset token
private provider secret
S3 secret
WhatsApp secret
email provider secret
```

Use environment configuration.

Public frontend environment variables must contain only values safe for browser exposure.

---

# 74. Logging Rule

Use safe structured context when useful:

```text
request_id
user_public_id
resource_public_id
merchant_order_id
event
canonical status
sanitized provider reference
```

Never log:

```text
password
session cookie
full secret header
API key
reset token
full sensitive payment payload
```

Logging must support troubleshooting without becoming a secret store.

---

# 75. Frontend Is Not a Parallel Domain Layer

Next.js may contain presentation logic and frontend orchestration.

Do not duplicate authoritative business transition rules in TypeScript and then trust them instead of Laravel.

If frontend needs to know allowed actions, expose safe server-derived state/permissions where appropriate.

Backend remains authoritative.

---

# 76. Portal Shell Rule

Portal shells exist early in Release A.

They are not proof that domain features are complete.

Allowed early:

```text
navigation shell
layout
loading skeleton
real empty state
permission-aware menu
```

Not allowed as production completion:

```text
fake metrics
fake projects
fake invoices
fake worker assignments
fake payment success
```

---

# 77. No Dummy Dashboard Rule

Production dashboard metrics must come from real database queries.

Forbidden:

```ts
const activeProjects = 12;
const unpaidInvoices = 3;
```

unless inside isolated test fixtures/component development clearly separated from production runtime.

Dashboard completion requires real domain data.

---

# 78. Dashboard Query Rule

Dashboard aggregates should be calculated in the database/query layer.

Do not:

```text
load every row
→ count/filter in PHP/browser
```

for growing operational data.

Review:

```text
indexes
bounded date ranges
pagination where relevant
N+1
authorization scope
```

A metric must be scoped before it reaches the browser.

---

# 79. Design Zone Rule

Every UI belongs to one experience zone.

```text
PUBLIC = EXPERIENCE
CLIENT = TRUST
WORKER = FOCUS
ADMIN = CONTROL
```

Agent must identify the experience zone before implementing a page/component.

Do not apply public-site cinematic behavior indiscriminately to operational dashboards.

---

# 80. Client UI Rule

Client Portal should feel:

```text
premium
calm
structured
clear
controlled
project-first
action-led
```

Prioritize:

```text
project status
next action
payment
preview
revision
delivery
```

Do not show internal-only information for aesthetic completeness.

---

# 81. Worker UI Rule

Worker Workspace should feel:

```text
focused
compact
efficient
low-noise
task-first
deadline-first
```

Prioritize:

```text
today's work
deadline
schedule
assigned project context
relevant files
revision assignment
```

Do not add:

```text
cinematic hero
parallax dashboard
full-screen decorative video
large experimental typography
```

to operational worker views.

---

# 82. Admin UI Rule

Admin / Owner interfaces should feel:

```text
attention-first
data-first
operational
dense but readable
controlled
clear
stable
```

Prioritize:

```text
needs action
urgent
blocked
overdue
status
search
filters
finance attention
schedule attention
```

Do not turn Admin Dashboard into a decorative portfolio interface.

---

# 83. UI State Rule

Applicable production UI must implement:

```text
loading
empty
error
forbidden
normal
success feedback for mutation
disabled/pending mutation state
```

Do not leave generic blank screens when data is empty.

Do not show authorization errors as normal empty data when that would hide a security issue.

---

# 84. Destructive Action UI Rule

Destructive actions require:

```text
clear destructive styling
explicit action label
resource/object context
confirmation where appropriate
impact explanation
```

Prefer:

```text
VOID INVOICE BDJG-INV-0048?
This invoice will no longer accept payment.
```

over:

```text
Are you sure?
```

Backend authorization remains mandatory.

---

# 85. Responsive Rule

Applicable pages must work across intended:

```text
mobile
tablet
desktop
wide desktop
```

Operational interfaces should not become unusable on mobile merely because desktop contains dense tables.

Use responsive patterns from `DESIGN.md`.

Do not invent a second unrelated mobile design language.

---

# 86. Accessibility Rule

Target the accessibility requirements defined in PRD/DESIGN.

Core requirements include applicable:

```text
keyboard access
focus-visible
semantic controls
labels
contrast
status not communicated by color alone
reduced motion
touch usability
error identification
```

Do not treat accessibility as Release D-only work.

Portal controls must be usable during Release A.

---

# 87. Status Visual Rule

Status UI should use:

```text
icon
+
label
+
semantic color
```

Do not communicate important state using color alone.

Use canonical status labels.

Avoid badge overload.

---

# 88. Dependency Policy

Before adding a dependency:

1. check native Laravel/Next.js capability;
2. check existing repository dependencies;
3. confirm compatibility with current versions;
4. check maintenance status;
5. consider security/license impact;
6. document why it is necessary.

Do not add a package merely because the agent knows it better.

Locked/specific packages already approved by project documents are exceptions.

---

# 89. Laravel Convention First

Prefer Laravel conventions before building custom infrastructure.

Use framework capabilities for:

```text
validation
policies
gates
events
jobs
queues
notifications
filesystem
HTTP client
process execution
database transactions
rate limiting
resources
testing
```

when they satisfy the requirement.

Do not create custom framework-like layers without need.

---

# 90. Laravel Boost Rule

If Laravel Boost is available in development:

- it may be used for version-aware Laravel guidance;
- it may help inspect application context;
- it is development-only guidance.

Laravel Boost does not override:

```text
PRD
blueprint
DESIGN
TECHNICAL
IMPLEMENTATION
CHECKLIST
AGENTS
```

Repository rules remain authoritative.

---

# 91. PHP Code Quality

Follow `TECHNICAL.md` code quality rules.

Use PHP 8.5 with strict typing where repository conventions require it.

Prefer:

```php
declare(strict_types=1);
```

in project-owned PHP files according to established codebase conventions.

Use:

- clear domain names;
- typed parameters/returns;
- backed enums for canonical states where approved;
- small focused classes;
- framework conventions.

Avoid clever abstractions that obscure business rules.

---

# 92. TypeScript Rule

Frontend TypeScript should remain strongly typed.

Do not use `any` as an escape hatch for normal API/domain data.

Prefer generated API types where available.

Keep browser-only and server-only code boundaries clear.

Do not expose server secrets through Next.js client bundles.

---

# 93. Next.js Rule

Use Next.js according to the technical architecture.

Respect:

```text
server/client component boundary
browser-only hooks
authenticated data flow
API contract
cache behavior
route/layout ownership
```

Do not bypass Laravel business authority by moving critical business mutations into Next.js server actions unless architecture is explicitly revised.

Laravel API remains the domain/backend authority.

---

# 94. Search / Filter Rule

Server-side search/filter/sort must be bounded.

Use:

```text
allowlisted sort fields
allowlisted filters
page-size maximum
authorization scope before pagination
stable default order
validated query values
```

Do not interpolate arbitrary request values into raw database column/expression names.

---

# 95. Performance Rule

Measure before changing architecture.

During Release A focus on:

```text
correct indexes
pagination
N+1 prevention
bounded queries
reasonable payload
database aggregates
```

Do not add:

```text
Octane
microservices
distributed database
specialized Go service
Kubernetes
```

for hypothetical scale.

---

# 96. Queue Rule

When queues are used, they use Laravel Queue + Redis and are operated through Horizon as approved.

A job must define where applicable:

```text
queue
business identity/idempotency
retry
backoff
timeout
failure handling
after-commit behavior
```

Do not serialize huge model graphs into jobs.

Prefer stable identifiers and re-fetch needed state.

---

# 97. Same Laravel Codebase Rule

Queue worker is not a second backend.

Do not create:

```text
apps/worker
```

as another application for normal queue/media work.

Use:

```text
same apps/api Laravel codebase
+
HTTP process
+
Horizon worker process
```

---

# 98. Media Processing Rule

Release B media processing uses queued jobs.

Never run heavy FFmpeg work inside normal HTTP request lifecycle.

Use controlled process execution.

Validate:

```text
input path
output path
arguments
timeout
cleanup
retry
failure state
idempotency
```

Do not construct unsafe shell strings from raw user input.

---

# 99. File Visibility Rule

Canonical file visibility must follow product/technical documents.

Typical protected classes include:

```text
INTERNAL
CLIENT_SHARED
CLIENT_PREVIEW
FINAL
```

Do not rely on hidden/unguessable URLs.

Authorization must occur before signed URL generation.

Protected storage objects remain private by default.

---

# 100. Large Upload Rule

Large media should use direct object-storage upload when the media phase is implemented.

Preferred flow:

```text
authorize
→ create pending upload intent
→ server generates controlled signed upload URL
→ browser uploads to storage
→ finalize
→ server verifies object/metadata
→ create canonical file record
→ queue derivative if needed
```

Do not trust a browser-supplied arbitrary storage key as ownership proof.

---

# 101. Final Delivery Rule

Do not invent final file release policy.

Final delivery depends on approved:

```text
media approval
release authorization
applicable payment/final-release business rule
```

If final-payment/release policy is unresolved, stop that behavior and record the open decision.

Do not silently decide that final payment is or is not required.

---

# 102. Revision Rule

Revision behavior must follow accepted package/project terms.

Do not invent revision limits.

Revision feedback must preserve applicable:

```text
round
preview version
timestamp
comment
actor
status
assignment
history
```

Release B must not turn revision back into unstructured chat.

---

# 103. Notification Rule

Notification is a side effect, not authority.

Failure to send:

```text
email
WhatsApp
in-app notification
```

must not undo an already committed verified payment/project transition.

Use async dispatch where appropriate.

Retry without duplicating critical messages.

---

# 104. Finance Privacy Rule

Finance/profit data is sensitive.

Never serialize project profit or unrelated internal costs to:

```text
CLIENT
WORKER
```

Admin access is permission-based.

Owner has system-wide finance access subject to audit.

---

# 105. Test Strategy

Testing is part of implementation, not an afterthought.

Use appropriate:

```text
unit
feature/integration
frontend unit/component
browser E2E
manual external-provider verification
```

Most business HTTP flows should receive strong backend feature/integration coverage.

Do not depend only on E2E for business correctness.

---

# 106. Backend Test Requirements

For applicable feature, cover:

```text
happy path
validation failure
unauthenticated access
unauthorized access
wrong resource
invalid state
database post-condition
audit side effect
provider failure
duplicate/retry behavior
```

Use the phase checklist as the exact gate.

---

# 107. Authorization Regression Suite

Once relevant domains exist, these become permanent blockers.

Client must not access another Client's:

```text
profile
quotation
invoice
payment
project
media
```

Worker must not access:

```text
unassigned project
invoice
payment
profit
unassigned media
```

Admin without permission must be denied.

Owner Super Admin path must remain functional and auditable.

---

# 108. Payment Regression Suite

Once Duitku phase exists, permanently protect:

```text
create transaction success
provider timeout
provider error
invalid callback signature
wrong amount
unknown order
valid paid callback
duplicate callback
out-of-order callback
transaction-status failure
return URL cannot mark paid
invoice recomputation
exactly-once project activation
secret redaction
```

Do not remove these tests to make CI green.

---

# 109. Frontend Test Rule

Test user-observable component/page behavior.

Cover where applicable:

```text
loading
empty
error
forbidden
mutation pending
mutation success/failure
permission-aware control
canonical status display
```

Do not use frontend tests as evidence that backend authorization exists.

---

# 110. Playwright Rule

Use Playwright for integrated user outcomes.

Prefer stable semantic locators:

```text
role
label
text tied to user behavior
```

Avoid brittle selectors where semantic locators exist.

Do not add arbitrary sleeps as normal synchronization.

Use deterministic test data.

Cross-role E2E must include direct URL/resource tampering.

---

# 111. Golden Path Rule

Release A must prove the integrated operational path:

```text
Owner/Admin login
→ Inquiry
→ Client
→ Quotation
→ Client login
→ Client owns quotation
→ Acceptance
→ DP Invoice
→ Duitku Sandbox payment
→ callback
→ transaction verification
→ PAID
→ Project activation
→ Worker assignment
→ Worker login
→ assigned project
→ task update
→ Client sees allowed progress
→ Client cross-resource attempt denied
→ Worker unassigned attempt denied
→ audit visible to authorized Owner/Admin
```

Do not call Release A complete without this path.

---

# 112. Live Duitku vs Automated Test Rule

Normal automated CI must be deterministic and should not require live Duitku.

Use HTTP fakes/mocks for provider behavior in automated tests.

Separately, Release A requires a **real Duitku Sandbox round trip**.

Both are required.

One does not replace the other.

---

# 113. CI Rule

All required configured quality gates must pass before completion.

Expected categories:

```text
Composer validation/install
backend tests
Laravel Pint check
pnpm frozen install
frontend lint
frontend typecheck
frontend tests
frontend production build
OpenAPI validation
generated client freshness when committed
```

Do not disable or loosen failing checks simply to get a green pipeline unless the check itself is proven incorrect and the change is reviewed.

---

# 114. Fresh Migration Rule

Before phase/release completion when database is involved:

```text
migrate fresh
+
seed
+
required tests
```

must be proven in the appropriate test/development environment.

Do not only test against a long-lived local database that may hide migration defects.

---

# 115. No Test Deletion to Hide Defects

Do not:

- delete a failing test because code is inconvenient;
- weaken assertions to make failure disappear;
- mark a test skipped without documented reason;
- replace an authorization assertion with a UI-only assertion.

Fix the defect or report a real blocker.

---

# 116. Completion Checklist Is Mandatory

At the end of each phase:

1. open the matching section in `PHASE-COMPLETION-CHECKLIST.md`;
2. evaluate every applicable mandatory item;
3. run required tests;
4. perform required manual verification;
5. record actual evidence;
6. determine exit gate result.

Do not rely on memory of the checklist.

Read it.

---

# 117. Completion Means Evidence

A phase is complete only when:

```text
IMPLEMENTED
+
AUTHORIZED
+
VALIDATED
+
TESTED
+
VERIFIED
+
AUDITABLE
+
DOCUMENTED
=
COMPLETE
```

Visible UI alone does not count.

---

# 118. Evidence Rule

Completion evidence must be factual.

Good:

```text
php artisan test: 142 passed
frontend typecheck: passed
migration: 2026_..._create_projects_table
policy: ProjectPolicy
negative test: ClientCannotSeeOtherClientProject
manual sandbox transaction: sanitized merchant_order_id...
```

Bad:

```text
should work
looks correct
probably fixed
seems complete
```

Never claim a command passed if it was not executed.

---

# 119. Evidence Must Be Sanitized

Do not include:

```text
API key
password
session cookie
secret header
private token
```

in evidence.

Duitku evidence may include only safe/sanitized:

```text
merchant_order_id
provider reference
callback timestamp
canonical status
invoice public ID
project public ID
request/correlation ID
```

---

# 120. Agent Phase Checkpoint Format

At the end of every phase, report using this structure:

```text
PHASE:
STATUS:

IMPLEMENTED:
- ...

DATABASE:
- migrations:
- constraints/indexes:
- seed/factory:

AUTHORIZATION:
- permissions:
- policies:
- isolation tests:

API:
- routes:
- OpenAPI:
- generated client:

FRONTEND:
- routes/screens:
- loading/empty/error/forbidden:

TESTS:
- backend:
- frontend:
- E2E:

MANUAL VERIFICATION:
- ...

SECURITY REVIEW:
- ...

AUDIT/LOGGING:
- ...

KNOWN LIMITATIONS:
- ...

DEFERRED:
- ...

DOCUMENTATION UPDATED:
- ...

EXIT GATE:
PASS / FAIL

BLOCKERS:
- ...
```

Do not report:

```text
STATUS: COMPLETE
EXIT GATE: FAIL
```

That is invalid.

---

# 121. Multi-Phase Task Rule

If explicitly asked to implement multiple phases in one task:

```text
Phase N
→ complete checks
→ exit gate PASS
→ Phase N+1
→ complete checks
→ exit gate PASS
```

Do not implement all phases first and run one generic test at the end.

Each phase must remain independently verifiable.

---

# 122. Blocked Phase Rule

When blocked:

```text
STATUS = BLOCKED
```

Report:

```text
what is blocked
why
which requirement/gate is affected
what evidence confirms the blocker
what can safely continue without bypassing it
```

Do not create insecure placeholders to avoid reporting a blocker.

---

# 123. No-Silent-Assumption Rule

Stop and surface the unresolved decision instead of inventing behavior when it concerns:

```text
final delivery payment/release policy
revision limit not defined by accepted commercial terms
refund authority/workflow
portfolio publication consent
missing canonical state transition
ambiguous permission boundary
destructive data change
provider behavior contradicting current official docs
source-document conflict
tax/legal invoice formatting
security-sensitive account behavior not yet approved
```

A missing product decision is not an invitation for the agent to design one silently.

---

# 124. Open Product Decision Rule

`PRD.md` contains open decisions.

Later technical/implementation documents may legitimately select an implementation for decisions that the PRD intentionally left provider/configuration-open, for example:

```text
Duitku selected as payment provider
Sanctum/Fortify selected as auth implementation
MySQL selected as database
```

This is not automatically a product contradiction.

However, unresolved **business policy** must not be guessed.

Distinguish:

```text
technical selection
vs
business rule
```

---

# 125. Exact DP Policy Rule

The system may support DP invoice in Release A.

Do not invent a universal DP percentage if the business default is not documented.

Keep approved terms configurable/snapshotted at quotation level as required.

Test fixtures may use explicit fixture amounts.

A test fixture is not a product default.

---

# 126. Provider Documentation Verification Rule

For external integrations and version-sensitive framework behavior, consult current official documentation when needed.

Preferred source order:

```text
official Laravel docs
official Spatie docs
official Duitku docs
official Next.js docs
official Playwright docs
official package docs
```

Avoid basing payment/security behavior on random blog posts.

Repository requirements still override generic framework examples.

---

# 127. Current Duitku Verification Principle

Before modifying payment integration, confirm the current official Duitku contract for the endpoint being used.

Pay special attention to:

```text
sandbox endpoint
headers/signature
callback signature
transaction-status signature
status codes
request content type
deprecated algorithms
rate-limit notes
```

Do not blindly copy an old Duitku integration from an unrelated product/API variant.

---

# 128. Provider Variant Rule

Duitku documentation may contain different product/API variants.

Do not mix formulas/endpoints from:

```text
POP
legacy API
direct credit card
subscription
other product
```

without confirming which integration BDJG uses.

Implement the exact selected Duitku product contract.

If repository specification and official provider documentation materially disagree, stop payment mutation work and report the discrepancy.

---

# 129. Security Before Convenience

When choosing between:

```text
fast shortcut
vs
correct authorization/payment/data handling
```

choose correctness.

Do not:

- disable CSRF;
- make protected storage public;
- skip callback verification;
- bypass Policy;
- trust browser amount;
- remove isolation tests;

to make local development easier.

Create a proper development setup instead.

---

# 130. Public Callback Development Rule

When testing real Duitku Sandbox locally, callback endpoint must be reachable by Duitku through an approved public HTTPS development path.

Do not:

- expose unrelated local admin services;
- place credentials in tunnel URL;
- mark payment paid manually when callback cannot reach local environment.

If callback infrastructure is unavailable:

```text
Phase 10 remains IN_PROGRESS/BLOCKED for manual sandbox sign-off
```

Automated fake-provider tests may still proceed.

---

# 131. Error Handling Rule

Differentiate:

```text
validation error
authentication error
authorization error
not found
invalid state
provider failure
internal application failure
```

Do not leak stack traces/secrets in production API responses.

Preserve enough internal correlation context for diagnostics.

---

# 132. Forbidden vs Not Found

For protected resources, use the approved API/security convention consistently.

Do not expose sensitive resource existence unnecessarily.

Whatever pattern is selected, tests must assert no protected metadata leaks.

---

# 133. Rate Limiting Rule

Apply rate limiting according to `TECHNICAL.md` and current phase.

Sensitive/public endpoints may include:

```text
login
password reset
public inquiry
provider webhook as appropriately designed
expensive endpoints
```

Do not use rate limits that break legitimate provider callback delivery without provider-aware design.

---

# 134. Cache Rule

Do not cache protected resource data without considering authorization scope.

Do not cache stale permission-sensitive responses globally.

Cache only when the technical design and invalidation behavior are clear.

Correctness first.

---

# 135. Environment Rule

Environment-specific values belong in configuration/environment files.

Keep:

```text
sandbox
production
local
test
```

behavior explicit.

Do not use environment name as a substitute for business authorization.

Do not hardcode production provider endpoints in domain code.

---

# 136. Test Data Rule

Use deterministic factories/seeders for tests.

Test scenarios should deliberately include:

```text
Owner
Admin with permissions
Admin without sensitive permission
Client A
Client B
Worker A assigned
Worker B unassigned
accepted quotation
issued invoice
pending/paid payment
active project
tasks/schedule
```

Do not rely on random test data where exact business state matters.

---

# 137. Seeder Rule

Seeders must be idempotent where expected.

Role/permission seeding must not create duplicates on rerun.

Seed data must not become a hidden production business default unless explicitly designed as configuration.

---

# 138. OpenAPI Freshness Rule

If API types are generated and committed:

```text
source contract changes
→ regenerate
→ verify diff
→ run frontend typecheck/build
```

CI should fail if generated client is stale when that check is configured.

---

# 139. Documentation Synchronization Rule

When a locked architectural or execution decision changes, review:

```text
TECHNICAL.md
IMPLEMENTATION.md
PHASE-COMPLETION-CHECKLIST.md
AGENTS.md
OpenAPI
.env.example
README
```

Do not silently change implementation behavior while leaving source documents contradictory.

Do not edit PRD/business rules simply to match code unless a product decision has actually changed.

---

# 140. Documentation Editing Rule

The agent may update technical/execution docs when:

- the task explicitly requests it; or
- a code change requires synchronization of a previously locked technical/execution decision.

The agent must not casually rewrite:

```text
PRD
blueprint
DESIGN
```

to justify its implementation.

If product/business/design change is required, surface it.

---

# 141. Existing Code First

Before creating a new:

```text
model
action
service
policy
component
hook
utility
route
package
```

search the repository for an existing implementation or naming convention.

Reuse established patterns when they remain correct.

Do not create duplicates because search was skipped.

---

# 142. Search Before Replace

Before renaming or replacing a domain symbol:

```text
search all references
inspect migrations
inspect tests
inspect OpenAPI
inspect generated client
inspect UI
inspect docs
```

Canonical names often span multiple layers.

Do not partial-rename a business concept.

---

# 143. Do Not Edit Generated Code

Generated output must be regenerated from its source.

Examples may include:

```text
OpenAPI TypeScript client
generated API types
framework-generated caches/build artifacts
```

Do not manually modify generated output as the primary fix.

---

# 144. No Premature Abstraction

Do not create:

```text
BaseGenericCrudRepository
UniversalStatusManager
AbstractProviderFactoryFactory
GenericBusinessEngine
```

without a real recurring requirement.

Business clarity is more important than maximizing abstraction.

---

# 145. No Premature Microservices

Do not add distributed architecture based on hypothetical future scale.

Only consider major architecture change after:

```text
measured bottleneck
+
explicit architecture decision
+
migration plan
+
full regression review
```

---

# 146. No Silent Framework Upgrade

Do not major-upgrade:

```text
Laravel
Next.js
React
Node
PHP baseline
Spatie Permission
OpenAPI tooling
```

without explicit task/decision.

Patch/minor dependency changes should still respect lockfile and compatibility policy.

---

# 147. Package Lockfile Rule

Do not manually edit lockfiles.

Use the appropriate package manager.

Keep:

```text
composer.lock
pnpm-lock.yaml
```

consistent with dependency manifests.

Use frozen lockfile behavior in CI.

---

# 148. Development Command Rule

Use actual repository scripts as the authority.

Typical Laravel checks may include:

```text
composer validate
php artisan test
./vendor/bin/pint --test
php artisan migrate:fresh --seed
php artisan route:list
```

Typical frontend checks may include:

```text
pnpm install --frozen-lockfile
pnpm lint
pnpm typecheck
pnpm test
pnpm build
```

Workspace script names may differ.

Inspect `package.json` / `composer.json` before assuming exact command aliases.

Do not claim a command ran when it did not.

---

# 149. Phase 0 Gate Rule

Before Phase 0 completion, prove:

```text
Laravel boots
Next.js boots
MySQL reachable
Redis reachable
health endpoint works
tests run
lint/typecheck/build commands run
fresh clone setup is documented
```

Do not continue with business modules on an unstable foundation.

---

# 150. Phase 1 Gate Rule

Before Authentication phase completion, prove:

```text
CSRF bootstrap
login
invalid login rejection
logout
/me
password reset
email verification behavior
session restore
suspended/disabled enforcement
```

No first-party JWT architecture.

---

# 151. Phase 2 Gate Rule

Before RBAC phase completion, prove:

```text
Spatie installed
roles seeded
permissions seeded
web guard
Owner Gate::before
Policies
positive authorization tests
negative authorization tests
Client isolation pattern
Worker assignment pattern
```

Business dashboard features must not build on untested authorization.

---

# 152. Phase 3 Gate Rule

Portal shells must:

```text
exist
navigate
respect session
show correct experience zone
show real empty/loading/error/forbidden states
not pretend later modules exist
```

Do not call a shell a feature implementation.

---

# 153. Catalog Gate Rule

Catalog must be stable enough to support commercial snapshots.

Do not let historical quotations reference mutable prices without snapshot protection.

---

# 154. Client Gate Rule

Client business entity and login User are related but not the same conceptual entity.

Do not collapse them if the repository model allows a client organization to have account relationships.

Client ownership must be explicit.

---

# 155. Inquiry Gate Rule

Inquiry status changes use explicit state transitions.

Do not allow generic arbitrary status patching.

Admin dashboard inquiry metrics must come from real data.

---

# 156. Quotation Gate Rule

Before completion:

```text
versioning works
sent/accepted history immutable
client ownership enforced
accept/revision-request/decline states enforced
accepted version recorded
commercial snapshot preserved
DP invoice becomes eligible
project is not activated prematurely
```

---

# 157. Client Activation Gate Rule

Invitation/account activation must prevent:

```text
duplicate accounts
token reuse
expired token abuse
wrong client association
```

Client portal access remains ownership scoped.

---

# 158. Invoice Gate Rule

Invoice must support the approved pre-project DP flow.

Client may view own invoice but cannot edit authoritative invoice state.

Amounts remain server authoritative.

---

# 159. Duitku Gate Rule

Phase 10 cannot be `COMPLETE` until:

```text
automated provider tests pass
+
real Duitku Sandbox round trip passes
```

Live sandbox verification must prove:

```text
transaction creation
payment URL
payment completion
callback receipt
signature verification
transaction-status verification
canonical payment update
invoice recomputation
idempotency
audit
secret redaction
```

---

# 160. Project Activation Gate Rule

Project activation must prove:

```text
no activation before required DP
verified DP activates
browser return does not activate
duplicate callback activates once
accepted quotation snapshot preserved
```

Exactly-once business outcome is mandatory.

---

# 161. Worker Gate Rule

Worker profile is not security role proliferation.

Worker project access must depend on assignment.

Removed assignment removes access.

---

# 162. Task Gate Rule

Task status transitions are controlled.

Assigned worker may update allowed tasks.

Unassigned or unrelated worker is denied.

Task action cannot mutate commercial state.

---

# 163. Schedule Gate Rule

Visibility must distinguish:

```text
internal
client-visible
worker relevant
```

Do not leak internal events into Client API.

---

# 164. Worker Portal Gate Rule

Worker dashboard data must be real and assignment scoped.

A Worker must be unable to access an unassigned project even by direct URL.

Finance is absent.

---

# 165. Client Portal Gate Rule

Client dashboard/project data must be real and ownership scoped.

Direct URL tampering to another client resource must fail without leaking metadata.

Internal finance/notes remain absent.

---

# 166. Admin Dashboard Gate Rule

Admin Dashboard must use real operational data.

It must answer attention/control questions.

Do not show a metric for a module that is not yet implemented unless explicitly marked unavailable and not presented as real production data.

---

# 167. User & Role Management Gate Rule

Do not allow ordinary Admin to:

```text
self-promote to OWNER
grant OWNER without approved authority
bypass role-management permission
```

Role changes must be audited.

---

# 168. Audit Gate Rule

Audit storage is append-oriented.

Normal application behavior must not rewrite or delete history casually.

Sensitive data is excluded.

Audit access is permission-protected.

---

# 169. Search/Filter Gate Rule

Search/filter must respect authorization before pagination.

A Client or Worker must not discover forbidden records through search count, autocomplete, filter options, or error metadata.

---

# 170. Release A Gate Rule

Do not start Release B until the **Release A Final Completion Gate** in the checklist passes.

If any of these remain defective:

```text
client isolation
worker isolation
payment verification
payment idempotency
exactly-once activation
accepted quotation integrity
role escalation protection
auditability
```

fix Release A first.

---

# 171. Release B Gate Rule

Do not begin media/revision/final-delivery features before Release A completion unless the project documents are explicitly revised.

Release B completion requires protected media boundaries, queue/FFmpeg correctness, and client release controls.

---

# 172. Release C Gate Rule

Notification and finance expansion must not weaken already-completed transactional boundaries.

External notification failure must remain isolated from committed business state.

Finance privacy remains strict.

---

# 173. Release D Gate Rule

Public website uses the stable operational backend.

`Book a Project` should feed the existing Inquiry domain/API.

Do not build a separate public lead database/workflow.

Project completion does not automatically authorize public portfolio publication.

---

# 174. Design Completion Rule

A UI phase also requires design QA.

Review applicable:

```text
typography
spacing
alignment
grid
contrast
media crop
hover
focus
loading
empty
error
disabled
selected
responsive
reduced motion
status clarity
CTA hierarchy
operational efficiency
```

Do not call UI complete because desktop happy path looks correct.

---

# 175. Accessibility Completion Rule

Applicable phase completion includes accessibility verification required by the checklist/design.

Do not defer all accessibility work to the final release.

Fix structural issues while the component is being built.

---

# 176. Security Completion Rule

At release gates verify:

```text
authentication
authorization matrix
IDOR
CSRF
webhook authenticity
validation
mass assignment
serialization
secret handling
rate limiting
audit
file visibility when applicable
safe process execution when applicable
```

Known P0/P1 blocks release completion.

---

# 177. Performance Completion Rule

At applicable gates verify:

```text
pagination
indexes
database dashboard aggregates
N+1
external-call repetition
queue for long work
direct media upload when applicable
explicit job timeout/retry
measured need before architecture expansion
```

---

# 178. Observability Completion Rule

At applicable gates verify:

```text
important errors logged
correlation context
payment callback traceability
provider failure distinguishable
queue failure visible when queues active
health checks valid
credentials absent from logs
```

---

# 179. Manual Verification Is Real Verification

When checklist requires manual verification:

- actually perform it when environment/tooling permits;
- record what was tested;
- record actual result.

Do not replace required manual verification with code inspection.

If environment prevents it, phase remains not fully complete.

---

# 180. Browser Verification Rule

When implementing portal UI, manually verify where applicable:

```text
login
direct protected URL
refresh
loading
empty
error
forbidden
mutation
responsive
keyboard
```

Do not test only through API and assume the browser flow works.

---

# 181. User-Facing Error Rule

Errors should help the intended user recover when possible.

Do not display raw:

```text
SQL errors
PHP stack traces
provider secrets
internal exception class
```

to end users.

Use clear domain-appropriate error messages.

---

# 182. Permission-Aware Navigation Rule

Frontend navigation may hide links users cannot use.

But direct URL/API access must still be denied by backend.

Navigation visibility is UX.

Policy/Gate is security.

---

# 183. Cross-Role Data Leak Review

Whenever an API Resource or query changes, ask:

```text
Could Client receive another Client's data?
Could Worker receive unassigned project data?
Could Client/Worker receive finance?
Could internal notes leak?
Could private provider payload leak?
Could hidden media leak?
```

Add regression test where risk exists.

---

# 184. Critical Mutation Review

For every critical mutation ask:

```text
Who can do this?
On which resource?
In which state?
Is it retryable?
Can it happen twice?
Does it need DB transaction?
Does it need audit?
Does it trigger external side effects?
Can side effects duplicate?
```

Examples:

```text
quotation accept
invoice issue
payment update
project activation
worker assignment
project state change
final release
refund
role change
```

---

# 185. Browser Refresh / Direct Navigation Rule

SPA experience must remain correct on direct navigation and browser refresh.

Do not rely only on client-side state accumulated from previous pages.

Protected route behavior must remain secure on fresh request.

---

# 186. No Hidden Production Placeholder

Do not ship temporary fake behavior disguised as production.

If a feature is deferred:

- hide unavailable action; or
- clearly mark it unavailable in an approved development context.

Do not create buttons that imply a feature works when it does not.

---

# 187. Temporary Development Utilities

A dev-only fixture/action may exist only if:

```text
clearly development-only
not reachable in production
does not become business authority
does not replace required provider integration
is documented
```

Do not use dev utilities to satisfy a production completion gate.

---

# 188. Provider Failure Rule

External providers can fail.

Code must distinguish provider failure from successful business state.

Do not mutate canonical state optimistically when provider confirmation is required.

Do not retry non-idempotent effects blindly.

---

# 189. Destructive Data Rule

Before destructive operation implementation:

```text
verify product permission
verify resource state
verify audit
verify historical retention rule
verify confirmation UX
verify database effects
```

Finance/payment/audit history must not be hard-deleted through normal operational flow without explicit requirement.

---

# 190. Public Site Reference Rule

When Release D begins, visual references such as Mondragon are inspiration only.

Do not clone:

```text
exact layouts
copyrighted assets
unique branded compositions
```

Use BDJG's own design language.

Public site may be creative.

Portal remains functional.

---

# 191. Agent External Research Rule

If current framework/provider behavior must be verified:

- prefer primary/official documentation;
- confirm version/product variant;
- do not change repository requirements merely because a tutorial uses another architecture.

For security/payment-sensitive external facts, verify rather than rely on memory.

---

# 192. No Conversation Memory as Authority

Previous chat messages can explain intent but repository documents and explicit current user decisions control implementation.

If conversation memory and repository differ:

1. inspect repository;
2. identify whether an explicit newer decision exists;
3. synchronize documentation when authorized;
4. do not silently choose based on memory.

---

# 193. No Fabricated Completion

Never fabricate:

```text
test results
sandbox payment result
callback receipt
CI status
migration success
build success
security review
manual browser verification
```

If not run:

```text
NOT RUN
```

If unavailable:

```text
BLOCKED / NOT VERIFIED
```

Truthful incomplete status is better than false completion.

---

# 194. Completion Report Must Mention Failures

If a test/check fails, include it.

Do not hide failures behind:

```text
implemented successfully
```

Report:

```text
command/check
actual failure
affected gate
whether feature remains IN_PROGRESS/BLOCKED
```

---

# 195. Minimum Final Task Report

For a normal feature task that is smaller than a full phase, report:

```text
Requirement/domain:
Phase:
Files changed:
Behavior implemented:
Authorization:
Tests run:
Tests passed/failed:
Manual verification:
Known limitations:
Checklist impact:
Next required step:
```

For full phase completion, use the exact Phase Checkpoint Format.

---

# 196. Stop Conditions

Stop the affected behavior and surface the issue when:

```text
source documents materially conflict
required business policy is unresolved
security boundary is ambiguous
payment provider contract conflicts with implementation
destructive migration may lose data
canonical status transition is undefined
authorization cannot be determined
required secret/credential/environment is unavailable for mandatory verification
```

Do not stop unrelated safe work unnecessarily.

Do not invent the missing decision.

---

# 197. What "Stop" Means for an AI Agent

"Stop" does not mean abandon the entire repository task.

It means:

1. do not implement the ambiguous/unsafe business decision;
2. complete other safe work in scope if independent;
3. clearly mark blocker;
4. record affected phase/checklist item;
5. do not claim phase completion.

---

# 198. Core Prohibitions

The agent MUST NOT:

- invent business rules;
- invent canonical status;
- skip phase gate;
- call untested work complete;
- implement public site before Release A just for appearance;
- replace Laravel backend;
- replace MySQL baseline silently;
- create custom first-party JWT browser auth;
- create custom RBAC engine instead of Spatie;
- enable Spatie Teams without explicit decision;
- use multiple guards merely for roles;
- authorize only from frontend;
- scatter Owner role bypasses;
- expose another Client's data;
- expose unassigned Project to Worker;
- expose finance/profit to Client/Worker;
- trust return URL for payment;
- trust payment screenshot;
- skip Duitku signature verification;
- skip transaction-status verification;
- create duplicate Project on repeated callback;
- overwrite accepted Quotation version;
- hardcode production dashboard metrics;
- create a second backend worker application;
- process FFmpeg in request lifecycle;
- expose protected object storage publicly;
- accept arbitrary storage key as ownership proof;
- add dependency only from familiarity;
- edit generated API client manually;
- change DB contract without migration/test;
- delete audit/financial history casually;
- hide failing tests;
- fabricate verification evidence;
- silently modify product documents to justify code.

---

# 199. Core Positive Rules

The agent MUST:

- read source documents;
- trace requirement;
- find current phase;
- inspect existing code before adding new code;
- preserve user changes;
- implement minimal correct scope;
- enforce backend authorization;
- validate inputs;
- use explicit domain transitions;
- use DB transactions for critical multi-record changes;
- design retry/idempotency where delivery can repeat;
- protect payment truth;
- protect client ownership;
- protect worker assignment boundaries;
- protect finance;
- protect files;
- update OpenAPI when API changes;
- update tests with code;
- run applicable quality checks;
- read the phase checklist before claiming completion;
- record real evidence;
- synchronize technical/execution docs when a locked decision changes.

---

# 200. Final Agent Operating Algorithm

For every implementation request:

```text
A. INTAKE
   ↓
Read user request.

B. LOCATE
   ↓
Find relevant PRD requirement.
Find blueprint rule.
Find design rule if UI.
Find technical rule.
Find implementation phase.
Find completion checklist.

C. INSPECT
   ↓
Inspect repository.
Inspect current code.
Inspect current migrations.
Inspect tests.
Inspect git status.
Determine earliest missing dependency.

D. PLAN
   ↓
Define smallest correct change.
Define authorization.
Define state transition.
Define data changes.
Define API contract.
Define tests.
Define manual verification.

E. IMPLEMENT
   ↓
Backend authority first where business behavior is involved.
Then API contract.
Then frontend integration.
Then audit/side effects.

F. TEST
   ↓
Happy path.
Validation.
Unauthorized.
Wrong resource.
Wrong state.
Retry/duplicate where applicable.
Frontend states.
Build/type/lint.

G. VERIFY
   ↓
Manual/browser/provider checks required by phase.

H. CHECKLIST
   ↓
Read applicable PHASE-COMPLETION-CHECKLIST section.
Evaluate every mandatory item.

I. REPORT
   ↓
Record actual evidence.
Declare PASS/FAIL.
Never hide blockers.

J. ADVANCE
   ↓
Only after exit gate PASS may next phase begin.
```

---

# 201. Final Project Invariant

BDJG is successful only when its complete business journey is:

```text
reliable
authorized
auditable
understandable
testable
recoverable
secure
maintainable
recognizably BDJG
```

The agent's goal is not:

```text
MAXIMUM NUMBER OF FILES
```

or:

```text
MAXIMUM NUMBER OF VISIBLE PAGES
```

The goal is:

```text
CORRECT BUSINESS FLOW
+
CORRECT ACCESS
+
CORRECT STATE
+
CORRECT DATA
+
CORRECT PAYMENT
+
CORRECT UI FOR THE ROLE
+
PROOF
```

The final rule is:

```text
DO NOT SKIP.
DO NOT INVENT.
DO NOT BYPASS.
DO NOT DRIFT.
DO NOT CLAIM COMPLETE WITHOUT EVIDENCE.
```

---

# 202. Repository Completion Principle

When uncertain whether to continue:

```text
Can the requirement be traced?
Can the actor be authorized?
Can the state change be proven valid?
Can the data boundary be proven safe?
Can the phase gate be proven passed?
```

If yes:

```text
continue
```

If no:

```text
do not guess
→ record the blocker
→ resolve the missing authority
```

---

**END — BDJG AGENTS.md**
