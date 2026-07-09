> **SaaS Template Playbook** › [Mục lục](../README.md) › Chiến lược sản phẩm › Plan xương sống (Phase 1–5)

# Implementation Plan — Xương sống (Phase 1–5)

Plan **cấp cao để review**. Phạm vi: dựng nền móng greenfield Laravel chạy được, multi‑tenant an toàn, có auth + billing — đủ để mọi domain (`app/Product/*`) cắm vào. Bám [lộ trình 14 phase](./roadmap-14-phase.md); đây là 5 phase đầu, chi tiết hơn ở mức milestone + deliverable.

**Tổng: ~10 ngày công.** Nguyên tắc: *đừng nhảy cóc* — mỗi phase ship được mới sang phase sau.

```
Phase 1 Skeleton ─► Phase 2 Auth ─► Phase 3 Tenancy ─┬─► Phase 4 Notifications
                                                      └─► Phase 5 Billing (cần P4 cho dunning email)
```

| Phase | Mục tiêu | "Done" khi | Tham chiếu |
|---|---|---|---|
| **1. Skeleton** (2d) | App rỗng chạy, CI xanh, ranh giới Kernel/Product dựng sẵn | `git clone && sail up` → app load; `./vendor/bin/sail composer check` xanh; deptrac chạy | [§4](../architecture/overview.md), [§26](../architecture/pillars/26-cicd-release.md), [§27](../architecture/pillars/27-dx.md), [§32](../architecture/kernel-product.md) · ADR [0001](../decisions/0001-postgresql.md)/[0003](../decisions/0003-modular-monolith.md)/[0004](../decisions/0004-layering.md)/[0007](../decisions/0007-uuid-v7.md)/[0014](../decisions/0014-deptrac-boundaries.md)/[0015](../decisions/0015-frontend-inertia-react.md) |
| **2. Auth** (2d) | Đăng ký/verify/login/reset/MFA | signup→verify→logout→login→reset chạy | [§6](../architecture/pillars/06-auth.md) · ADR [0005](../decisions/0005-auth-strategy.md) |
| **3. Tenancy** (2d) | Cô lập tenant 2 lớp (scope + RLS), RBAC | Teammate được mời chỉ thấy workspace mình; cross‑tenant bị RLS chặn | [§5](../architecture/multi-tenancy.md), [§7](../architecture/pillars/07-accounts-teams.md) · ADR [0002](../decisions/0002-multitenancy-pool-rls.md) |
| **4. Notifications** (1d) | Mail + inbox + broadcast, preference | Invite email tới Mailpit + inbox; bell hiện mention | [§14](../architecture/pillars/14-notifications.md) |
| **5. Billing** (3d) | Stripe end‑to‑end | Chọn plan/trả/nâng/hạ; thanh toán fail → UX đúng | [§9](../architecture/pillars/09-billing.md) · ADR [0006](../decisions/0006-billing-stripe-cashier.md) |

## Deliverables chính

**Phase 1 — Skeleton:** Laravel 12 + Sail (Postgres 16 `pg_trgm`/`pgvector` + Redis + Mailpit + pgweb) · **starter kit React/Inertia** (Tailwind + shadcn) theo [ADR-0015](../decisions/0015-frontend-inertia-react.md) · health endpoint · structured log JSON + `RequestID` middleware · chuỗi middleware skeleton (§4.2) · cây `app/Kernel/*` + `app/Product/*` + `deptrac.yaml` · 6 Kernel Contracts (interface) · `config/product.php` · convention bake sẵn (`HasUuids` UUID v7) · composer scripts (`dev/test/lint/stan/deptrac/check`) · 1 CI job.

**Phase 2 — Auth:** Fortify (headless) + Sanctum (cookie SPA) + Socialite (Google) · magic link · email verify (không chặn vào app) · password reset · rate‑limit `/login` · `argon2id` · session regenerate · checklist bảo mật (§6.4). RBAC để ở Phase 3.

**Phase 3 — Tenancy (quan trọng nhất):** bảng `workspaces`/`memberships`/`invites` · trait `BelongsToTenant` + `TenantScope` · `Tenant` middleware (`set_config('app.workspace_id', …)`) · **RLS policy** (`NULLIF(current_setting(…, true),'')`) · `spatie/laravel-permission` (owner/admin/member/viewer) · workspace switcher (UI Inertia) · invite→accept · Policy đối chiếu `tenant()->id` *(không dùng `$user->workspace_id`)*.

**Phase 4 — Notifications:** Mailer qua contract (Resend/Postmark) · `Notification` 3 channel (mail/database/broadcast) · inbox + `notification_preferences` (check trước khi gửi) · bell + unread. *(Worker async đầy đủ ở Phase 6 — tạm sync queue ở dev.)*

**Phase 5 — Billing:** Cashier (Checkout + Portal) · bảng `plans` (`price_cents` + `billing_interval`) + `subscriptions` provider‑agnostic · webhook **idempotent** (handler tùy biến tự dedup theo `event.id`) · trial no‑card · feature gating (`can()` + upgrade prompt) · dunning · metering qua `Meter` contract (Redis, không gọi Stripe đồng bộ) · admin override.

## Bake sẵn từ Phase 1 (convention xuyên suốt)
UUID v7 · `workspace_id` + RLS trên mọi bảng tenant · tạo `audit_logs` sớm (wiring đầy đủ ở Phase 10) · webhook idempotent · validate phía server · migration backward‑compatible · secret không commit.

## Quyết định đã chốt / còn mở
- ✅ **Frontend stack** → **Inertia v2 + React + TS + Tailwind + shadcn** ([ADR-0015](../decisions/0015-frontend-inertia-react.md)).
- ⬜ **Provider cụ thể**: mail (Resend hay Postmark), OAuth (chỉ Google hay thêm GitHub).
- ⬜ **Host deploy** (Forge / Vapor) cho CI Phase 1.
- ⬜ **Đường dẫn repo greenfield** + bootstrap hoàn toàn qua Docker/Compose.

## Tiền đề kỹ thuật
Docker (Sail) · Stripe test account (Phase 5) · Resend/Postmark key hoặc Mailpit (dev).

## Ngoài phạm vi plan này (Phase 6–14)
Jobs/Cron · Files · Search · Real‑time · Audit/Activity/Telemetry · Flags/Admin · Security hardening · Observability · Package/Reskin.
