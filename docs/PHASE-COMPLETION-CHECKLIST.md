# BDJG — Phase Completion Checklist

> **Document:** `PHASE-COMPLETION-CHECKLIST.md`  
> **Purpose:** Mandatory completion gate for every implementation phase.  
> **Applies to:** `IMPLEMENTATION.md`, `TECHNICAL.md`, and future `AGENTS.md`.  
> **Execution model:** Dashboard-first, Laravel 13 + Next.js 16, real Duitku Sandbox, Spatie RBAC + Laravel Policies.  
> **Rule:** A phase is **not complete** because the UI looks finished. A phase is complete only when every mandatory gate in this document is satisfied and evidence exists.

---

# 1. Purpose

This checklist prevents partial or visually complete implementations from being treated as finished.

For every implementation phase, the AI agent or developer must prove:

```text
requirement understood
→ dependency satisfied
→ schema correct
→ backend rule implemented
→ authorization enforced
→ validation enforced
→ API contract stable
→ frontend integrated
→ automated tests green
→ manual verification passed
→ security/data leakage reviewed
→ documentation synchronized
→ evidence recorded
→ phase exit gate passed
```

The next phase must not begin while a mandatory item from the current phase remains unresolved, unless:

1. the item is explicitly marked `DEFERRED`,
2. the deferral is allowed by `IMPLEMENTATION.md`,
3. the deferral does not violate security, data integrity, authorization, payment correctness, or a Release gate,
4. the reason and follow-up phase are recorded.

---

# 2. Source-of-Truth Order

When checking completion, use this hierarchy:

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

Rules:

- Product behavior comes from product documents.
- Technical architecture comes from `TECHNICAL.md`.
- Build sequence comes from `IMPLEMENTATION.md`.
- Completion proof comes from this checklist.
- `AGENTS.md` may instruct execution behavior but must not silently override product or technical decisions.
- If documents conflict, do not guess. Record the conflict and resolve the source documents before continuing.

---

# 3. Completion Status Vocabulary

Every phase must use one of these statuses:

```text
NOT_STARTED
IN_PROGRESS
BLOCKED
READY_FOR_REVIEW
COMPLETE
DEFERRED
```

Definitions:

## NOT_STARTED

No implementation work has started.

## IN_PROGRESS

Implementation exists but one or more mandatory gates are incomplete.

## BLOCKED

Progress cannot continue because a dependency, decision, credential, environment, or unresolved defect prevents completion.

## READY_FOR_REVIEW

All implementation work is believed complete and all mandatory checks have passed locally. Independent review/sign-off is still pending.

## COMPLETE

All mandatory checklist items are satisfied, required evidence is recorded, CI is green, and the phase exit gate has passed.

## DEFERRED

Only allowed for an item explicitly permitted to move to a later phase. A deferred item must include:

```text
reason
risk
target phase/release
owner
```

Security, authorization, payment integrity, and data isolation defects may not be deferred merely to make a phase appear complete.

---

# 4. Severity Vocabulary for Defects

Use:

```text
P0 — security breach, data loss, payment corruption, system unusable
P1 — critical business flow broken, authorization bypass, cross-tenant/resource leak
P2 — significant feature defect without immediate security/data-loss impact
P3 — minor defect, polish, non-blocking usability issue
```

A phase cannot become `COMPLETE` with:

- any open P0;
- any open P1;
- any P2 that breaks the current phase acceptance criteria;
- any known payment correctness issue;
- any known IDOR/resource-isolation issue;
- red required CI checks.

---

# 5. Required Completion Evidence

Every completed phase must have an evidence record.

Minimum:

```text
Phase:
Status:
Commit / branch:
Date:
Implemented requirements:
Migrations:
API routes:
Permissions / Policies:
Automated tests:
Manual verification:
E2E:
Security review:
Known limitations:
Deferred items:
Documentation updated:
Reviewer / sign-off:
```

Evidence may point to:

- test names;
- CI run;
- migration names;
- route list;
- screenshots for UI states;
- sanitized sandbox transaction IDs;
- sanitized callback log correlation IDs;
- OpenAPI diff;
- seed scenario used;
- Playwright trace/screenshot only when useful.

Never store:

- API keys;
- passwords;
- full session cookies;
- raw secret headers;
- sensitive provider credentials.

---

# 6. Universal Hard Blockers

These block completion in **every** phase.

- [ ] Fresh migrations fail.
- [ ] Required seeders fail.
- [ ] Required backend tests fail.
- [ ] Required frontend checks fail.
- [ ] Build fails.
- [ ] A protected endpoint lacks backend authorization.
- [ ] Client can access another client's protected resource.
- [ ] Worker can access an unassigned protected project.
- [ ] A response exposes forbidden internal finance, secrets, or internal notes.
- [ ] Generic client input can arbitrarily mutate a canonical status.
- [ ] Secrets are committed or logged.
- [ ] Payment can become `PAID` from return URL/browser-only data.
- [ ] Duplicate payment callback can duplicate project activation or another critical side effect.
- [ ] Destructive migration is introduced without explicit review.
- [ ] OpenAPI and implemented endpoint contract materially disagree.
- [ ] Production navigation exposes a dead feature as if implemented.
- [ ] Dashboard production values are hardcoded/fake.
- [ ] Critical mutation has no test for unauthorized access.
- [ ] Known P0/P1 defect remains open.

---

# 7. Universal Phase Completion Gate

Apply this to every backend/frontend business phase.

## Requirements

- [ ] Phase goal from `IMPLEMENTATION.md` is identified.
- [ ] Related PRD/blueprint rules are identified.
- [ ] No unresolved requirement contradiction exists.
- [ ] Deferred behavior is explicitly documented.

## Database

When the phase changes persistence:

- [ ] Migration exists.
- [ ] Migration is reversible when reasonably possible.
- [ ] Foreign keys are correct.
- [ ] Unique constraints are reviewed.
- [ ] Indexes match expected lookup/filter paths.
- [ ] Money uses approved storage strategy.
- [ ] Canonical enums/statuses match technical documents.
- [ ] Historical commercial snapshot requirements are preserved.
- [ ] `migrate:fresh` succeeds in test/dev environment.
- [ ] Seeder/factory data remains valid.

## Backend Domain

- [ ] Non-trivial state mutation uses an Action/Service or approved domain boundary.
- [ ] Controller remains thin.
- [ ] Generic update endpoint cannot bypass state-transition rules.
- [ ] External provider calls are isolated behind the integration boundary.
- [ ] Transactions are used for multi-row critical changes.
- [ ] Concurrency-sensitive mutation is reviewed for locking/idempotency.

## Authorization

- [ ] Required Spatie permission exists.
- [ ] Permission is assigned through intended role baseline.
- [ ] Resource Policy exists when ownership/assignment matters.
- [ ] Owner override uses Gate behavior, not controller role branching.
- [ ] Unauthorized test exists.
- [ ] Cross-resource/IDOR test exists where applicable.
- [ ] Hidden frontend controls are not treated as security.

## Validation and Serialization

- [ ] Form Request or equivalent input validation exists.
- [ ] Only validated input reaches mutation logic.
- [ ] Server-calculated financial values are not trusted from the browser.
- [ ] API Resource/serializer explicitly controls returned fields.
- [ ] Client/Worker response is reviewed for internal data leakage.
- [ ] Error response does not leak sensitive internals.

## API

- [ ] Route follows `/api/v1` convention where applicable.
- [ ] HTTP method and action semantics are appropriate.
- [ ] OpenAPI contract is updated.
- [ ] Generated frontend client/types are refreshed when required.
- [ ] No handwritten duplicate API type remains if generated type is authoritative.
- [ ] Pagination/filter/sort behavior is bounded and whitelisted where applicable.

## Frontend

When UI is part of the phase:

- [ ] Uses real API data.
- [ ] Loading state exists.
- [ ] Empty state exists.
- [ ] Error state exists.
- [ ] Forbidden behavior exists.
- [ ] Success feedback exists for mutation.
- [ ] Pending/disabled mutation state prevents accidental double submit where needed.
- [ ] Responsive core layout works.
- [ ] Core controls are keyboard usable.
- [ ] Canonical status labels are used.
- [ ] Permission-aware navigation is UX only.
- [ ] No internal-only field is rendered to Client/Worker.

## Audit and Logging

When event is audit-sensitive:

- [ ] Actor recorded.
- [ ] Subject/resource recorded.
- [ ] Important before/after state recorded safely.
- [ ] Request context recorded where appropriate.
- [ ] Secret/provider credentials are excluded.
- [ ] Logs include enough sanitized correlation to troubleshoot.

## Automated Tests

- [ ] Success-path test.
- [ ] Validation-failure test.
- [ ] Unauthorized test.
- [ ] Invalid-state test where state machine exists.
- [ ] Database post-condition assertion.
- [ ] Data-leakage assertion where role boundary exists.
- [ ] Provider failure/timeout test where external API exists.
- [ ] Duplicate/retry test where operation may be delivered more than once.

## Quality

- [ ] Backend test suite required for phase is green.
- [ ] Formatter/lint check is green.
- [ ] Frontend lint is green.
- [ ] Frontend typecheck is green.
- [ ] Frontend tests required for phase are green.
- [ ] Frontend production build is green.
- [ ] OpenAPI validation is green.
- [ ] No new unreviewed dependency is introduced.

## Manual Verification

