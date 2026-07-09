<!--
Sync Impact Report
Version change: 1.0.0 -> 1.1.0
Modified principles:
 - None
Added sections:
 - None
Removed sections:
 - None
Templates requiring updates:
- ✅ updated .specify/templates/plan-template.md
- ✅ updated .specify/templates/spec-template.md
- ✅ updated .specify/templates/tasks-template.md
- ✅ updated README.md
- ✅ updated CONTRIBUTING.md
- ✅ updated docs/guides/fork-from-template.md
- ✅ updated docs/architecture/pillars/27-dx.md
- ✅ updated docs/strategy/implementation-plan-phase1-5.md
- ⚠ pending .specify/templates/commands/*.md (directory not present in this repository)
Follow-up TODOs:
- None
-->
# SaaS Template Constitution

## Core Principles

### I. Code Quality by Design
All production changes MUST preserve modular boundaries, keep controllers thin,
and pass the repository's lint, static analysis, boundary, and test gates before
merge unless an exception is explicitly approved. New abstractions MUST earn
their cost through clearer ownership, reduced coupling, or improved testability;
speculative indirection is prohibited. Rationale: this template scales through
predictable seams, not through cleverness.

### II. Test Evidence Over Assumption
Every behavior change MUST ship with automated test evidence at the
highest-leverage layer affected: unit tests for isolated logic, integration tests
for persistence and module seams, and end-to-end coverage for critical journeys
or cross-cutting regressions. A lower-value layer may be omitted only when the
plan or PR records why existing coverage and targeted manual verification are
sufficient. Rationale: balanced delivery still requires evidence, not confidence
alone.

### III. Consistent User Experience
User-facing changes MUST reuse established design tokens, interaction patterns,
copy tone, and accessibility conventions across loading, empty, error, and
success states. Specs and PRs MUST describe the primary journey, affected edge
states, and any intentional UX deviation. Rationale: consistency lowers support
cost, reduces user confusion, and keeps Kernel and Product surfaces cohesive.

### IV. Performance Is a Feature
User-facing endpoints and interactions MUST declare and respect explicit
performance expectations before release. The default repository budget remains
API latency under 500 ms p95 unless a stricter feature budget is defined;
expensive work MUST move to asynchronous paths when it cannot stay within that
budget. Data-access changes on hot paths MUST review query shape, caching,
observability, and failure modes. Rationale: latency and resource regressions
erode trust as quickly as functional bugs.

### V. Balanced Delivery, Explicit Exceptions
These principles are mandatory by default, but a temporary exception MAY be
accepted when its scope, risk, mitigation, owner, and cleanup trigger are
recorded in `plan.md`, `tasks.md`, or the PR. Untracked exceptions are
non-compliant. Rationale: delivery speed matters, but hidden compromises
compound into fragile systems.

## Engineering Constraints

- Kernel MUST NOT import Product code, and module boundaries MUST remain
  enforceable with `deptrac`.
- All repository commands for PHP, Composer, Node, npm, Artisan, tests, lint,
  builds, and code generation MUST run inside the project's Docker environment.
  After dependencies exist, use `./vendor/bin/sail ...` or
  `docker compose exec ...`; before Sail is available, bootstrap through
  Dockerized utilities such as `docker run ... composer install`. Direct host
  execution of project commands is prohibited unless an explicit constitution
  exception is approved.
- Business rules MUST live in services or domain-level collaborators; controllers,
  jobs, and UI handlers orchestrate but do not own core logic.
- Tenant-scoped behavior MUST preserve `workspace_id` isolation, auditability,
  and append-only migration safety.
- Cross-cutting integrations such as billing, storage, mail, flags, and
  background work MUST use established contracts and asynchronous patterns where
  available.
- Architectural or public-contract changes MUST update the relevant ADR, guide,
  or operational documentation in the same change set.

## Delivery Workflow & Quality Gates

- `spec.md` MUST capture the user journey, quality requirements, UX states, and
  measurable success criteria, including performance expectations when relevant.
- `plan.md` MUST complete the Constitution Check with code-quality impact, test
  strategy, UX consistency approach, performance budget, Docker execution path,
  and any requested exception records.
- `tasks.md` MUST include the test work needed to produce the promised evidence,
  plus UX-state validation, performance or observability tasks when the feature
  changes those concerns, and Docker-based verification commands where command
  execution is part of the task.
- PRs and reviews MUST verify constitution compliance, document exceptions, and
  include screenshots or interaction notes for UI changes plus validation notes
  for performance-sensitive work and confirmation that project commands were run
  through Docker.

## Governance

This constitution supersedes conflicting local habits and planning defaults.
Amendments require a pull request that updates this file and any impacted
templates or guidance documents in the same change set. Versioning follows
semantic rules: MAJOR for incompatible principle removals or redefinitions,
MINOR for new principles or materially stronger governance, and PATCH for
clarifications that do not change enforcement. Compliance is reviewed in every
feature plan and pull request; unresolved violations MUST be documented as
explicit exceptions before merge.

**Version**: 1.1.0 | **Ratified**: 2026-07-01 | **Last Amended**: 2026-07-09
