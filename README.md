# Weberry Portal

Internal Client Management / CRM / Helpdesk / Service Management / Maintenance Management / Business
Operations platform for a web development, WordPress, WooCommerce, hosting and maintenance business.

Two strictly separated interfaces:

- **Admin Portal** — the operational hub: clients, websites, services, tickets, tasks, renewals,
  maintenance, invoicing, financials, audit.
- **Client Portal** — a minimal self-service portal scoped to each client's own data only.

## Stack

- Laravel 13, PHP 8.3+
- Vue 3 + TypeScript via Inertia 3
- Tailwind CSS 4 + shadcn-vue
- PostgreSQL
- Docker Compose for local/self-hosted deployment

See `docs/plan.md` for the full architecture, database schema, permissions model and implementation
roadmap.

## Branching

- `main` — stable/deployable
- `dev` — active development