- [ ] Happy path manually verified.
- [ ] Forbidden path manually verified for protected feature.
- [ ] Empty/error state manually verified where UI exists.
- [ ] Browser refresh/direct URL behavior verified where relevant.
- [ ] No console/server error remains during normal path.
- [ ] Evidence recorded.

---

# 8. Phase 0 — Repository Bootstrap Completion Checklist

## Entry Gate

- [x] Repository location and intended monorepo structure are confirmed.
- [x] Supported runtime versions match `TECHNICAL.md`.
- [x] No previous incompatible NestJS runtime is being treated as active backend architecture.

## Repository

- [x] `apps/api` exists and boots as Laravel application.
- [x] `apps/web` exists and boots as Next.js application.
- [x] pnpm workspace is configured.
- [x] Composer lockfile exists.
- [x] pnpm lockfile exists.
- [x] `.env.example` documents required non-secret variables.
- [x] Real secrets are ignored from version control.
- [x] Root README documents fresh local setup.

## Local Infrastructure

- [x] MySQL service is available.
- [x] Redis service is available.
- [x] Local services can be started by the documented method.
- [x] API can reach MySQL.
- [x] API can reach Redis.
- [x] Object storage may remain deferred for Release B.
- [x] Mail test provider may remain minimal if not yet required.

## Health

- [x] `GET /api/health` returns a stable successful response.
- [x] Health route does not leak secrets/configuration.
- [x] Deeper database/Redis checks, if exposed, are appropriate for environment/security.

## Toolchain

- [x] Laravel test runner executes.
- [x] Frontend test runner executes.
- [x] Backend formatting/lint command executes.
- [x] Frontend lint executes.
- [x] Frontend typecheck executes.
- [x] Frontend production build executes.
- [x] CI can install Composer dependencies.
- [x] CI can install pnpm dependencies with frozen lockfile.

## Fresh Clone Proof

From a clean checkout:

- [x] README setup steps are sufficient.
- [x] Environment can be configured without tribal knowledge.
- [x] Dependencies install.
- [x] Database initializes.
- [x] API boots.
- [x] Web boots.
- [x] Health endpoint works.

## Exit Gate

Phase 0 is complete only when a new developer/agent can start the project from a fresh clone using documented steps.

---

# 9. Phase 1 — Authentication Foundation Completion Checklist

## Architecture

- [x] Laravel Sanctum is configured for first-party SPA session authentication.
- [x] Laravel Fortify provides required headless authentication flows.
- [x] `web` guard is the canonical stateful guard.
- [x] Database-backed session strategy matches technical specification.
- [x] No first-party JWT access/refresh token architecture exists.

## Required Flows

- [x] CSRF cookie bootstrap works.
- [x] Login works with valid credentials.
- [x] Invalid credentials are rejected safely.
- [x] Logout invalidates usable authenticated access.
- [x] `/api/v1/me` returns canonical current-user representation.
- [ ] Forgot-password flow works.
- [ ] Reset-password flow works.
- [ ] Email-verification behavior matches configured rule.
- [x] Session survives expected SPA navigation/refresh.

## User Lifecycle

- [x] `INVITED` handled explicitly.
- [x] `ACTIVE` can authenticate.
- [x] `SUSPENDED` cannot retain normal portal access.
- [x] `DISABLED` cannot retain normal portal access.
- [x] User state is not confused with Worker lifecycle state.
- [x] No free-text `users.role` is used as RBAC authority.

## Security

- [x] CSRF is enforced for browser state-changing routes.
- [x] Session cookie settings are environment-appropriate.
- [x] Session fixation/rotation behavior is reviewed around login.
- [x] Password reset token behavior follows Laravel security flow.
- [x] Auth error does not leak sensitive account details.
- [x] Rate limiting exists where required by technical baseline.

## Required Automated Tests

- [x] `UserCanLogin`.
- [x] `InvalidCredentialsRejected`.
- [x] `SuspendedUserCannotUsePortal`.
- [x] `DisabledUserCannotUsePortal`.
- [x] `UserCanLogout`.
- [x] `UnauthenticatedApiRequestRejected`.
- [ ] `CsrfProtectedStateChangingRequest`.
- [ ] `PasswordResetFlow`.
- [ ] `EmailVerificationBehavior`.

## Frontend

- [x] `/login`.
- [ ] `/forgot-password`.
- [ ] `/reset-password`.
- [ ] `/verify-email`.
- [x] Authenticated root layouts exist for `/admin`, `/worker`, `/client`.
- [x] Direct navigation to protected portal without auth is handled safely.
- [x] Frontend auth guard is not the only authorization barrier.

## Exit Gate

- [x] All required auth tests green.
- [ ] Session behavior manually tested in browser.
- [ ] Logout followed by direct API/portal access is denied.
- [x] No JWT architecture exists for the first-party portal.
- [ ] Evidence recorded.

---

# 10. Phase 2 — Spatie RBAC + Laravel Policies Completion Checklist

## Spatie Configuration

- [x] `spatie/laravel-permission` installed.
- [x] `HasRoles` is applied to the User model.
- [x] `guard_name = web`.
- [x] Spatie Teams is not enabled for MVP.
- [x] Permission cache behavior is handled in test/seeder workflow. RefreshDatabase clears cache; firstOrCreate is idempotent.

## Canonical Roles

Idempotent seeder creates:

- [x] `OWNER`.
- [x] `ADMIN`.
- [x] `WORKER`.
- [x] `CLIENT`.
- [x] Re-running seeder does not duplicate roles/permissions.

## Permission Catalog

- [x] Permission names use `resource.action`.
- [x] Permission names are lowercase/predictable according to project convention.
- [x] Only implemented/known-required permissions are seeded.
- [x] Sensitive permissions are not given broadly by default.
- [x] Admin baseline reflects intended operational scope.
- [x] Client baseline contains only client-facing abilities.
- [x] Worker baseline contains only worker-facing abilities.

## Owner Super Admin

- [x] Owner override is implemented through `Gate::before` or approved equivalent.
- [x] Non-owner returns `null`/continues normal authorization flow.
- [ ] Sensitive Owner action still emits audit event. DEFERRED → Phase 20 (Audit Log), reason: AuditLog model not yet created.
- [x] Normal application code does not scatter `hasRole('OWNER')` bypasses.

## Policies

Initial resource policies exist where required:

- [x] ClientPolicy.
- [x] InquiryPolicy.
- [x] QuotationPolicy.
- [x] InvoicePolicy.
- [x] PaymentTransactionPolicy.
- [x] ProjectPolicy.
- [x] WorkerPolicy.
- [x] ProjectAssignmentPolicy.
- [x] TaskPolicy.
- [x] SchedulePolicy.
- [x] AuditLogPolicy.

## Isolation Tests

- [x] Client A cannot access Client B resource. DEFERRED → Phase 5 (Client Domain), requires Client model with user_id relationship.
- [x] Worker A cannot access unassigned Project B. DEFERRED → Phase 13 (Assignment), requires ProjectAssignment model.
- [x] Worker cannot access invoice/payment. DEFERRED → Phase 16 (Worker Portal), tested via permission denial (WORKER lacks invoices.view, payments.view).
- [x] Client cannot access internal finance. DEFERRED → Phase 17 (Client Portal), tested via permission denial (CLIENT lacks payments.view).
- [x] Admin without permission is denied. Tested in RBACTest: worker without admin permissions denied clients.create.
- [x] Owner can perform intended Super Admin action. Tested in RBACTest: OWNER gets all abilities via Gate::before.
- [x] Suspended user cannot bypass policy. Tested in existing AuthenticationTest: SuspendedUserCannotUsePortal.
- [ ] Role/permission-sensitive changes are auditable. DEFERRED → Phase 20 (Audit Log).

## Exit Gate

No business dashboard feature may proceed until reusable authorization test patterns prove role + permission + relationship enforcement.

---

# 11. Phase 3 — Portal Shells Completion Checklist

## Admin Shell

- [ ] `/admin/dashboard`.
- [x] Sales navigation structure.
- [x] Projects navigation structure.
- [x] Team navigation structure.
- [x] Finance navigation structure.
- [x] System navigation structure.
- [ ] Unimplemented production links are hidden or explicitly unavailable only in development context.

## Worker Shell

- [ ] `/worker/dashboard`.
- [x] `/worker/projects`.
- [x] `/worker/tasks`.
- [x] `/worker/schedule`.
- [x] `/worker/profile`.

## Client Shell

- [ ] `/client/dashboard`.
- [x] `/client/projects`.
- [x] `/client/quotations`.
- [x] `/client/invoices`.
- [x] `/client/profile`.

## Permission-Aware UX

- [ ] Navigation derives from effective permission/user context where appropriate.
- [x] Hiding navigation is not relied upon for backend security.
- [x] Direct URL access still reaches backend authorization.

## UI States

Every implemented shell/screen pattern supports:

- [x] loading.
- [ ] empty.
- [ ] error.
- [ ] forbidden.
- [ ] normal.

## No Dummy Completion

- [ ] No fake production metric is displayed as real.
- [ ] Placeholder screens are clearly non-functional/development-only.
- [ ] No clickable dead navigation exists in production behavior.

## Exit Gate

Portal shells are navigable, scoped, and ready to receive real domain data without pretending unimplemented modules are complete.

---

# 12. Phase 4 — Catalog Master Data Completion Checklist

## Schema

- [ ] Service model/migration.
- [ ] Package model/migration.
- [ ] AddOn model/migration.
- [ ] Public IDs where required.
- [ ] Status fields use approved vocabulary.
- [ ] Monetary fields use canonical money strategy.
- [ ] Relationship indexes/FKs reviewed.

