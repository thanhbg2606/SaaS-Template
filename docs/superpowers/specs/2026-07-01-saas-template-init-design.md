# SaaS Template Init Design

**Date**: 2026-07-01
**Topic**: Scaffold this repository into a runnable Laravel SaaS template with
Docker-first local development
**Status**: Draft for review

## Goal

Turn this repository from a docs-first blueprint into a runnable
`Laravel + React/Inertia + Sail` template that reaches a practical
`Dev ready` state for local development.

The result should let a new engineer clone the repository, follow the quick
start, boot the application with Docker/Sail, run migrations and seeds, and
start the normal development processes without undocumented setup work.

## Non-Goals

- Implement full multi-tenancy, billing, admin, or observability features
- Reproduce every capability currently described in the architecture docs
- Finalize long-term module boundaries beyond the minimum skeleton needed now
- Optimize for production deployment in this phase

## Recommended Approach

Use the official Laravel application scaffold with the React/Inertia starter kit
as the upstream base, then apply a thin template layer on top of it.

This is preferred over building the stack manually because it keeps the
application close to upstream conventions, lowers bootstrap risk, and makes
future upgrades easier. It also gets the repository to a real `Dev ready` state
faster than attempting to hand-assemble auth, frontend, and local runtime
integration from scratch.

The architectural target remains a **Modular Monolith**. The initial scaffold
must therefore preserve module seams and avoid package choices that would take
over the template's architectural direction.

## Scope

### Included

- Scaffold a Laravel app directly in this repository
- Use the React/Inertia starter kit for the initial app shell
- Enable Docker-first local development via Laravel Sail
- Add the minimum template structure needed for future SaaS work:
  `app/Kernel/`, `app/Product/`, and `config/product.php`
- Provide demo seed data so the local environment is inspectable after setup
- Align the repository so the quick-start path reflects actual runnable steps

### Deferred

- Real multi-tenancy with RLS and workspace isolation
- Billing flows and Stripe integration
- Admin panel and advanced operational surfaces
- Full observability and performance instrumentation
- Deep modular monolith enforcement beyond starter boundaries

## Architecture Decision

### Modular Monolith Now

The repository should implement a Modular Monolith from the start: one
deployable Laravel application, one primary database, and explicit boundaries
between reusable platform capabilities and product-specific business domains.

### Microservice-Ready Later

The module seams should be designed so individual business capabilities can be
extracted into separate services later without a full rewrite. That means
module boundaries must follow business capabilities, not only technical folders.

### No Module Package as the Core Architecture Driver

The implementation should **not** depend on a package such as
`nwidart/laravel-modules` to define the architecture. The template's own
`app/Kernel/*` and `app/Product/*` structure is the primary source of modular
boundaries. This keeps the codebase aligned with the existing docs and avoids
locking the architecture to a third-party module convention.

### Enforce Boundaries with Deptrac

Boundary rules should be enforced automatically with `deptrac`, not left as
documentation only. At minimum, the ruleset should protect:

- `Kernel` must not depend on `Product`
- controllers must not jump directly to repository/persistence code
- product modules must not reach into other product modules except through
  approved seams

### Use Contracts and Domain Events as Seams

Cross-module interaction should prefer stable seams such as contracts
(`app/Kernel/Contracts`) and domain events/listeners. Modules should not depend
directly on each other's internal models and implementation details. This keeps
the monolith easier to evolve now and easier to split later.

## Architecture

The implementation will have three layers:

1. **Starter layer**
   The standard Laravel app, React/Inertia frontend, auth scaffolding, Vite,
   tests, and default framework configuration.

2. **Template layer**
   Product-facing configuration, demo seed data, repository conventions, and
   quick-start alignment that make the repo behave like a reusable SaaS
   template instead of a generic starter project.

3. **Future boundary layer**
   Initial `Kernel` and `Product` directories that establish where shared
   platform code and product-specific domain code will live as the template
   evolves. `app/Product/` is the product domain layer for real business
   modules, not a demo-only folder. Dependency flow is one-way:
   `Kernel -> Product` is forbidden, while `Product` extends platform behavior
   through configuration, contracts, and events.

This approach intentionally avoids pretending the template already has all SaaS
capabilities wired up. Instead, it creates a clean, working base that can host
those capabilities in later iterations.

## Laravel Implementation Architecture

The Laravel implementation will start from the official starter structure, then
be organized into a template-friendly modular layout.

### 1. Framework and bootstrap layer

This layer remains close to standard Laravel so upgrades stay manageable.

- `bootstrap/`, `public/`, `storage/`, `config/`, and `database/` keep normal
  Laravel responsibilities
- `routes/` remains the HTTP entrypoint
- framework-level providers and environment wiring stay in standard Laravel
  locations

### 2. Delivery layer

This layer handles HTTP and UI delivery while staying thin.

- `routes/web.php` and related route files define entrypoints
- controllers and form requests orchestrate requests and validation
- Inertia pages and shared React UI provide the app shell and user-facing
  screens
- authentication scaffolding from the starter kit is kept close to upstream in
  the first iteration

Controllers should stay thin and delegate business behavior instead of holding
domain logic directly.

### 3. Kernel layer

`app/Kernel/` contains reusable platform capabilities that any SaaS product
built from the template can share.

Typical contents in later iterations may include:

