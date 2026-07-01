> **SaaS Template Playbook** › [Mục lục](../README.md) › Chiến lược sản phẩm › Thực thi
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../section-map.md).

# Lộ trình, bẫy & cheat sheet (§33–§35)

## 33. Lộ trình 14 phase (bản Laravel)

Mỗi phase ship được. **Đừng nhảy cóc** — đa số thất bại là làm phase 7 khi phase 3 chưa chắc.

| Phase | Nội dung | Done khi |
|---|---|---|
| **1. Skeleton** (2d) | Sail (PG+Redis+Mailpit+pgweb), health endpoint, structured log, request-id middleware, 1 CI job (Pint+Pest) | `git clone && sail up` → app rỗng load, chưa auth |
| **2. Auth** (2d) | Fortify + Sanctum + Socialite (Google), magic link, verify email, reset, rate-limit `/login` | User đăng ký/verify/logout/login/reset được |
| **3. Tenancy** (2d) | `workspaces`/`memberships`/`invites`, global scope + RLS, `spatie/permission`, workspace switcher, roles | Teammate được mời chỉ thấy workspace của mình; cross-tenant bị RLS chặn |
| **4. Notifications** (1d) | Resend/Postmark, Notification 3 channel (mail/database/broadcast), preference | Invite email tới Mailpit (dev) + inbox thật; bell hiện mention |
| **5. Billing** (3d) | Cashier (Checkout + Portal), plans + subscriptions + webhook, trial, feature gating, dunning, admin override | Chọn plan/trả/nâng/hạ; thanh toán fail → UX đúng |
| **6. Jobs & Cron** (1d) | Horizon, worker trong Sail, job mẫu (send email/sync Stripe/expire trial), scheduler `onOneServer`, outbox (Events + afterCommit) | Job 10s chạy trong worker, API vẫn nhanh, cron chạy 1 lần trên N replica |
| **7. Files** (1d) | S3/R2 bucket per env, signed-URL upload, confirm endpoint, avatar mẫu, CDN | User upload avatar serve qua CDN, byte không chạm API |
| **8. Search** (1d) | Scout (database/Meili), hybrid BM25+trgm, (tùy) pgvector + embedding worker | Gõ search bar trả kết quả < 200ms |
| **9. Real-time** (1d) | Reverb + Echo, channel theo tenant, WS invalidate Query cache, presence | Hai tab thấy cùng update đồng thời |
| **10. Audit/Activity/Telemetry** (1d) | `activitylog`, bảng `activity`, PostHog với event chuẩn, activation + retention dashboard | Mọi action đặc quyền vào audit log; mọi signup vào PostHog |
| **11. Flags & Admin** (2d) | Pennant, resolve env/workspace/user, Filament admin (search/impersonate read-only/suspend/override flag), audit với staff actor | Support xử lý ticket "tôi không thấy X" < 5 phút |
| **12. Security & Compliance** (1d) | CSP/HSTS/secure cookie/CSRF, gitleaks CI, GDPR primitive (export/delete/consent), DPA + subprocessor page, ZAP scan | Security review pass OWASP Top 10 không cần sửa |
| **13. Observability** (1d) | OTel + Sentry + Pulse, log/metric/trace tag `request_id`+`tenant_id`, Grafana golden signals, status page, 1 SLO | Click lỗi Sentry → trace → log → request |
| **14. Package & Reskin** (2d) | `Kernel/`↔`Product/`, `config/product.php`, reskin guide, marketing template, docs template, README+ADR | Engineer mới fork + reskin → skeleton có brand riêng trong 30 phút |

**Tổng: ~21 ngày công cho một engineer giàu kinh nghiệm → MVP-quality template. ~6–8 tuần lịch kèm review, polish, docs.**

## 34. Bẫy thường gặp & guardrail

| Bẫy | Guardrail |
|---|---|
| Quên `WHERE workspace_id = ?` đâu đó | RLS trên mọi bảng tenant; CI grep filter thiếu; global scope |
| Webhook handler không idempotent | Tự lưu `event.id` đã xử lý làm dedup key (Cashier converge state nhưng KHÔNG dedup theo event.id; handler tùy biến → DB/Redis 7d) |
| Job dài block request path | Đẩy vào queue; không gọi bên thứ ba đồng bộ |
| Admin action không audit-log | Middleware bọc mọi admin handler ghi audit log |
| Email enumeration | Cùng response + cùng timing cho "tồn tại" vs "không" |
| Migration phá rolling deploy | 2-phase; không drop+rename một phát |
| WS ghi thẳng client store | Quy tắc: WS chỉ invalidate cache query |
| Cookie auth không CSRF | `SameSite=Lax` + CSRF token |
| Secret commit vào git | `gitleaks` pre-commit + CI fail |
| Abuse free tier (signup farming) | Rate-limit signup/IP + block email domain + Cloudflare Turnstile |
| Đổi plan không nhất quán | Handler đổi plan: enforce limit, archive overflow, email user |
| Trial hết khi user có 50 issue | Read-only + banner; **không xóa data** |
| N+1 nóng ở trang detail | `EXPLAIN ANALYZE` trong CI; `preventLazyLoading` |
| Cache không bao giờ invalidate | Tag-based; không set TTL > 1h mà không có hook invalidate |
| Data tenant lộ qua search index | Key search index gồm `workspace_id`, query filter theo nó |
| CORS sai mở API cho origin xấu | Allowlist origin tường minh; từ chối `*` với credentials |
| User xóa được audit log của mình | Audit log append-only; không endpoint cho user sửa |
| Một query chậm hạ cả API | `SET LOCAL statement_timeout = '5s'` |
| Worker fail âm thầm mãi mãi | Dead-letter queue + alert khi DLQ depth tăng |
| Test data lọt vào prod | Connection string khác nhau; banner to ở non-prod |
| Không consent log → GDPR fail | Bảng `consents` với version + timestamp + IP từ ngày đầu |