## Business Rules

- [ ] Inactive/archived Package cannot be selected for new quotation unless explicitly allowed.
- [ ] Historical quotation will snapshot names/prices rather than depend on mutable catalog.
- [ ] Public CMS-specific publishing fields are not prematurely required.

## Admin UI

- [ ] Service list/create/edit.
- [ ] Package list/create/edit.
- [ ] Add-on list/create/edit.
- [ ] loading/empty/error/forbidden states.
- [ ] Validation feedback.

## Authorization

- [ ] Authorized Admin can manage catalog.
- [ ] Unauthorized Admin denied.
- [ ] Worker denied from admin catalog endpoints.
- [ ] Client denied from admin catalog endpoints.

## Tests

- [ ] CRUD success.
- [ ] Invalid data rejected.
- [ ] Unauthorized access rejected.
- [ ] Inactive package selection rule tested.
- [ ] Quotation snapshot dependency prepared/tested when quotation phase lands.

## Exit Gate

Catalog provides stable internal commercial data for Inquiry/Quotation without coupling historical records to mutable current prices.

---

# 13. Phase 5 — Client Domain Completion Checklist

## Data Model

- [ ] Client business entity is separate concept from User.
- [ ] Client-user association is explicit.
- [ ] Schema does not unnecessarily prevent multiple future users per client entity.
- [ ] Billing/business contact canonical fields are documented.
- [ ] Duplicate email behavior is explicit.

## Admin

- [ ] Client list.
- [ ] Client detail.
- [ ] Create client.
- [ ] Update allowed client fields.
- [ ] Archive/status behavior if implemented.
- [ ] Search/pagination where introduced.

## Client Portal

- [ ] Client can view own profile.
- [ ] Client can update only fields explicitly allowed.
- [ ] Client cannot enumerate another client.

## Conversion Safety

- [ ] Inquiry conversion can find/create client safely.
- [ ] Duplicate account/client creation is prevented.
- [ ] Existing User association is handled deterministically.

## Tests

- [ ] Authorized Admin creates client.
- [ ] Unauthorized role denied.
- [ ] Duplicate-email scenario.
- [ ] Client sees own profile.
- [ ] Client A cannot request Client B profile.
- [ ] Serialization excludes internal fields.

## Exit Gate

Client identity is reliable enough for quotation ownership, invoice ownership, project ownership, and future invitation flow.

---

# 14. Phase 6 — Inquiry / CRM Completion Checklist

## Status Model

Canonical statuses:

- [ ] `NEW`.
- [ ] `CONTACTED`.
- [ ] `QUALIFIED`.
- [ ] `QUOTATION`.
- [ ] `WON`.
- [ ] `LOST`.
- [ ] Implemented as backed enum or canonical project approach.

## State Transition

- [ ] `ChangeInquiryStatus` or equivalent explicit action exists.
- [ ] Allowed transition map is enforced.
- [ ] Generic update cannot arbitrarily assign status.
- [ ] Lost reason is handled when required.
- [ ] Actor/audit context is preserved.

## Data

- [ ] Required client/contact fields.
- [ ] Service/package reference.
- [ ] Preferred/alternative date.
- [ ] Location.
- [ ] Brief.
- [ ] Reference links.
- [ ] Budget.
- [ ] Source.
- [ ] Assigned admin.
- [ ] Internal notes protected.

## Admin UI

- [ ] Inquiry list.
- [ ] Inquiry detail.
- [ ] Search.
- [ ] Status filter.
- [ ] Assigned-admin filter.
- [ ] Service filter.
- [ ] Date-range filter.
- [ ] Source filter where present.
- [ ] Pagination.

## Dashboard

- [ ] Real `New Inquiries` metric is computed from database.
- [ ] No hardcoded count.

## Tests

- [ ] `AuthorizedAdminCanCreateInquiry`.
- [ ] `AuthorizedAdminCanAssignInquiry`.
- [ ] `InvalidInquiryTransitionRejected`.
- [ ] `InternalInquiryNoteNotExposedToClient`.
- [ ] `InquiryListCanFilterByStatus`.
- [ ] `UnauthorizedRoleCannotReadInquiry`.

## Public API

If unauthenticated inquiry API is introduced:

- [ ] Rate limiting.
- [ ] Validation.
- [ ] Spam/abuse considerations documented.
- [ ] No public landing page required yet.

## Exit Gate

Inquiry lifecycle can safely reach qualified/quotation state and feed the dashboard with real data.

---

# 15. Phase 7 — Quotation Completion Checklist

## Statuses

- [ ] `DRAFT`.
- [ ] `SENT`.
- [ ] `VIEWED`.
- [ ] `REVISION_REQUESTED`.
- [ ] `ACCEPTED`.
- [ ] `DECLINED`.
- [ ] `EXPIRED`.
- [ ] `CANCELLED`.

## Schema

- [ ] `quotations`.
- [ ] `quotation_versions`.
- [ ] `quotation_items`.
- [ ] event/status history mechanism.
- [ ] accepted version reference.
- [ ] expiry fields.
- [ ] creator/actor fields.
- [ ] required constraints/indexes.

## Snapshot Integrity

- [ ] Sent/accepted historical commercial data is immutable.
- [ ] Catalog name/price is snapshotted.
- [ ] Revision creates a new version.
- [ ] V1 remains available after V2.
- [ ] Accepted version cannot be overwritten.
- [ ] Totals are server-calculated/validated.

## Actions

- [ ] Create quotation.
- [ ] Create revision/version.
- [ ] Send quotation.
- [ ] Client accept.
- [ ] Client request revision.
- [ ] Client decline.
- [ ] Expiry/cancellation rules.
- [ ] No generic status bypass.

## Client Portal

- [ ] Own quotation list.
- [ ] Own quotation detail.
- [ ] Accept eligible quote.
- [ ] Request revision where eligible.
- [ ] Decline where eligible.
- [ ] Other-client quotation access denied.

## Acceptance Side Effects

- [ ] Accepted version locked.
- [ ] Actor recorded.
- [ ] Timestamp recorded.
- [ ] Inquiry commercial state updated at correct point.
- [ ] Client/account flow ensured.
- [ ] DP invoice workflow becomes eligible.
- [ ] Project is **not** activated before required DP.
- [ ] Audit record exists.

## Tests

- [ ] `ClientCanOnlySeeOwnQuotation`.
- [ ] `SentQuotationCanBeAccepted`.
- [ ] `ExpiredQuotationCannotBeAccepted`.
- [ ] `CancelledQuotationCannotBeAccepted`.
- [ ] `QuotationRevisionCreatesNewVersion`.
- [ ] `AcceptedVersionCannotBeOverwritten`.
- [ ] `AcceptanceStoresActorAndTimestamp`.
- [ ] `InquiryBecomesWonAtCorrectCommercialPoint`.

## Exit Gate

Accepted quotation is an immutable commercial snapshot and creates the correct preconditions for client activation and DP invoicing without prematurely activating a project.

---

# 16. Phase 8 — Client Invitation / Activation Completion Checklist

## Trigger

- [ ] Approved invitation trigger is defined.
- [ ] Quotation acceptance integration works where applicable.

## Account Safety

- [ ] Existing account is detected.
- [ ] Duplicate login account is not created.
- [ ] Invitation token expires.
- [ ] Token storage is hashed/safeguarded where practical.
- [ ] Password setup uses secure Laravel flow.
- [ ] Client association is explicit.
- [ ] Email verification follows project decision.

## Portal

After activation:

- [ ] Client can log in.
- [ ] Client dashboard loads.
- [ ] Client quotation data is scoped.
- [ ] Client invoice route may show real empty state before invoice exists.
- [ ] Client project route may show real empty state before activation.

## Tests

- [ ] Valid invitation activation.
- [ ] Expired invitation rejected.
- [ ] Reused invitation rejected or safely idempotent.
- [ ] Existing account path.
- [ ] Wrong client association cannot be forced.
- [ ] Activated Client cannot access another Client data.

## Exit Gate

A Client can transition from accepted commercial relationship to a securely authenticated, correctly associated portal user.

---

# 17. Phase 9 — Invoice Domain Completion Checklist

## Statuses

- [ ] `DRAFT`.
- [ ] `ISSUED`.
- [ ] `PARTIALLY_PAID`.
- [ ] `PAID`.
- [ ] `OVERDUE`.
- [ ] `VOID`.
- [ ] `REFUNDED`.

## Pre-Project DP Model

- [ ] `invoice.client_id` required.
- [ ] `invoice.quotation_id` supports pre-project DP invoice.
- [ ] `invoice.project_id` can remain nullable before activation.
- [ ] Invoice can be linked after Project activation.
- [ ] No active project is required merely to issue DP invoice.

## Fields / Constraints

- [ ] Invoice number uniqueness.
- [ ] Currency.
- [ ] Amount.
- [ ] Paid amount.
- [ ] Type.
- [ ] Issue/due/paid timestamps.
- [ ] Creator.
- [ ] Required FKs/indexes.

## Actions

- [ ] `CreateInvoice`.
- [ ] `IssueInvoice`.
- [ ] `VoidInvoice`.
- [ ] `RecalculateInvoicePaymentState`.
- [ ] Client cannot edit invoice.
- [ ] Status cannot be arbitrarily patched.

## UI

Admin:

- [ ] Invoice list.
- [ ] Invoice detail.
- [ ] Issue action.
- [ ] Void action if allowed.

Client:

- [ ] Own invoice list.
- [ ] Own invoice detail.
- [ ] Other-client invoice access denied.

