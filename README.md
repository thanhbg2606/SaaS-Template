# SaaS Template (Laravel)

> Bộ khung SaaS tái sử dụng được, xây trên Laravel theo kiến trúc **Modular Monolith** với tách **Kernel / Product**.
> Fork → đổi brand → thêm domain → ship. Phần hạ tầng "nhàm chán" (auth, multi-tenancy, billing, jobs, notifications, audit…) đã có sẵn; bạn chỉ xây 20% logic nghiệp vụ.

Tài liệu kiến trúc đầy đủ: [`docs/`](./docs/README.md) (mục lục + lối đọc theo vai trò).

---

## Có sẵn những gì (12 trụ cột)

Identity (Fortify + Sanctum + Socialite) · Multi-tenancy (Pool + RLS) · Billing (Cashier/Stripe) · Lifecycle (onboarding, invite, GDPR delete) · Eventing (Events → outbox) · Observability (Sentry + Pulse + OTel) · Audit log (append-only) · Notifications (mail + inbox + broadcast) · Files (signed S3 URL) · Admin (Filament) · Feature flags (Pennant) · DX (Sail, một lệnh để dev).

## Tech stack

| Lớp | Công nghệ |
|---|---|
| Runtime | PHP 8.3+, Laravel 11+ (PHP-FPM hoặc Octane) |
| Database | PostgreSQL 16+ (`pgvector`, `pg_trgm`) |
| Cache / Queue / WS scaling | Redis 7 |
| Auth | Fortify + Sanctum (+ Socialite, Passport tùy chọn) |
| Permissions | `spatie/laravel-permission` |
| Billing | Laravel Cashier (Stripe) |
| Background jobs | Queue (Redis) + Horizon |
| Real-time | Laravel Reverb + Echo |
| Search | Laravel Scout (database → Meilisearch) + pgvector |
| Feature flags | Laravel Pennant |
| Audit log | `spatie/laravel-activitylog` |
| Admin panel | Filament |
| Storage | S3 / Cloudflare R2 |
| Email | Resend / Postmark |
| Observability | Sentry + Pulse + OpenTelemetry |
| Test / Lint / Boundary | Pest · Pint · Larastan · **deptrac** |
| Local dev | Laravel Sail (Docker) |

## Kiến trúc tóm tắt

**Modular Monolith**, một deployable, một Postgres. Bên trong tách hai trục:

- **Module:** `app/Kernel/*` (mọi SaaS đều có) + `app/Product/*` (domain của bạn). **Kernel không bao giờ import Product.**
- **Layer:** Controller → Service → Repository.

Module giao tiếp qua **interface + domain event**, không với tay vào model của module khác. Ranh giới ép bằng `deptrac` ở CI. Chi tiết: [§4.3 phân lớp & §4.5 seam](./docs/architecture/overview.md) và [§32 tách Kernel/Product](./docs/architecture/kernel-product.md).

---

## Yêu cầu

- Docker + Docker Compose (cho Sail), hoặc PHP 8.3+ / Composer / Node 20+ / Postgres / Redis cài sẵn.
- Tài khoản Stripe (test mode) để chạy billing.

## Khởi động nhanh (≤ 10 phút)

```bash
# 1. Clone
git clone <repo-url> my-saas && cd my-saas

# 2. Cấu hình
cp .env.example .env
composer install
php artisan key:generate

# 3. Boot hạ tầng (Postgres + Redis + Mailpit + pgweb)
./vendor/bin/sail up -d

# 4. Migrate + seed dữ liệu demo
./vendor/bin/sail artisan migrate --seed

# 5. Chạy app + queue worker + WS + Vite (hot reload)
./vendor/bin/sail composer dev
```

Sau khi chạy:

| Dịch vụ | URL |
|---|---|
| App | http://localhost |
| Admin (Filament) | http://localhost/admin |
| Mailpit (đọc email dev) | http://localhost:8025 |
| pgweb (xem DB) | http://localhost:8081 |
| Horizon (queue) | http://localhost/horizon |
| Pulse (perf) | http://localhost/pulse |
| Telescope (debug) | http://localhost/telescope |

> Nếu engineer mới không thể `git clone && sail up` và chạm app trong 10 phút → đó là bug, hãy sửa.

### Tài khoản seed

| Email | Mật khẩu | Vai trò |
|---|---|---|
| owner@demo.test | password | owner (workspace demo) |
| admin@demo.test | password | admin |
| member@demo.test | password | member |
| viewer@demo.test | password | viewer |

---

## Cấu trúc thư mục

```
app/
  Kernel/                 ← mọi SaaS đều có (KHÔNG import Product)
    Auth/  Tenancy/  Billing/  Notifications/
    Audit/  Files/  Search/  Flags/  Admin/  Support/
    Contracts/            ← interface (port): Mailer, Storage, Bus, Meter, Flags, Cache
  Product/                ← domain của bạn
    Projects/  Issues/  ...
      Models/  Services/  Http/  Jobs/  Events/  Policies/
config/
  product.php             ← brand, features, plans (config-over-code)
database/
  migrations/  seeders/  factories/
docs/
  decisions/              ← ADR (xem ADR-log.md)
  runbooks/
routes/
tests/                    ← Pest
deptrac.yaml              ← ranh giới module
```

## Lệnh thường dùng

```bash
# Dev
sail composer dev              # app + worker + reverb + vite
sail artisan migrate           # chạy migration
sail artisan migrate:fresh --seed
sail artisan db:seed

# Chất lượng (chạy trước khi push)
sail composer test             # Pest
sail composer lint             # Pint (PSR-12)
sail composer stan             # Larastan
sail composer deptrac          # kiểm tra ranh giới module
sail composer check            # chạy tất cả gate trên

# Tạo module Product mới (xem module-extension-guide.md)
```

## Môi trường

```
dev (laptop) → preview (per-PR) → staging → production
```

Production deploy qua **Forge** (VPS) hoặc **Vapor** (serverless). Marketing + docs deploy **tách riêng** trên CDN. Chi tiết §26.

---

## Tài liệu liên quan

| File | Nội dung |
|---|---|
| [`docs/`](./docs/README.md) | **Mục lục tài liệu** + lối đọc theo vai trò (cổng vào nội dung sâu) |
| [`docs/architecture/`](./docs/README.md) | Kiến trúc: [tổng quan §1–4](./docs/architecture/overview.md), [§5 multi‑tenancy](./docs/architecture/multi-tenancy.md), [§32 kernel/product](./docs/architecture/kernel-product.md), [data‑model](./docs/architecture/data-model.md), [trụ cột §6–28](./docs/architecture/pillars/) |
| [`docs/decisions/`](./docs/decisions/README.md) | 14 ADR (mỗi quyết định 1 file) + template |
| [`docs/guides/`](./docs/guides/module-extension.md) | [Fork & reskin](./docs/guides/reskin-fork.md) · [Thêm Product module](./docs/guides/module-extension.md) |
| [`docs/runbooks/`](./docs/runbooks/README.md) | Vận hành: deploy · rollback · incident/on‑call |
| [`docs/strategy/`](./docs/strategy/pricing-packaging.md) | §29–§35: pricing, analytics, support, lộ trình 14 phase |
| [`CONTRIBUTING.md`](./CONTRIBUTING.md) | Quy trình đóng góp, branch, commit, coding standard |

## License

MIT — xem `LICENSE`. *(Đổi sang `proprietary` nếu fork thành sản phẩm đóng; cập nhật cả `composer.json`.)*