## 35. Cheat Sheet

### File / quyết định cần chốt đầu tiên

1. **Multi-tenancy** — pool, mọi query lọc `workspace_id`, RLS phòng thủ.
2. **Auth** — cookie session (Sanctum) cho browser, token cho mobile/API, API key cho integration.
3. **Permissions** — một helper `Can(actor, action, resource)` (Policy/Gate), RBAC role.
4. **Billing** — Cashier (Checkout + Portal), metered price cho usage.
5. **Event bus** — Events/Listeners → outbox (afterCommit) → worker.
6. **API shape** — REST + JSON, cursor pagination, một error envelope, idempotency key.
7. **Frontend state** — TanStack Query cho server state, Zustand cho UI, không trộn.

### Config defaults

| Setting | Default |
|---|---|
| Session TTL (cookie) | 14 ngày, sliding |
| Access token TTL | 15 phút |
| Refresh token TTL | 30 ngày |
| API rate limit | 100 req/phút/IP, 1000 req/phút/workspace |
| File upload max | 100 MB |
| Idempotency cache TTL | 24h |
| Trial length | 14 ngày |
| Soft-delete grace | 30 ngày |
| Audit log retention | 7 năm |
| Activity feed retention | 6 tháng |
| Workspace slug regex | `[a-z0-9-]{3,40}` |
| Password min | 12 ký tự (hoặc zxcvbn score ≥ 3) |

### Hard rules (không thương lượng)

- Mọi query tenant-scoped lọc `workspace_id`.
- Mọi action đặc quyền ghi `audit_logs`.
- Mọi email tuân preference notification của user.
- Mọi webhook handler idempotent.
- Mọi input validate phía server (FormRequest).
- Mọi secret trong secrets manager ở prod, không trong env.
- Mọi public endpoint có rate limit.
- Mọi side-effect thanh toán qua webhook Stripe, không phải request path.
- Mọi task dài trong queue.
- WS event invalidate Query cache; không bao giờ ghi thẳng store.
- Migration append-only.
- Admin action audit-log với staff là actor.
- Feature flag bọc mọi behavior rủi ro mới.
- Upload file bypass API server (signed S3 URL).
- Không `WHERE` SQL nào build bằng nối chuỗi.
- Bảng mới theo convention: `id`, `workspace_id`, `created_at`, `updated_at`.

### Resource shape chuẩn (REST)

```json
{
  "id": "proj_01HMZQ...",
  "workspace_id": "01HMW1...",
  "name": "Project Alpha",
  "status": "active",
  "created_at": "2026-04-30T10:00:00Z",
  "updated_at": "2026-04-30T10:00:00Z",
  "created_by": { "type": "user", "id": "01HM..." }
}
```

### Polymorphic-actor pattern

```sql
created_by_type TEXT CHECK (created_by_type IN ('user','api_key','system')),
created_by_id   UUID
```

Dùng trên mọi field "actor" — xử lý agent, integration, người một cách đồng nhất, không cần schema song song. (Laravel: `morphTo()`.)

### Env baseline

```properties
APP_ENV=production
APP_URL=https://app.yourtool.com

DB_CONNECTION=pgsql
DB_DATABASE=...
REDIS_URL=...

# Auth
SESSION_DRIVER=cookie
SANCTUM_STATEFUL_DOMAINS=app.yourtool.com
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...

# Billing
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Object storage (S3 / R2)
AWS_BUCKET=...
AWS_ENDPOINT=...        # set cho R2/MinIO
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...

# Real-time
REVERB_APP_KEY=...
REVERB_APP_SECRET=...

# Email
RESEND_API_KEY=...
MAIL_FROM_ADDRESS="hi@yourtool.com"

# Observability
SENTRY_LARAVEL_DSN=...
POSTHOG_KEY=...

# Optional AI
OPENAI_API_KEY=...
```

### KPI track từ ngày đầu

Sign-ups/tuần · Activation rate · Free→paid conversion · MRR/ARR · NRR · Logo churn · DAU/WAU/MAU · p95 latency · error rate · NPS.

---

## Đúc kết

Một template SaaS tốt là **cứng nhắc (opinionated) về mọi thứ không quan trọng với khách, và linh hoạt về mọi thứ quan trọng**:

- Auth, billing, tenancy, observability, admin → **opinionated**, bake sẵn.
- Domain model, UI flow, branding, pricing → **flexible**, config được.

Kỷ luật: mỗi lần bạn giải *cùng một* bài hạ tầng trong *một sản phẩm mới*, lời giải đó thuộc về template. Mỗi lần bạn giải *một* bài domain *khác*, việc đó thuộc về `Product/`.

Nội hóa **§5 (Multi-tenancy)**, **§9 (Billing)**, **§19 (Security)** và **§32 (kernel/product)** thì phần còn lại của playbook trở thành một checklist chi tiết bạn thực thi trong 6–8 tuần.