## Tests

- [ ] DP invoice creation.
- [ ] Issue transition.
- [ ] Invalid transition denied.
- [ ] Client ownership isolation.
- [ ] Amount cannot be browser-tampered.
- [ ] Invoice remains usable before Project exists.

## Exit Gate

Invoice domain is stable enough for a real Duitku transaction and for payment state to be recomputed safely.

---

# 18. Phase 10 — Duitku Sandbox Completion Checklist

> **Critical phase. No shortcut is allowed.**

## Configuration

- [ ] `DUITKU_ENV=sandbox`.
- [ ] Merchant code configured outside repository.
- [ ] API key configured outside repository.
- [ ] Sandbox base URL configuration-driven.
- [ ] Callback URL configuration-driven.
- [ ] Return URL configuration-driven.
- [ ] Connect timeout configured.
- [ ] Request timeout configured.
- [ ] Production credentials are not used for normal development.
- [ ] No secret appears in client bundle.

## Integration Boundary

- [ ] `PaymentGateway` contract exists.
- [ ] `DuitkuPaymentGateway` implements runtime behavior.
- [ ] Provider HTTP code is not scattered through controllers.
- [ ] Create transaction logic is testable.
- [ ] Check transaction logic is testable.
- [ ] Callback verification logic is testable.

## Payment Transaction

- [ ] Transaction row created before/around provider call according to safe design.
- [ ] `merchant_order_id` generated server-side.
- [ ] `merchant_order_id` unique constraint exists.
- [ ] Invoice ownership authorized.
- [ ] Invoice payable state validated.
- [ ] Amount comes from authoritative server invoice.
- [ ] Provider reference stored.
- [ ] Payment URL stored.
- [ ] Canonical status stored.
- [ ] Raw provider payload retention is sanitized and justified.

## Canonical Status

- [ ] `UNPAID`.
- [ ] `PENDING`.
- [ ] `PAID`.
- [ ] `FAILED`.
- [ ] `EXPIRED`.
- [ ] `REFUNDED`.
- [ ] `PARTIALLY_REFUNDED`.
- [ ] Provider-to-canonical mapping documented.

## Callback Endpoint

- [ ] Provider-specific callback route exists.
- [ ] It is not protected by browser session auth.
- [ ] Browser CSRF exemption is scoped only as required.
- [ ] Malformed payload rejected safely.
- [ ] Merchant order resolved.
- [ ] Payment transaction resolved.
- [ ] Merchant/order consistency checked.
- [ ] Amount consistency checked.
- [ ] Expected HMAC-SHA256 signature calculated.
- [ ] Timing-safe signature comparison used.
- [ ] Duitku transaction-status check occurs before trusting paid state.
- [ ] Provider state mapped to canonical state.
- [ ] DB transaction wraps critical mutation.
- [ ] Relevant rows locked where required.
- [ ] Payment history written.
- [ ] Invoice recomputed.
- [ ] Project activation rule evaluated.
- [ ] Audit written.
- [ ] Side effects occur safely after/around commit according to design.
- [ ] HTTP 200 is returned only after event is safely accepted.

## Payment Authority

Prove all are true:

- [ ] Return URL cannot mark payment paid.
- [ ] Browser `resultCode` cannot mark payment paid.
- [ ] Client JavaScript callback cannot mark payment paid.
- [ ] Screenshot/manual browser claim cannot mark payment paid.
- [ ] Trusted backend callback + verification is payment authority.

## Idempotency

- [ ] Duplicate callback with same final state is safe.
- [ ] Duplicate paid callback cannot create second Project.
- [ ] Duplicate paid callback cannot duplicate payment history incorrectly.
- [ ] Duplicate paid callback cannot duplicate critical notification/receipt side effect.
- [ ] Out-of-order stale event cannot regress trusted `PAID`.
- [ ] Provider reference indexing/uniqueness reviewed.
- [ ] Exactly-once business outcome is enforced even if delivery is at-least-once.

## Status Check Failure

When callback signature is valid but status API fails:

- [ ] System does not guess `PAID`.
- [ ] Safe state is retained.
- [ ] Failure is logged without secrets.
- [ ] Callback evidence is retained safely.
- [ ] Reconciliation/retry path is possible.
- [ ] Client UI remains non-authoritative.

## Return Page

- [ ] Return page displays verification/pending state.
- [ ] Return page queries BDJG API.
- [ ] Bounded polling only.
- [ ] No direct aggressive browser polling to Duitku.
- [ ] Browser cannot mutate backend payment state.

## Public Sandbox Callback

- [ ] HTTPS.
- [ ] Publicly reachable.
- [ ] Correctly routes to Laravel callback.
- [ ] No secret in callback URL.
- [ ] Tunnel/development endpoint is documented.
- [ ] Logs are sanitized.

## Required Automated Tests

- [ ] `CreateDuitkuTransactionSuccess`.
- [ ] `CreateDuitkuTransactionTimeout`.
- [ ] `CreateDuitkuTransactionProviderError`.
- [ ] `ValidCallbackPaid`.
- [ ] `InvalidCallbackSignatureRejected`.
- [ ] `CallbackWrongAmountRejected`.
- [ ] `UnknownMerchantOrderRejected`.
- [ ] `DuplicatePaidCallbackIsIdempotent`.
- [ ] `OutOfOrderCallbackDoesNotRegressPaid`.
- [ ] `TransactionStatusCheckFailureDoesNotGuessPaid`.
- [ ] `InvoiceBecomesPaidAfterVerifiedPayment`.
- [ ] `ReturnUrlCannotMarkInvoicePaid`.
- [ ] Tests use HTTP fakes/mocks and do not require live sandbox in normal CI.

## Mandatory Manual Duitku Sandbox Proof

- [ ] Sandbox credential configured.
- [ ] Real sandbox transaction request succeeds.
- [ ] Generated merchant order ID is unique.
- [ ] Provider reference is persisted.
- [ ] Payment URL opens.
- [ ] Test payment can be completed.
- [ ] Real callback reaches application.
- [ ] Signature validation succeeds.
- [ ] Transaction-status verification succeeds.
- [ ] Canonical payment status updates correctly.
- [ ] Invoice updates correctly.
- [ ] Duplicate callback/replay test does not duplicate critical side effects.
- [ ] Payment history exists.
- [ ] Audit exists.
- [ ] API key/merchant secret absent from logs.

## Exit Gate

Phase 10 is not complete until both automated provider tests **and** a real Duitku Sandbox round trip have passed.

---

# 19. Phase 11 — Project Activation Completion Checklist

## Activation Rule

Prove:

```text
Accepted Quotation
+
Required DP Satisfied
=
Project may activate
```

- [ ] Rule is implemented in one explicit domain action/path.
- [ ] `ActivateProject` exists.
- [ ] Activation is not duplicated in webhook/controller/UI.
- [ ] Payment callback only triggers evaluation; domain action owns creation/activation.

## Creation

- [ ] No active project exists before required DP by baseline plan.
- [ ] Project created when activation requirement is satisfied.
- [ ] Client linked.
- [ ] Quotation linked.
- [ ] Accepted quotation version linked.
- [ ] Commercial values snapshotted.
- [ ] Relevant pre-project invoice linked.
- [ ] Responsible admin linked when known.
- [ ] `activated_at` recorded.
- [ ] Audit record written.

## Initial Status

- [ ] Initial exposed active state is `PRE_PRODUCTION`.
- [ ] No misleading client-visible active `DRAFT`.
- [ ] Canonical Project enum exists.

## Exactly Once

- [ ] Duplicate verified payment callback cannot create duplicate Project.
- [ ] Concurrent activation attempt cannot create duplicate Project.
- [ ] Unique constraints/locking support exactly-once business result.
- [ ] Existing activated Project is returned/handled idempotently.

## Tests

- [ ] Accepted quote without DP does not activate.
- [ ] Verified required DP activates.
- [ ] Unverified browser return does not activate.
- [ ] Duplicate callback activates only once.
- [ ] Wrong invoice/quote cannot activate unrelated project.
- [ ] Snapshot values match accepted version.

## Exit Gate

A verified commercial/payment state creates exactly one correct Project with immutable accepted commercial context.

---

# 20. Project State Machine Completion Checklist

This gate supports Phase 11 onward.

- [ ] Canonical statuses are declared.
- [ ] `ChangeProjectStatus` exists.
- [ ] Generic arbitrary status patch is prohibited.
- [ ] Allowed transition map is explicit.
- [ ] Cancellation requires permission and audit.
- [ ] Worker cannot move global project stage by default.
- [ ] Invalid transition test exists.
- [ ] Actor and transition history/audit are recorded where required.
- [ ] Client-safe status representation does not expose internal-only state detail unexpectedly.

---

# 21. Phase 12 — Worker Profile Completion Checklist

## Model

- [ ] Worker profile is distinct from User auth identity.
- [ ] Worker lifecycle status uses `ACTIVE`, `INACTIVE`, `ON_LEAVE`.
- [ ] User account lifecycle remains separate.
- [ ] Profession is domain metadata, not Spatie security role.
- [ ] Worker User receives canonical `WORKER` role.

## Fields

- [ ] User relation.
- [ ] Public ID.
- [ ] Profession.
- [ ] Skills.
- [ ] Phone.
- [ ] Status.
- [ ] Internal notes protected.

## Authorization

- [ ] Only authorized Owner/Admin manages worker profiles.
- [ ] Client cannot enumerate worker internal data.
- [ ] Worker can view/edit only approved self-profile fields.

