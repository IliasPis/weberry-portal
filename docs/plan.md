# Weberry Client Panel — Client Management / CRM / Helpdesk / Maintenance Operations Platform

## Context

The user runs a web development / WordPress / WooCommerce / hosting / maintenance business and needs a
production-grade internal system to replace ad-hoc tools. It must serve two audiences with strictly
separated permissions: an **Admin Portal** (the operational hub for running the whole business — clients,
websites, services, tickets, tasks, finances, maintenance, renewals) and a **Client Portal** (a minimal,
professional self-service view scoped to that one client's own data only). The working directory is empty
— this is a greenfield build. Tech stack, database, and hosting/storage approach were confirmed directly by
the user (see Architecture). Default UI language is Greek, switchable to English (bilingual from day one).

This plan covers the full design (architecture, schema, permissions, navigation, folder structure) and a
staged MVP implementation roadmap. Given the size of the system, implementation proceeds stage-by-stage
after approval; later stages (Phase 2/3) are documented as roadmap, not built immediately.

## Architecture

- **Backend:** Laravel 13, PHP 8.3+. Single Laravel codebase containing both portals as separate route
  groups, controllers, middleware and Vue page namespaces (`Pages/Admin/*` vs `Pages/Client/*`) — not two
  separate apps, so shared domain models/services stay consistent, but URL space, layouts and
  authorization are cleanly partitioned.
- **Frontend:** Vue 3 + TypeScript via Inertia 3 (no SPA API layer, no Blade for app pages — Blade is used
  only for the single root template Inertia requires and for email templates). Scaffolded from Laravel
  Breeze's Vue/Inertia stack (its own real, editable auth controllers — matches "Laravel built-in
  authentication", not the headless Fortify package), extended with TypeScript.
- **UI kit:** Tailwind CSS 4 + shadcn-vue (radix-vue based primitives copied into `resources/js/components/ui`).
  Admin and Client portals share the same primitives but compose them into two distinct layouts (dense
  sidebar+data-table admin UI vs minimal card-based client UI).
- **Database:** PostgreSQL, via Eloquent migrations. Status/category fields use PHP backed enums
  (`enum Status: string`) cast on the model rather than native Postgres enum types, so adding a new status
  later is a code change, not a schema migration with `ALTER TYPE`.
- **Auth:** Laravel's native `Auth` facade/session guards (Breeze-generated controllers) — login, register
  (admin-created clients get invited, not self-registered), email verification, password reset, password
  confirmation. 2FA (TOTP) added in Phase 2.
- **Authorization:** `spatie/laravel-permission` for role/permission storage and assignment + native Laravel
  **Policies** for row-level/object-level checks (client data isolation, per-contact visibility). Enforced
  exclusively server-side; Inertia/Vue only reflects what the server already decided (hides UI, never the
  sole gate).
- **Queues/Scheduler:** Laravel queues (`database` driver for MVP, swappable to Redis later via `.env` only)
  for notification dispatch and renewal/SLA checks. Laravel Task Scheduling for the daily jobs (expiration
  scans, recurring maintenance generation, SLA breach sweep).
- **Cache/Sessions:** `database`/`file` drivers for MVP; Redis is a drop-in `.env` swap later, no code
  changes needed since Laravel abstracts this.
- **File storage:** Laravel Filesystem, `local` **private** disk for MVP (not `public` — nothing is
  directly web-accessible by guessable URL). All downloads go through a policy-gated streaming controller
  (`Gate::authorize()` then `Storage::response()`), which re-checks the session on every request — stronger
  than a bare signed URL. Config is disk-name-driven so switching to an `s3` disk later (Cloudflare
  R2/AWS S3 compatible) is a config change, not a rewrite.
- **Credentials vault:** Sensitive fields (FTP/SFTP/SSH/WP admin/hosting/registrar/API keys) use Eloquent's
  built-in `encrypted` cast (AES-256-CBC via `APP_KEY`) — never stored plaintext. Actual decryption for
  display only ever happens through a `CredentialVault` service method that also writes a
  `CredentialAccessLog` + global `AuditLog` row in the same transaction — no controller decrypts a
  credential directly.
- **Real-time:** Deferred (Phase 3) — Laravel Echo + Reverb. MVP uses plain page reloads / Inertia partial
  reloads for ticket replies and notifications, which is enough for an internal tool at this scale.
- **i18n:** Default locale **Greek (`el`)**, switchable to English. Backend strings via Laravel's native
  `lang/el/*.php` + `lang/en/*.php` (validation messages, notification/email text). Frontend UI strings via
  `vue-i18n` with `resources/js/lang/el.json` / `en.json`. Locale resolved from the authenticated user's
  stored `locale` preference (default `el` for guests) via a `SetLocale` middleware.
- **Testing:** Pest (Laravel's current default) for feature/unit tests covering auth, policies/client
  isolation, and core CRUD; Playwright added in Phase 2 for end-to-end golden-path checks.
- **Key packages:** `laravel/breeze` (vue stack, +TS), `spatie/laravel-permission`, `laravel/pint`,
  `pestphp/pest` + `pest-plugin-laravel`; npm: `vue`, `@inertiajs/vue3`, `typescript`, `vite`,
  `tailwindcss@^4`, shadcn-vue CLI output, `vue-i18n`, `ziggy` (JS `route()` helper for named Laravel
  routes), `@vueuse/core`, `date-fns`.
- **Deployment target:** Self-hosted VPS via Docker Compose (app container + PostgreSQL container),
  confirmed by the user; storage local now, S3-compatible later.

## Database Schema (entities & relations)

Standard `id`, `created_at`, `updated_at` on all tables; `SoftDeletes` (`deleted_at`) added where noted for
recoverable trash, distinct from business-level `status = archived` (still visible/read-only in Admin).

**Identity & access**
- `users` — name, email, password, phone, `locale`, `is_active`, `last_login_at`, `client_id` (nullable —
  set only for client/client_contact role users), timestamps. Roles/permissions via spatie tables
  (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`).
- `client_contacts` — pivot-with-extra-columns linking `users` ↔ `clients`: `contact_title` (owner /
  accountant / marketing / employee / custom), `is_primary`, `can_view_billing`, `can_view_invoices`,
  `can_view_requests`, `can_view_documents`, `can_create_requests`, `can_manage_account`. This is what
  makes "Accountant sees billing, not technical" and "Owner sees everything" possible per contact.

**Clients**
- `clients` (SoftDeletes) — company_name, contact_name, vat_number, tax_office, email, phone, address,
  billing_email, technical_email, status (lead/active/inactive/suspended/archived), priority, start_date,
  internal_notes, client_visible_notes, last_contact_at, next_action, cached `mrr`/`arr` (recomputed by a
  service, not hand-maintained).
- `tags`, `client_tags` (+ `service_tags`) — free-form manageable tags (WordPress, VIP, Difficult Client…).

**Websites / Projects**
- `websites` (SoftDeletes) — client_id, name, domain, production_url, staging_url, cms, wp_version,
  woo_version, theme, hosting_provider, hosting_package, server, server_ip, php_version, database_name,
  cdn_provider, cloudflare_enabled, ssl_provider, ssl_expires_at, analytics_id, search_console, smtp_provider,
  backup_provider, status, internal_notes, client_visible_notes. **Field-level privacy is enforced by
  Resource whitelisting, not a per-field DB flag** — see Permissions Model.

**Services / Renewals**
- `service_types` — name, category (hosting/domain/maintenance/woocommerce_maintenance/seo/ssl/email/
  plugin_license/theme_license/backup/cdn/development/support/other).
- `services` (SoftDeletes) — client_id, website_id, service_type_id, name, provider, start_date,
  expiration_date, renewal_date, billing_cycle, internal_cost, client_price, auto_renewal, status
  (active/pending/expired/cancelled), notes_internal, notes_client.
- Type-specific 1:1 extension tables (keep `services` generic, avoid one giant sparse table):
  `service_hosting_details` (package, server, ip, storage), `service_domain_details` (registrar,
  nameservers, dns_provider), `service_maintenance_details` (plan, included_hours, used_hours, sla),
  `service_license_details` (license_type, purchased_by, license_status).
- `service_renewals` — service_id, previous_expiration, new_expiration, renewed_at, renewed_by_user_id,
  amount_charged, status (upcoming/pending/contacted/approved/paid/renewed/expired/cancelled).

**Credentials vault**
- `credentials` — website_id, client_id (nullable), type (ftp/sftp/ssh/wp_admin/hosting/registrar/api_key/
  other), label, username, `secret` (encrypted cast), created_by, last_accessed_at.
- `credential_access_logs` — credential_id, user_id, action (view/reveal/edit/delete), ip_address,
  created_at — also mirrored into the global `audit_logs`.

**Requests / Helpdesk**
- `tickets` (SoftDeletes) — client_id, website_id, service_id (nullable), category, priority, status,
  title, created_by_user_id, assigned_to_user_id, sla_policy_id, first_response_at, resolved_at, closed_at.
- `ticket_messages` — ticket_id, author_user_id, body, `message_type` (client_message/admin_reply/
  internal_note/system) — **internal_note rows are simply never queried into client-facing props.**
- `files` — generic storage table reused by tickets/documents/maintenance/quotes: storage_disk,
  storage_key, original_filename, mime_type, size_bytes, uploaded_by_user_id.
- `ticket_attachments` — ticket_message_id, file_id.

**Tasks**
- `tasks` — client_id, website_id, related_ticket_id (nullable), title, description, assigned_to_user_id,
  priority, status (todo/in_progress/blocked/waiting/completed/cancelled), start_date, due_date,
  estimated_minutes, actual_minutes, tags, `client_visible` (default false — internal by default per the
  "safe default" rule).
- `task_checklist_items` — task_id, label, is_done, position.

**Reminders**
- `reminders` — created_by_user_id, owner_type (admin/client), client_id, website_id, service_id, ticket_id,
  invoice_id (all nullable FKs — a reminder can hang off any entity or none), title, description, remind_at,
  priority, repeat_rule (none/daily/weekly/monthly/quarterly/yearly/custom), assigned_to_user_id, status
  (upcoming/completed/dismissed).

**Notes** (visibility enforced at the DB/query level, not just UI)
- `notes` — client_id, website_id/service_id/ticket_id (nullable), author_user_id, **`visibility` enum:
  `internal` | `shared` | `system`, NOT NULL, default `internal`** — client-facing queries always add
  `whereIn('visibility', ['shared','system'])`; this makes "internal note leaked to client" structurally
  hard to do by accident.

**Invoices & Documents**
- `invoices` (SoftDeletes) — client_id, website_id (nullable), service_id (nullable), invoice_number,
  issue_date, due_date, subtotal, vat_amount, total, currency, status (draft/sent/paid/unpaid/overdue/
  cancelled), pdf_file_id.
- `documents` — client_id, website_id (nullable), type (invoice/contract/proposal/quote/technical_report/
  credentials_document/hosting_document/agreement/other), file_id, description, `visible_to_client`
  (default **false**), uploaded_by_user_id.

**Maintenance**
- `maintenance_logs` — website_id, performed_by_user_id, performed_at, duration_minutes, notes_internal,
  notes_client, `client_visible` (default true — this is the one thing that should default open, since its
  whole purpose is proving work to the client).
- `maintenance_items` — maintenance_log_id, item_key (wp_updated/woo_updated/plugins_updated/theme_updated/
  backup_created/db_optimized/security_scan/malware_scan/broken_links_checked/forms_tested/
  checkout_tested/cache_cleared/performance_checked/ssl_checked/uptime_checked/other), checked, note.
- `recurring_maintenance_schedules` (Phase 2) — website_id, frequency, task_template (jsonb), next_run_at.

**Notifications, Activity, Audit**
- `notifications` — Laravel's native notifications table (recipient, type, data jsonb, read_at).
- `activity_logs` — client_id, website_id, actor_user_id, action, description, `client_visible`, entity_type,
  entity_id — feeds the client-visible timeline.
- `audit_logs` — actor_user_id, action, entity_type, entity_id, previous_value (jsonb), new_value (jsonb),
  ip_address, created_at — **admin-only, immutable, never exposed to Client Portal**.

**Time tracking, Quotes, Health, SLA, Settings**
- `time_entries` — user_id, client_id, website_id, task_id/ticket_id (nullable), started_at, ended_at,
  duration_minutes, description, billable.
- `quotes` — client_id, website_id, title, description, subtotal, vat_amount, total, estimated_days,
  expires_at, status (draft/sent/viewed/approved/rejected/expired), approved_at, approved_by_user_id.
- `quote_items` — quote_id, description, quantity, unit_price, line_total.
- `website_health_snapshots` — website_id, checked_at, is_online, uptime_percentage, ssl_valid,
  ssl_expires_at, last_backup_at, wp_version, woo_version, plugin_updates_available, php_version,
  performance_score, security_status, source (manual/api), raw_payload (jsonb) — schema ready for
  automation even though MVP fills it manually.
- `sla_policies` — name, priority, first_response_target_minutes, resolution_target_minutes,
  business_hours_only.
- `settings` — key, value (jsonb), scope (global/user).

**Indexes/FKs:** every child table indexes its FK columns; `clients.vat_number`, `websites.domain`,
`invoices.invoice_number` get unique/lookup indexes for Global Search; all `client_id` FKs are `restrict`
on delete (never cascade-delete a client's financial/legal trail).

## Permissions Model

- **Roles** (spatie, guard `web`): `super_admin`, `admin`, `staff`, `client`, `client_contact`. Additional
  admin/staff users can be added freely later (multi-admin is just "add another user with role admin/staff").
- **Permissions** (spatie), examples: `clients.viewAny`, `clients.manage`, `services.viewFinancials`,
  `tickets.viewAny`, `tickets.assign`, `tickets.internalNotes.view`, `tasks.manage`, `invoices.manage`,
  `credentials.view`, `credentials.reveal`, `audit.view`, `reports.view`, `settings.manage`,
  `timeentries.manage`. Seeded per role; `staff` gets a deliberately narrower default (no
  `credentials.reveal`, no `settings.manage`) than `admin`.
- **Row-level scoping via Policies** (the actual security boundary, always re-checked server-side
  regardless of role): every Policy's `view`/`update` for a `client`/`client_contact` user checks the
  requested model's `client_id` against the user's own `client_id` (or `client_contacts` membership) —
  this is what makes "a Client can never see another Client's data" structural rather than a UI rule.
- **Per-contact granular permissions** live on `client_contacts` (see schema) — a `ClientPortalPolicy`
  reads these flags before allowing access to billing/invoices/requests/documents/account-management inside
  that client's own portal, satisfying the Accountant-vs-Marketing-vs-Owner example.
- **Field-level privacy default-deny:** the Client Portal never reads raw Eloquent models into Inertia
  props. Every client-facing page goes through a dedicated Resource/Data-transformer
  (e.g. `ClientFacingWebsiteResource`) that whitelists exactly the client-safe fields. A new internal field
  added later is invisible to clients until someone explicitly adds it to that whitelist — the safe
  direction by construction.
- **Route-level gating** (defense in depth, not the real gate): `/admin/*` requires
  `EnsureRole:super_admin,admin,staff`; `/portal/*` requires `EnsureRole:client,client_contact`; a
  mismatched role is redirected to their own portal, never shown a 403 that leaks structure.
- Internal-vs-client-vs-system distinctions (`notes.visibility`, `documents.visible_to_client`,
  `tasks.client_visible`, `maintenance_logs.client_visible`) default to the private/internal option
  wherever the spec leaves ambiguity, per the user's own rule #9.

## Folder Structure

```
app/
  Console/Commands/           renewal:check, sla:check, maintenance:generate-recurring
  Http/
    Controllers/Admin/        ClientController, WebsiteController, ServiceController, TicketController, ...
    Controllers/Client/       DashboardController, WebsiteController, RequestController, ...
    Middleware/                EnsureAdminPortal, EnsureClientPortal, SetLocale, HandleInertiaRequests
    Requests/                  per-entity Form Requests
    Resources/                 Admin* and ClientFacing* whitelisted transformers
  Models/                     Client, Website, Service, Ticket, TicketMessage, Task, Note, Invoice,
                               Document, MaintenanceLog, Credential, AuditLog, TimeEntry, Quote, ...
  Policies/                   ClientPolicy, WebsitePolicy, TicketPolicy, InvoicePolicy, CredentialPolicy, ...
  Services/                   RenewalService, SlaService, NotificationDispatcher, CredentialVault,
                               MaintenanceScheduler, AuditLogger
  Notifications/               TicketReplied, DomainExpiringSoon, InvoiceOverdue, MaintenanceCompleted
  Events/ + Listeners/          TicketMessageCreated -> NotifyAdmin/NotifyClient, ServiceRenewed -> LogActivity
resources/
  js/
    Pages/Admin/...            Dashboard, Clients/{Index,Show}, Websites, Requests, Tasks, Calendar,
                                Renewals, Services, Invoices, Documents, Maintenance, Reports, Settings
    Pages/Client/...           Dashboard, Websites/{Index,Show}, Services, Requests/{Index,Show}, Invoices,
                                Documents, Notes, Reminders, Notifications, Account
    Layouts/                   AdminLayout.vue, ClientLayout.vue, AuthLayout.vue
    Components/admin/          DataTable, PriorityBadge, StatusBadge, Sidebar, GlobalSearch, NotificationBell
    Components/client/         SimpleCard, ExpirationBadge
    Components/ui/             shadcn-vue primitives (shared)
    composables/                useAuth, usePermissions, useNotifications
    lang/                      el.json, en.json (vue-i18n)
    types/                      TS interfaces mirroring backend Resources
routes/  web.php, admin.php, client.php, auth.php
database/  migrations/, seeders/ (DemoDataSeeder), factories/
lang/    el/*.php, en/*.php   (backend validation/notification strings)
tests/   Feature/, Unit/       (Pest)
```

## Navigation

**Client Portal** (`/portal/*`): Dashboard · My Websites · My Services · Requests · Invoices · Documents ·
Notes · Reminders · Notifications · Account. Minimal chrome, top nav, card-based, no internal
CRM-style density.

**Admin Portal** (`/admin/*`): Dashboard (today's work queue + Next Action + priority queue) · Clients ·
Websites · Requests · Tasks · Calendar · Renewals · Services · Invoices · Documents · Maintenance ·
Notifications · Reports · Settings. Sidebar + header with global search and notification bell. Client
profile is tabbed: Overview / Websites / Services / Requests / Tasks / Notes / Reminders / Invoices /
Documents / Maintenance / Activity / Contacts / Time Tracking / Financials.

## MVP Implementation Plan (Phase 1 — built now, stage by stage)

0. **Scaffolding** — `laravel new`, Breeze Vue/Inertia stack + TypeScript conversion, Tailwind 4,
   shadcn-vue init, PostgreSQL connection, Docker Compose (app + postgres), Pint/Pest setup, vue-i18n with
   `el` default locale wired through `SetLocale` middleware.
1. **Core schema & seed** — migrations for users/roles/permissions (spatie), clients, client_contacts,
   websites, service_types/services (+ detail tables), tags. `DemoDataSeeder` producing the ABC Fishing
   example from the spec (aboutfishing.gr, hosting/domain/maintenance services with real dates/prices).
2. **Auth** — Breeze-based login/logout/password-reset/email-verification, role-aware post-login redirect
   (admin → `/admin/dashboard`, client → `/portal/dashboard`).
3. **Authorization core** — spatie roles/permissions seeded, base Policies (Client/Website/Service) with
   row-level scoping, `EnsureAdminPortal`/`EnsureClientPortal` middleware, Pest tests proving client
   isolation (client A cannot fetch client B's records via any route).
4. **Admin shell + Clients** — AdminLayout (sidebar/header), Clients index (search/filter/sort/archive) +
   full Client Profile with Overview/Websites/Services tabs wired to real data.
5. **Websites + Services** — CRUD, expiration tracking, renewal status coloring (green/yellow/red).
6. **Requests/Tickets** — conversation thread (client_message/admin_reply/internal_note/system), category/
   priority/status per spec, attachments via the `files` table, distinct internal-note styling.
7. **Tasks** — CRUD, checklist items, linkage to a ticket, client_visible flag.
8. **Notes + Reminders** — 3-tier note visibility enforced at query level; reminders with repeat_rule.
9. **Invoices + Documents** — file storage abstraction, policy-gated download controller, visible_to_client
   gating.
10. **Maintenance logs** — checklist-style maintenance_items, client-visible history feed.
11. **Notifications + Activity + Audit logs** — in-app notifications (Laravel Notifications DB channel),
    client-visible activity timeline, admin-only immutable audit log (including credential access).
12. **Client Portal build-out** — ClientLayout + all client pages consuming the same backend through
    `ClientFacing*` whitelisted Resources (Dashboard, My Websites, My Services, Requests, Invoices,
    Documents, Notes, Reminders, Notifications, Account).
13. **Global Search** — cross-entity search (client, website, ticket, invoice, note, service, document) by
    name/VAT/domain, respecting the same row-level scoping as everything else.
14. **End-to-end pass** — run the seeded demo data through both portals in a real browser: create a ticket
    as client, reply + add internal note as admin, confirm the internal note never renders client-side;
    confirm client A/B isolation; confirm invoice/document visibility toggles work.

**Phase 2 (documented roadmap, not built now):** Calendar, Renewals management views, Time tracking,
Financial analytics (MRR/ARR/margin), Quotes/Approvals, recurring maintenance automation, SLA tracking,
Reports, 2FA.

**Phase 3 (roadmap):** Website health monitoring/API integrations, uptime/SSL/WordPress version automation,
Laravel Echo/Reverb real-time, email/push/Telegram/Slack notification channels.

## Verification

- Pest feature tests for: login/redirect-by-role, client data isolation across every major entity,
  internal-note-never-visible-to-client, document/invoice visibility gating, credential reveal audit
  logging.
- Manual browser walkthrough of the seeded ABC Fishing scenario in both portals after stage 14, checking
  the golden path (ticket lifecycle, invoice visibility, maintenance history, note visibility) and the
  explicit business rules (`rule #1–#9` in the original spec) as a checklist.
- `php artisan test`, `vendor/bin/pint --test`, `npm run build` (TS type-check) must all pass before a
  stage is considered done.