- `Auth/`
- `Billing/`
- `Notifications/`
- `Support/`
- `Contracts/`

In the initial scaffold phase, this layer may only contain foundational
directories, namespaces, and a small amount of shared support code. The key
goal is to reserve a stable home for reusable platform logic and shared seams
such as contracts and events.

### 4. Product layer

`app/Product/` contains the real product-specific business domains of the app
being built on top of the template.

Example product modules:

- `Projects/`
- `Issues/`
- `Tasks/`

Each product module should eventually group its own:

- `Models/`
- `Services/`
- `Http/`
- `Policies/`
- `Events/`
- `Jobs/`

This is the main extension surface for teams using the template. It is where
product-specific workflows, entities, and use cases belong. Product modules
should not couple directly to each other's internals if the same behavior can
flow through a contract, event, or explicit application service.

### 5. Configuration layer

`config/product.php` is the template customization boundary for product-level
configuration such as:

- brand identity
- feature flags
- plan definitions
- onboarding defaults

This keeps product variation in configuration where possible instead of forcing
every customization into code changes.

### 6. Data and seed layer

The initial implementation will use standard Laravel migrations, factories, and
seeders.

- framework and starter tables come from the Laravel base
- demo seed data is added so local development is usable immediately
- product-specific sample data belongs in seeders, not ad hoc manual setup

### 7. Execution flow

The intended request flow is:

`Route -> FormRequest -> Controller -> Service -> Model/persistence -> Inertia response`

For the first runnable skeleton, persistence may still lean on standard
Eloquent patterns from the starter kit where introducing extra abstraction
would add ceremony without value. As the template matures, reusable or complex
domains can be pulled into stricter module boundaries in `Kernel` and
`Product`.

For cross-module behavior, the intended interaction flow is:

`Module A -> Contract or Domain Event -> Module B listener/adapter`

That flow keeps in-process communication compatible with a later move toward
queues, workers, or extracted services.

## Implementation Flow

1. Scaffold the upstream Laravel app with React/Inertia in the current
   repository.
2. Enable or install Laravel Sail and generate the local Docker runtime files.
3. Create environment files and install PHP and frontend dependencies.
4. Add minimal template-specific structure and configuration:
   - `app/Kernel/`
   - `app/Product/`
   - `config/product.php`
5. Add demo seed data so local development has a known baseline.
6. Run the local development path:
   - `sail up -d`
   - `sail artisan migrate --seed`
   - `sail composer dev`
7. Verify the repository reaches the agreed `Dev ready` state.

## Dev Ready Definition

The repository is considered `Dev ready` only when all of the following are
true:

- The repo contains real Laravel app artifacts such as `composer.json`,
  `artisan`, environment templates, routes, resources, database, and tests.
- Docker/Sail can boot the required local services without bootstrap failure.
- Database migrations and demo seeds complete successfully.
- The application shell is reachable locally and the starter auth flow works.
- Normal developer processes can be started through the documented dev command.

## Verification Plan

Verification will focus on observable outcomes instead of only file existence.

### Environment Verification

- Confirm Laravel app artifacts exist
- Confirm Sail runtime files exist
- Confirm frontend build tooling exists

### Runtime Verification

- Confirm containers start successfully
- Confirm migrations and seeding complete successfully
- Confirm app and supporting local services are reachable

### Developer Workflow Verification

- Confirm the documented development command starts the expected processes
- Confirm demo data provides an immediately usable local baseline

## Risks

### Repository Shift

This repository will change from docs-first to runnable-template-first. That is
intentional, but it means a large set of framework files will be added in one
step.

### Upstream Version Drift

The current upstream Laravel starter output may not perfectly match every
version reference or future-state claim already present in the docs.

### Network and Tooling Constraints

Scaffolding, dependency installation, and Docker-based startup will require
network access and may need execution outside the current sandbox.

## Risk Mitigations

- Scaffold upstream first, customize second
- Keep this phase strictly limited to `Dev ready`
- Avoid inventing large fake SaaS subsystems just to satisfy docs wording
- Reconcile the README against what is truly runnable after scaffold
- Treat advanced SaaS capabilities as follow-up milestones rather than hidden
  scope

## Expected Deliverables

- A runnable Laravel application in this repository
- Docker/Sail local development support
- Minimal template structure for future SaaS layering
- A clear separation between shared platform code in `app/Kernel/` and
  product-specific domain code in `app/Product/`
- An initial architecture direction that is Modular Monolith now and
  microservice-ready later
- A plan to enforce boundaries with `deptrac` rather than relying on developer
  discipline alone
- Demo seed data
- Updated quick-start alignment between repository behavior and docs

## Acceptance Criteria

- A new engineer can follow the repository setup path and start the app with
  Docker/Sail
- The local app can be migrated and seeded successfully
- The app shell and starter authentication surfaces are reachable
- The repo now behaves like a real bootstrap point for future SaaS template
  work, not only a documentation package

## Open Decisions Resolved

- **Local runtime**: Docker/Sail
- **Scaffold strategy**: Starter-kit first
- **Target level**: Dev ready
- **Execution location**: Scaffold directly in this repository
- **Architecture style**: Modular Monolith with microservice-ready seams
- **Module system**: Native template boundaries, not `laravel-modules`
- **Boundary enforcement**: `deptrac` plus contracts and domain events