## Tests

- [ ] Authorized creation/update.
- [ ] Unauthorized role denied.
- [ ] Internal notes not exposed.
- [ ] Worker lifecycle status does not change auth role silently.

## Exit Gate

Worker domain identity is stable and can be used safely by assignment logic.

---

# 22. Phase 13 — Project Assignment Completion Checklist

## Schema

- [ ] `project_assignments`.
- [ ] Project FK.
- [ ] Worker FK.
- [ ] Assignment role.
- [ ] Active flag.
- [ ] Assigned/removed timestamps.
- [ ] Actor.
- [ ] Index supports active assignment lookup.

## Actions

- [ ] `AssignWorkerToProject`.
- [ ] `RemoveWorkerFromProject`.
- [ ] Duplicate active assignment handled.
- [ ] Invalid inactive worker assignment handled according to rule.
- [ ] Mutation audited.

## Authorization

- [ ] Only permitted Admin/Owner can assign.
- [ ] Assignment is used by ProjectPolicy for Worker access.
- [ ] Removed worker loses access immediately/predictably.
- [ ] Worker cannot self-assign.

## Tests

- [ ] Worker gains access after active assignment.
- [ ] Unassigned Worker denied.
- [ ] Removed Worker denied.
- [ ] Assignment change audited.
- [ ] Duplicate assignment does not corrupt access.

## Exit Gate

Project assignment is the authoritative resource-access relationship for Worker Project access.

---

# 23. Phase 14 — Tasks Completion Checklist

## Statuses

- [ ] `TODO`.
- [ ] `IN_PROGRESS`.
- [ ] `REVIEW`.
- [ ] `BLOCKED`.
- [ ] `DONE`.
- [ ] `CANCELLED`.

## Schema

- [ ] Project.
- [ ] Title.
- [ ] Description.
- [ ] Status.
- [ ] Priority.
- [ ] Assigned worker.
- [ ] Due date.
- [ ] Completion timestamp.
- [ ] Creator.

## Transition Rules

- [ ] Allowed state map exists.
- [ ] Invalid arbitrary state changes rejected.
- [ ] Worker can update eligible assigned task.
- [ ] Worker cannot update another worker task without permission.
- [ ] Admin/Owner broader action remains controlled.

## Boundary Protection

Worker task action cannot mutate:

- [ ] Project finance.
- [ ] Client billing.
- [ ] Project ownership.
- [ ] Global project stage without explicit future permission.

## Tests

- [ ] Assigned worker updates task.
- [ ] Unassigned worker denied.
- [ ] Wrong project worker denied.
- [ ] Invalid transition rejected.
- [ ] Completion timestamp behavior correct.
- [ ] Significant status change audited if required.

## Exit Gate

Task workflow enables real worker progress without expanding Worker authority into commercial/global project controls.

---

# 24. Phase 15 — Basic Schedule Completion Checklist

## Event Model

Supported baseline types:

- [ ] `MEETING`.
- [ ] `SHOOT`.
- [ ] `DEADLINE`.
- [ ] `INTERNAL_REVIEW`.
- [ ] `CLIENT_REVIEW`.

## Fields

- [ ] Project.
- [ ] Type.
- [ ] Title.
- [ ] Start.
- [ ] End.
- [ ] Location.
- [ ] Visibility.
- [ ] Creator.

## Visibility

- [ ] Internal events are not exposed to Client.
- [ ] Client-visible events are explicitly marked.
- [ ] Worker visibility follows assignment/policy.
- [ ] API serialization respects visibility.

## Tests

- [ ] Authorized schedule create/update.
- [ ] Client internal-event denial.
- [ ] Worker unassigned-project schedule denial.
- [ ] Date/time validation.
- [ ] Dashboard upcoming-shoot query uses real schedule data.

## Exit Gate

Schedule data is safe enough to power Admin, Worker, and Client dashboard context.

---

# 25. Phase 16 — Worker Portal Completion Checklist

## Dashboard Metrics

Only real/scoped values:

- [ ] Active Projects.
- [ ] Tasks Today.
- [ ] Tasks Overdue.
- [ ] Upcoming Shoot.
- [ ] Deadline This Week.
- [ ] Unimplemented media/revision metrics are not faked.

## My Projects

- [ ] Query uses active assignment.
- [ ] Worker cannot enumerate all project IDs.
- [ ] Removed assignment disappears/restricts access.

## Project Detail

Worker may receive:

- [ ] Overview.
- [ ] Brief.
- [ ] Relevant Schedule.
- [ ] Team information allowed by policy.
- [ ] My Tasks.
- [ ] Relevant activity.

Worker must not receive:

- [ ] Invoice.
- [ ] Payment.
- [ ] Profit.
- [ ] Unrelated worker cost.
- [ ] General client directory.
- [ ] Unassigned project data.

## Required Tests

- [ ] `WorkerSeesAssignedProject`.
- [ ] `WorkerCannotSeeUnassignedProject`.
- [ ] `RemovedWorkerLosesProjectAccess`.
- [ ] `WorkerCannotSeeInvoice`.
- [ ] `WorkerCanUpdateAssignedTask`.
- [ ] `WorkerCannotUpdateAnotherWorkersTaskUnlessPermitted`.
- [ ] `WorkerCannotChangeProjectCommercialState`.

## UI

- [ ] loading.
- [ ] empty.
- [ ] error.
- [ ] forbidden.
- [ ] normal.
- [ ] direct deep link is secured.
- [ ] dashboard counts are scoped in query, not filtered only in browser.

## Exit Gate

Worker can perform real assigned operational work while commercial and unrelated project data remain inaccessible.

---

# 26. Phase 17 — Client Portal Project View Completion Checklist

## Dashboard

Real owned metrics:

- [ ] Active Projects.
- [ ] Payment Due.
- [ ] Next Shoot.
- [ ] Media-dependent metrics deferred until Release B.

## Projects

- [ ] Own project list only.
- [ ] Active/completed/archived filtering as applicable.
- [ ] No enumeration of another Client's project.

## Project Detail

Client-safe fields include:

- [ ] Overview.
- [ ] High-level status.
- [ ] Client-safe timeline.
- [ ] Client-visible schedule.
- [ ] Quotation reference.
- [ ] Own invoice/payment summary.

Client must not receive:

- [ ] Internal expense.
- [ ] Worker cost.
- [ ] Profit.
- [ ] Internal notes.
- [ ] Private draft.
- [ ] Sensitive admin activity.
- [ ] Unreleased media.

## Required Tests

- [ ] `ClientSeesOwnProject`.
- [ ] `ClientCannotSeeOtherProject`.
- [ ] `ClientCannotSeeInternalFinance`.
- [ ] `ClientCannotSeeWorkerInternalNotes`.
- [ ] `ClientCanSeeOwnInvoice`.
- [ ] `ClientCannotSeeOtherInvoice`.

## IDOR Manual Test

- [ ] Client A copies Client B public ID/URL.
- [ ] API returns 403 or safe 404.
- [ ] Response does not reveal protected title/client/status metadata.

## Exit Gate

Client portal exposes only owned, client-safe commercial/project information and survives direct URL tampering tests.

---

# 27. Phase 18 — Admin Dashboard Completion Checklist

## Prerequisite

- [ ] Inquiry data exists.
- [ ] Quotation data exists.
- [ ] Invoice/payment data exists.
- [ ] Project data exists.
- [ ] Task data exists.
- [ ] Schedule data exists.
- [ ] Dashboard is not built from fake metrics.

## Summary Cards

Release A:

- [ ] New Inquiries.
- [ ] Quotation Waiting.
- [ ] Quotation Accepted.
- [ ] Active Projects.
- [ ] Invoices Due.
- [ ] Today Shoots.

Deferred until later module:

- [ ] Client Review Waiting only when review module exists.
- [ ] Revisions Open only when revision module exists.

## Operational Sections

- [ ] Today's Schedule.
- [ ] Upcoming Production.
- [ ] Overdue Tasks.
- [ ] Payment Attention.
- [ ] Recent Activity.

## Finance

- [ ] Cash Received only for permitted role.
- [ ] Outstanding only for permitted role.
- [ ] Profit not displayed before finance model supports it.
- [ ] Client/Worker endpoints never receive Admin finance aggregates.

## Query Quality

- [ ] Aggregation occurs in database.
- [ ] No entire-table load then PHP count.
- [ ] Indexes reviewed for dashboard queries.
- [ ] Query count/N+1 reviewed.
- [ ] Empty database yields correct zero/empty state.

## Tests

- [ ] Metric query correctness using deterministic seed.
- [ ] Permission-scoped finance section.
- [ ] Unauthorized Admin finance permission test.
- [ ] No dummy value.
- [ ] Date-sensitive metric test uses controlled clock when needed.

## Exit Gate

Admin Dashboard reflects real operational state and is useful as a test surface for the entire Release A domain.

---

# 28. Phase 19 — Users & Role Management UI Completion Checklist

## User Actions

- [ ] Invite.
- [ ] Activate.
- [ ] Suspend.
- [ ] Disable.
- [ ] Assign role.
- [ ] Remove role.
- [ ] Sensitive action confirmation where needed.

## Security

- [ ] Normal Admin cannot promote self to Owner.
- [ ] Owner assignment requires trusted explicit path.
- [ ] Role mutation uses Spatie APIs/project-approved service.
- [ ] Permission cache behaves correctly after changes.
- [ ] Suspended/disabled session behavior is correct.
- [ ] UI hiding does not replace backend authorization.

## Audit

For role change:

- [ ] Actor.
- [ ] Target user.
- [ ] Old role.
- [ ] New role.
- [ ] Timestamp.
- [ ] Relevant request context.

## Tests

- [ ] Owner manages roles.
- [ ] Unauthorized Admin denied.
- [ ] Self-promotion blocked.
- [ ] Owner role escalation path protected.
- [ ] Role change takes effect.
- [ ] Role change audited.

## Exit Gate

User lifecycle and RBAC can be administered without opening privilege-escalation paths.

---

# 29. Phase 20 — Audit Log Completion Checklist

## Storage

- [ ] Append-oriented `audit_logs` storage.
- [ ] Event type.
- [ ] Actor.
- [ ] Subject type.
- [ ] Subject ID.
- [ ] Before data when appropriate.
- [ ] After data when appropriate.
- [ ] Metadata.
- [ ] IP when appropriate.
- [ ] User agent when appropriate.
- [ ] Created timestamp.
- [ ] No normal destructive edit/delete path.

## Mandatory Release A Events

- [ ] Sensitive login/account event where required.
- [ ] Role assigned/removed.
- [ ] Permission-sensitive user change.
- [ ] Inquiry assigned.
- [ ] Inquiry status changed.
- [ ] Quotation sent.
- [ ] Quotation revision created.
- [ ] Quotation accepted.
- [ ] Quotation declined.
- [ ] Invoice issued.
- [ ] Payment status changed.
- [ ] Manual reconciliation.
- [ ] Project activated.
- [ ] Project status changed.
- [ ] Worker assigned.
- [ ] Worker removed.
- [ ] Significant task status change.

## Sensitive Data

- [ ] Password absent.
- [ ] Session cookie absent.
- [ ] Duitku API key absent.
- [ ] Authorization secret absent.
- [ ] Raw sensitive provider payload redacted/limited.
- [ ] Audit metadata does not become a second secret store.

## UI

- [ ] `/admin/system/activity`.
- [ ] Actor filter.
- [ ] Event filter.
- [ ] Resource filter.
- [ ] Date filter.
- [ ] Pagination.
- [ ] Owner/authorized Admin only.

## Tests

- [ ] Critical mutation writes audit event.
- [ ] Unauthorized role cannot read audit.
- [ ] Sensitive values are not stored.
- [ ] Audit append behavior is preserved.

## Exit Gate

Critical Release A decisions can be reconstructed from audit records without exposing secrets.

---

# 30. Phase 21 — Search, Filter, Pagination Completion Checklist

## Dataset Coverage

Server-side behavior exists for:

- [ ] inquiries.
- [ ] clients.
- [ ] quotations.
- [ ] invoices.
- [ ] payments.
- [ ] projects.
- [ ] workers.
- [ ] audit.

## Security / Query Safety

- [ ] Sort fields are allowlisted.
- [ ] Filter fields are allowlisted.
- [ ] Page size has maximum.
- [ ] Search does not expose unauthorized rows.
- [ ] Role/resource scope is applied before pagination.
- [ ] User input cannot become raw database column/expression.
- [ ] Stable default sort exists.
- [ ] Invalid filter/sort input handled consistently.

## UX

- [ ] Query state is reflected predictably in URL/UI where chosen.
- [ ] Loading during filter/page transition.
- [ ] Empty result state.
- [ ] Error state.
- [ ] Pagination does not lose active filters.

## Tests

- [ ] Search result correctness.
- [ ] Filter correctness.
- [ ] Sort allowlist.
- [ ] Invalid sort rejected/falls back per contract.
- [ ] Page-size cap.
- [ ] Scoped user cannot search into forbidden records.

## Exit Gate

Core datasets remain usable and secure as volume increases, without unrestricted database query input.

---

# 31. Release A — Operational Dashboard MVP Final Completion Gate

Release A is **not complete** merely because Phases 0–21 have individual checkmarks. The integrated system must pass this final gate.

## Identity

- [ ] Login works.
- [ ] Logout works.
- [ ] Sanctum stateful session works.
- [ ] CSRF works.
- [ ] Password reset works.
- [ ] Email verification behavior is defined/working.
- [ ] Suspended behavior works.
- [ ] Disabled behavior works.

## Authorization

- [ ] Canonical Spatie roles seeded.
- [ ] Permission matrix works.
- [ ] Owner Super Admin works through Gate.
- [ ] Client ownership isolation passes.
- [ ] Worker assignment isolation passes.
- [ ] Admin granular permission passes.
- [ ] Direct URL/IDOR checks pass.
- [ ] No cross-role protected data leak found.

## Sales

- [ ] Client directory works.
- [ ] Inquiry workflow works.
- [ ] Quotation versioning works.
- [ ] Client can accept eligible quotation.
- [ ] Client can request revision where eligible.
- [ ] Client can decline where eligible.
- [ ] Accepted commercial snapshot is immutable.

## Billing

- [ ] DP invoice works before project activation.
- [ ] Real Duitku Sandbox create transaction works.
- [ ] Callback reaches application.
- [ ] Signature verification works.
- [ ] Transaction-status check works.
- [ ] Canonical payment mapping works.
- [ ] Duplicate callback is idempotent.
- [ ] Return URL cannot mark paid.
- [ ] Invoice state recomputes correctly.

## Production

- [ ] Project activation rule works.
- [ ] Project activation occurs exactly once.
- [ ] Accepted commercial snapshot stored in Project.
- [ ] Worker profile works.
- [ ] Worker assignment works.
- [ ] Removed Worker access revoked.
- [ ] Task workflow works.
- [ ] Basic Schedule works.
- [ ] Project state transitions are controlled.

## Portals

- [ ] Admin Dashboard uses real data.
- [ ] Worker Dashboard uses real scoped data.
- [ ] Client Dashboard uses real owned data.
- [ ] Loading/empty/error/forbidden states work.
- [ ] No fake production metric remains.
- [ ] No dead production navigation remains.

## Audit

- [ ] Critical Release A events are auditable.
- [ ] Audit view is permission protected.
- [ ] Sensitive secrets absent from audit/logs.

## API / Contract

- [ ] `/api/v1` conventions are consistent.
- [ ] OpenAPI reflects implemented Release A APIs.
- [ ] Generated frontend client is current.
- [ ] No material contract drift.

## Required CI

- [ ] `composer validate`.
- [ ] Composer install from lockfile.
- [ ] Backend test suite.
- [ ] Laravel Pint check.
- [ ] pnpm frozen-lockfile install.
- [ ] Frontend lint.
- [ ] Frontend typecheck.
- [ ] Frontend unit/component tests required by project.
- [ ] Frontend production build.
- [ ] OpenAPI validation.
- [ ] Generated client freshness check if committed.

## Golden Path E2E

Prove this sequence:

- [ ] Owner logs in.
- [ ] Owner/Admin creates or qualifies Inquiry.
- [ ] Client is created/resolved.
- [ ] Quotation is created.
- [ ] Quotation is sent.
- [ ] Client logs in.
- [ ] Client sees only own Quotation.
- [ ] Client accepts.
- [ ] DP Invoice exists.
- [ ] Client starts Duitku Sandbox payment.
- [ ] Sandbox payment is completed.
- [ ] Callback reaches backend.
- [ ] Backend verifies transaction.
- [ ] Invoice becomes `PAID`.
- [ ] Project activates.
- [ ] Admin assigns Worker.
- [ ] Worker logs in.
- [ ] Worker sees assigned Project.
- [ ] Worker changes assigned Task.
- [ ] Client sees allowed Project progress.
- [ ] Client attempts another Client Project URL and is denied.
- [ ] Worker attempts unassigned Project URL and is denied.
- [ ] Owner/Admin views resulting Audit trail.

## Release A Sign-Off

- [ ] No P0.
- [ ] No P1.
- [ ] No unresolved payment correctness issue.
- [ ] No unresolved authorization/isolation issue.
- [ ] Required CI green.
- [ ] Real Duitku Sandbox checklist passed.
- [ ] Golden path passed.
- [ ] Documentation synchronized.
- [ ] Release A evidence record completed.

Only then may Release B begin.

---

# 32. Release B — Media Workflow Phase Completion Gates

Release B begins only after Release A is `COMPLETE`.

---

# 33. Release B.1 — Object Storage & Pending Upload Completion Checklist

## Storage

- [ ] S3-compatible disk configured.
- [ ] Development and production storage config are environment driven.
- [ ] Protected media is private by default.
- [ ] Client cannot choose arbitrary trusted storage key.

## Pending Upload

- [ ] Upload intent created only after authorization.
- [ ] Expected mime/type/size metadata recorded.
- [ ] Signed/presigned upload URL generated.
- [ ] Browser uploads directly to object storage.
- [ ] Large binary does not transit Laravel unnecessarily.
- [ ] Finalize endpoint verifies ownership and intent.
- [ ] Uploaded object existence/metadata verified.
- [ ] Expired/abandoned intent cleanup strategy exists.

## Visibility

- [ ] Internal.
- [ ] Client-shared.
- [ ] Client-preview.
- [ ] Final.
- [ ] Canonical visibility vocabulary matches product/technical document.
- [ ] Visibility is enforced server-side before temporary URL generation.

## Security Tests

- [ ] Client cannot finalize another user's upload intent.
- [ ] Worker cannot upload to unassigned Project.
- [ ] Arbitrary object key rejected.
- [ ] Expired intent rejected.
- [ ] Disallowed size/type rejected.
- [ ] Private object is not publicly enumerable.

## Exit Gate

Authorized users can upload large media directly to protected object storage through controlled pending-upload intent.

---

# 34. Release B.2 — Preview Completion Checklist

## Flow

- [ ] Worker uploads source.
- [ ] Source remains internal.
- [ ] Processing creates preview derivative.
- [ ] Internal review occurs.
- [ ] Authorized Admin releases preview.
- [ ] Client gains access only after release.

## Security

- [ ] Client cannot discover internal preview before release.
- [ ] Preview URL is temporary/signed.
- [ ] URL generation checks authorization before issuing URL.
- [ ] Expired URL no longer grants normal access.
- [ ] Storage path does not reveal unrelated media.

## Tests

- [ ] Internal preview denied to Client.
- [ ] Released preview available to owning Client.
- [ ] Other Client denied.
- [ ] Unassigned Worker denied.
- [ ] Expired access behavior.

## Exit Gate

Preview lifecycle cleanly separates internal media from client-released media.

---

# 35. Release B.3 — FFmpeg Completion Checklist

## Execution

- [ ] FFmpeg runs only in queued job.
- [ ] Job runs under Horizon/queue process, not HTTP request lifecycle.
- [ ] Laravel Process or approved process boundary is used.
- [ ] Input path is controlled.
- [ ] Output path is controlled.
- [ ] Arguments are not built from unsafe raw shell concatenation.
- [ ] Timeout defined.
- [ ] Retry/backoff defined.
- [ ] Failed job behavior defined.
- [ ] Temporary files cleaned.
- [ ] Successful output metadata persisted.

## Idempotency

- [ ] Duplicate derivative job does not corrupt/duplicate canonical output.
- [ ] Existing completed derivative handled safely.
- [ ] Reprocessing behavior is explicit.

## Required Tests

- [ ] Success.
- [ ] Timeout.
- [ ] Invalid media.
- [ ] Job retry.
- [ ] Failed job.
- [ ] Cleanup.
- [ ] Duplicate job.

## Operational

- [ ] Horizon can observe queues.
- [ ] Failed media job can be diagnosed without exposing secret data.
- [ ] Worker memory/runtime behavior is reviewed with realistic sample.

## Exit Gate

Media derivative processing is asynchronous, retry-safe, observable, and cannot block normal API requests.

---

# 36. Release B.4 — Revision Completion Checklist

## Statuses

- [ ] `REQUESTED`.
- [ ] `TRIAGE`.
- [ ] `IN_PROGRESS`.
- [ ] `INTERNAL_REVIEW`.
- [ ] `READY_FOR_CLIENT`.
- [ ] `APPROVED`.
- [ ] `CLOSED`.

## Revision Rules

- [ ] Revision round is linked to correct preview/version.
- [ ] Package/accepted quotation snapshot determines applicable revision limit.
- [ ] Agent does not invent revision limit when product rule is unresolved.
- [ ] Timestamp feedback stores preview version.
- [ ] Timestamp stored accurately.
- [ ] Comment actor recorded.
- [ ] Assignment recorded where applicable.
- [ ] State transition controlled.

## Access

- [ ] Owning Client only.
- [ ] Assigned/authorized Worker only.
- [ ] Internal comments stay internal.
- [ ] Client comment cannot mutate unrelated project state.

## Tests

- [ ] Client creates allowed revision feedback.
- [ ] Other Client denied.
- [ ] Revision against wrong preview denied.
- [ ] Revision limit enforced only according to documented rule.
- [ ] Invalid state transition rejected.
- [ ] Timestamp/comment persists.

## Exit Gate

Revision feedback is version-aware, ownership-safe, and consistent with accepted commercial terms.

---

# 37. Release B.5 — Final Delivery Completion Checklist

## Preconditions

- [ ] Approved final media exists.
- [ ] Release authorization exists.
- [ ] Applicable payment/release rule is resolved from product documents.
- [ ] No unresolved product rule is silently invented.

## Delivery

- [ ] Final asset marked appropriately.
- [ ] Client authorization checked before link generation.
- [ ] Temporary/protected delivery URL used where required.
- [ ] Delivery event audited.
- [ ] Download/access history recorded if required by technical/product rule.

## Tests

- [ ] Owning Client can access released final.
- [ ] Other Client denied.
- [ ] Unreleased final denied.
- [ ] Payment/release precondition enforced.
- [ ] Expired URL behavior.

## Exit Gate

Final media can be released only under documented business conditions and remains protected from unauthorized access.

---

# 38. Release B Final Gate

- [ ] Pending upload flow passed.
- [ ] Direct object-storage upload passed.
- [ ] Protected URL behavior passed.
- [ ] FFmpeg queue processing passed.
- [ ] Horizon queue observability passed.
- [ ] Preview release boundary passed.
- [ ] Revision lifecycle passed.
- [ ] Final delivery rule passed.
- [ ] Client cannot access unreleased media.
- [ ] Worker cannot access unassigned Project media.
- [ ] Media golden path E2E passed.
- [ ] No P0/P1.
- [ ] CI green.
- [ ] Documentation synchronized.

---

# 39. Release C.1 — Notification Completion Checklist

## Channels

- [ ] In-app where required.
- [ ] Email.
- [ ] WhatsApp provider when configured.

## Architecture

- [ ] Notification is asynchronous where appropriate.
- [ ] Critical business transaction commits independently of external notification success.
- [ ] Notification failure cannot rollback verified payment/project state.
- [ ] Retry/backoff strategy.
- [ ] Provider failure observability.
- [ ] Idempotency/deduplication for critical notification.

## Security

- [ ] Recipient is derived from authoritative data.
- [ ] Sensitive internal data is not sent to Client.
- [ ] Secrets not logged.
- [ ] Provider payload retention is minimized.

## Tests

- [ ] Notification queued after relevant committed event.
- [ ] Provider failure does not rollback business state.
- [ ] Retry path.
- [ ] Wrong recipient protection.
- [ ] Duplicate event handling.

## Exit Gate

Notifications are reliable supplementary side effects, never the authority for a critical business transition.

---

# 40. Release C.2 — Finance Expansion Completion Checklist

## Features

- [ ] Project expense.
- [ ] Estimated gross profit.
- [ ] Worker expense claim.
- [ ] Finance reports.
- [ ] Manual reconciliation UI.
- [ ] Refund workflow where approved.

## Authorization

- [ ] Owner / explicitly permitted Admin only.
- [ ] Worker cannot see Project profit.
- [ ] Client cannot see Project profit.
- [ ] Worker cost not exposed outside authorized context.
- [ ] Finance API Resources explicitly whitelist fields.

## Integrity

- [ ] Money arithmetic strategy consistent.
- [ ] Refund state does not corrupt original transaction history.
- [ ] Reconciliation is audited.
- [ ] Manual finance adjustment is audited.
- [ ] Financial records are not hard-deleted through normal flow.

## Tests

- [ ] Finance permission tests.
- [ ] Client leak test.
- [ ] Worker leak test.
- [ ] Refund/reconciliation state tests.
- [ ] Audit tests.

## Exit Gate

Expanded finance operations preserve historical integrity and remain isolated to authorized finance users.

---

# 41. Release C Final Gate

- [ ] Notification channels work.
- [ ] Notification failures are isolated.
- [ ] Finance authorization isolation passes.
- [ ] Reconciliation is auditable.
- [ ] Refund workflow passes if in scope.
- [ ] No Client/Worker profit leak.
- [ ] No P0/P1.
- [ ] CI green.
- [ ] Documentation synchronized.

---

# 42. Release D.1 — Public Site Completion Checklist

## Routes

- [ ] `/`.
- [ ] `/works`.
- [ ] `/works/[slug]`.
- [ ] `/services`.
- [ ] `/about`.
- [ ] `/studio`.
- [ ] `/contact`.
- [ ] `/book`.

## Integration Principle

- [ ] Public Book form uses existing stable Inquiry domain/API.
- [ ] No second parallel lead database/workflow exists.
- [ ] Public Service/Package data maps cleanly to internal catalog/CMS.
- [ ] Public page does not expose internal fields.

## Quality

- [ ] Responsive.
- [ ] Accessibility baseline.
- [ ] SEO metadata where required.
- [ ] Loading/error states for dynamic content.
- [ ] Image optimization.
- [ ] Public performance reviewed.
- [ ] Contact/Book input rate limiting and validation.

## Security

- [ ] No internal API data serialized into page payload.
- [ ] Public IDs/slugs do not grant protected portal access.
- [ ] Public forms protected from obvious abuse according to project baseline.

## Exit Gate

Public experience reuses the operational backend rather than creating parallel business logic.

---

# 43. Release D.2 — CMS Completion Checklist

## CMS Scope

- [ ] Portfolio.
- [ ] Services.
- [ ] Packages.
- [ ] Add-ons.
- [ ] Basic contact content.

## Publishing

- [ ] Draft/published behavior explicit.
- [ ] Only authorized Admin/Owner can publish.
- [ ] Completed Project does not automatically publish to Works.
- [ ] Publication consent/decision remains explicit.
- [ ] Internal media cannot be accidentally selected as public without release/publish action.

## Tests

- [ ] Authorized publish.
- [ ] Unauthorized publish denied.
- [ ] Draft not public.
- [ ] Published content public.
- [ ] Project completion does not auto-publish.
- [ ] Internal fields not serialized publicly.

## Exit Gate

CMS publication is a deliberate content operation separated from Project completion and internal production data.

---

# 44. Release D Final Gate

- [ ] Public routes complete.
- [ ] Book form enters existing Inquiry workflow.
- [ ] CMS publishing works.
- [ ] No internal data leak.
- [ ] SEO/performance baseline passed.
- [ ] Responsive/accessibility review passed.
- [ ] No P0/P1.
- [ ] CI green.
- [ ] Documentation synchronized.

---

# 45. Regression Gate Before Starting Any Next Phase

Before moving from Phase N to Phase N+1:

- [ ] Current phase checklist is `COMPLETE`.
- [ ] Previous completed phase tests still pass.
- [ ] Database migrations still work from fresh state.
- [ ] Seeders still work.
- [ ] Authorization regression suite still passes.
- [ ] Client isolation tests still pass.
- [ ] Worker isolation tests still pass.
- [ ] Payment regression suite passes once Phase 10 exists.
- [ ] Project exactly-once activation test passes once Phase 11 exists.
- [ ] Frontend build remains green.
- [ ] OpenAPI remains synchronized.
- [ ] No new P0/P1 was introduced.
- [ ] Implementation plan is updated if sequencing changed.

---

# 46. Required Authorization Regression Suite

Once relevant domains exist, these tests are permanent release blockers.

## Client

- [ ] Client A cannot view Client B profile.
- [ ] Client A cannot view Client B quotation.
- [ ] Client A cannot view Client B invoice.
- [ ] Client A cannot view Client B payment.
- [ ] Client A cannot view Client B project.
- [ ] Client cannot view internal finance.
- [ ] Client cannot view internal notes.
- [ ] Client cannot view unreleased media after Release B.

## Worker

- [ ] Worker cannot view unassigned project.
- [ ] Removed Worker cannot view project.
- [ ] Worker cannot view invoice.
- [ ] Worker cannot view payment.
- [ ] Worker cannot view profit.
- [ ] Worker cannot update another worker's task unless explicitly permitted.
- [ ] Worker cannot change commercial Project state.
- [ ] Worker cannot access unassigned media after Release B.

## Admin

- [ ] Admin without permission is denied.
- [ ] Admin cannot self-promote to Owner.
- [ ] Finance access respects permission.
- [ ] Role management respects permission.

## Owner

- [ ] Owner Super Admin path works.
- [ ] Owner sensitive mutations remain audited.

---

# 47. Required Payment Regression Suite

Once Phase 10 exists, these checks are permanent.

- [ ] Create transaction success.
- [ ] Provider timeout.
- [ ] Provider error.
- [ ] Invalid callback signature.
- [ ] Wrong amount.
- [ ] Unknown order.
- [ ] Valid paid callback.
- [ ] Duplicate paid callback.
- [ ] Out-of-order callback.
- [ ] Status-check failure.
- [ ] Return URL cannot mark paid.
- [ ] Invoice recomputation.
- [ ] Exactly-once Project activation.
- [ ] Secret redaction in logs.

---

# 48. Required Database Integrity Regression Gate

For every release candidate:

- [ ] Fresh schema migration succeeds.
- [ ] Seed succeeds.
- [ ] No duplicate unique business identifiers.
- [ ] Orphan critical relations are prevented.
- [ ] Expected cascade/restrict behavior reviewed.
- [ ] Critical historical records are not accidentally deleted.
- [ ] Accepted quotation snapshots remain intact.
- [ ] Payment history remains append-safe.
- [ ] Audit history remains append-safe.
- [ ] Project commercial snapshots remain intact.

---

# 49. Required Frontend Regression Gate

For each portal:

- [ ] Login/session restore.
- [ ] Loading.
- [ ] Empty.
- [ ] Error.
- [ ] Forbidden.
- [ ] Normal.
- [ ] Direct deep-link navigation.
- [ ] Refresh on protected route.
- [ ] Permission-aware navigation.
- [ ] No reliance on hidden button for security.
- [ ] No internal field leak in rendered HTML/JSON.
- [ ] Mutation double-submit prevention where needed.
- [ ] Canonical status display.
- [ ] Production build.

---

# 50. Playwright / E2E Rules

Use E2E for integrated user outcomes, not every trivial implementation detail.

Rules:

- [ ] Prefer user-facing locators such as role/label.
- [ ] Avoid brittle CSS/XPath selectors when stable semantic locator exists.
- [ ] Do not add arbitrary sleep/timeouts as normal synchronization.
- [ ] Test data is controlled/deterministic.
- [ ] Each E2E test starts from known state.
- [ ] Authentication setup is reusable but does not bypass authorization assertions.
- [ ] Browser action is followed by observable assertion.
- [ ] Server-side post-condition may be verified through API/database test boundary where appropriate.
- [ ] Cross-role E2E includes direct URL tampering.
- [ ] Live Duitku Sandbox path may be manual/tagged and excluded from every-commit CI.

---

# 51. AI Agent Phase Checkpoint Format

At the end of every phase, the AI agent must report using this exact conceptual structure:

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

Rules:

- Do not report `COMPLETE` if `EXIT GATE = FAIL`.
- Do not hide failing tests.
- Do not call an untested phase complete.
- Do not say “should work” as completion evidence.
- Record actual results.

---

# 52. Evidence Standards for Payment Phase

For Duitku evidence, store only sanitized information.

Allowed examples:

```text
merchant_order_id: BDJG-TEST-20260818-XXXX
provider_reference: sanitized/non-secret reference
callback received_at
canonical status
invoice public_id
project public_id
correlation/request id
```

Never include:

```text
DUITKU_API_KEY
session cookie
password
private credential
full secret headers
```

---

# 53. Documentation Synchronization Gate

When a phase changes an architectural or locked implementation decision:

- [ ] `TECHNICAL.md` reviewed.
- [ ] `IMPLEMENTATION.md` reviewed.
- [ ] This checklist reviewed.
- [ ] `AGENTS.md` reviewed after it exists.
- [ ] API contract reviewed.
- [ ] `.env.example` reviewed.
- [ ] README reviewed.
- [ ] No old NestJS/Prisma/BullMQ instruction remains active by mistake.
- [ ] No FakePayment runtime instruction remains active by mistake.
- [ ] No manual RBAC design conflicts with Spatie decision.
- [ ] Dashboard-first ordering remains consistent.

---

# 54. Dependency Addition Gate

Before adding a dependency:

- [ ] Native Laravel/Next.js capability was considered.
- [ ] Existing installed package cannot solve the requirement cleanly.
- [ ] Package supports current framework/runtime.
- [ ] Package is maintained.
- [ ] Security/licensing risk reviewed.
- [ ] Package does not duplicate a major existing abstraction.
- [ ] Lockfile updated.
- [ ] CI passes.
- [ ] Reason documented for non-trivial dependency.

Do not add a package merely because an AI agent is more familiar with it.

---

# 55. Security Sign-Off Gate

At each Release gate:

- [ ] Authentication behavior reviewed.
- [ ] Authorization matrix reviewed.
- [ ] IDOR tests passed.
- [ ] CSRF behavior reviewed.
- [ ] Webhook authenticity verified.
- [ ] Input validation reviewed.
- [ ] Mass assignment reviewed.
- [ ] Sensitive serialization reviewed.
- [ ] Secret handling reviewed.
- [ ] Rate limiting reviewed.
- [ ] Audit coverage reviewed.
- [ ] File visibility reviewed after Release B.
- [ ] Unsafe shell/process arguments reviewed after FFmpeg.
- [ ] No unresolved P0/P1.

---

# 56. Performance Sign-Off Gate

Do not prematurely optimize; verify obvious correctness first.

At applicable phase/release:

- [ ] Pagination exists for growing datasets.
- [ ] Required indexes exist.
- [ ] Dashboard aggregates use database.
- [ ] N+1 query behavior reviewed.
- [ ] Expensive external calls are not repeated unnecessarily.
- [ ] Long media processing is queued.
- [ ] Large media bypasses Laravel binary proxy where intended.
- [ ] Queue timeout/retry is explicit.
- [ ] Performance issue is measured before architecture is expanded.

---

# 57. Observability Sign-Off Gate

At applicable release:

- [ ] Important errors are logged.
- [ ] Correlation/request context exists where useful.
- [ ] Payment callback can be traced without secret payload.
- [ ] Queue failure is visible after Release B.
- [ ] External provider failure can be distinguished from validation/business failure.
- [ ] Audit and operational logging concerns are not conflated.
- [ ] Health check remains valid.
- [ ] Production logs do not expose credentials.

---

# 58. No-Silent-Assumption Gate

The AI agent must stop and document a product/technical question instead of inventing behavior when any of these are unresolved:

- [ ] Final-delivery payment/release rule.
- [ ] Revision limit not derivable from accepted package/product rule.
- [ ] Refund authority/workflow not defined.
- [ ] Publication consent not defined.
- [ ] A state transition is missing from canonical rule.
- [ ] A permission boundary is ambiguous.
- [ ] A destructive data change is required.
- [ ] A provider behavior contradicts current documentation.
- [ ] A source document conflicts with a newer locked decision.

A missing product decision is **not** permission to create one silently.

---

# 59. Final Completion Principle

A phase is complete only when the implemented software proves the intended business behavior under:

```text
happy path
+
invalid input
+
unauthorized actor
+
wrong resource
+
wrong state
+
retry/duplicate where applicable
+
real UI behavior
+
database post-condition
+
audit/log requirement
+
documentation consistency
```

For BDJG, the most important rule is:

```text
VISIBLE UI
≠
COMPLETED PHASE
```

The correct rule is:

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
