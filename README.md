# SaaS Template (Laravel)

> Bộ khung SaaS tái sử dụng được, xây trên Laravel theo kiến trúc **Modular Monolith** với tách **Kernel / Product**.
> Fork → đổi brand → thêm domain → ship. Phần hạ tầng "nhàm chán" (auth, multi-tenancy, billing, jobs, notifications, audit…) đã có sẵn; bạn chỉ xây 20% logic nghiệp vụ.

Tài liệu kiến trúc đầy đủ: [`docs/`](./docs/README.md) (mục lục + lối đọc theo vai trò).

---

## Có sẵn hiện tại

- Laravel starter app chính thức với React + Inertia + TypeScript
- Auth starter mặc định của Laravel/Fortify
- Docker-first local development với `compose.yaml` + Laravel Sail
- PostgreSQL, Redis, Mailpit cho môi trường dev
- Boundary skeleton ban đầu: `app/Kernel`, `app/Product`, `config/product.php`
- Demo data với 4 tài khoản `*@demo.test`
- Smoke test cho route `/`
- Luật ranh giới ban đầu bằng `deptrac`

## Tech stack

| Lớp | Công nghệ |
|---|---|
| Runtime | PHP 8.4.1+ (Sail runtime mặc định là 8.5), Laravel 13 |
| Frontend | React 19, Inertia, TypeScript, Tailwind CSS 4, Vite |
| Database | PostgreSQL 18 (Sail) |
| Cache / Queue backing | Redis |
| Email dev | Mailpit |
| Auth | Laravel Fortify + starter auth pages |
| Test / Lint / Boundary | PHPUnit, Pint, Larastan, Deptrac |
| Local dev | Laravel Sail (Docker) |

## Kiến trúc tóm tắt

Repository hiện ở giai đoạn đầu của một **Modular Monolith**: một ứng dụng Laravel chạy được, có sẵn seam để mở rộng tiếp thành template SaaS đầy đủ.

- `app/Kernel/*` dành cho capability dùng chung
- `app/Product/*` dành cho domain nghiệp vụ của sản phẩm
- `config/product.php` là ranh giới cấu hình cho brand, feature flags, plan, onboarding
- `deptrac.yaml` đang ép bộ quy tắc đầu tiên cho các lớp `Kernel`, `Product`, `Controller`, `Service`, `Repository`

Chi tiết định hướng kiến trúc dài hạn vẫn nằm trong [`docs/`](./docs/README.md), nhưng code hiện tại mới chỉ implement phần scaffold chạy được + boundary entry points ban đầu.

---

## Yêu cầu

- Docker + Docker Compose
- Không yêu cầu host cài sẵn PHP, Composer, Node, hay npm cho các lệnh của dự án
- Mọi lệnh của dự án MUST chạy trong môi trường Docker; trước khi có `vendor/bin/sail`,
  bootstrap bằng container Composer

## Khởi động nhanh (≤ 10 phút)

```bash
# 1. Clone
git clone <repo-url> my-saas && cd my-saas

# 2. Cấu hình env
cp .env.example .env

# 3. Cài PHP dependencies
docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app composer:2 composer install

# 4. Boot Sail stack
./vendor/bin/sail up -d

# 5. Tạo app key và cài frontend dependencies
./vendor/bin/sail artisan key:generate
./vendor/bin/sail npm install

# 6. Migrate + seed dữ liệu demo
./vendor/bin/sail artisan migrate --seed

# 7. Kiểm tra baseline hoạt động
./vendor/bin/sail artisan test tests/Feature/TemplateSmokeTest.php

# 8. Chạy quy trình dev mặc định của starter
./vendor/bin/sail composer dev
```

Sau khi chạy:

| Dịch vụ | URL |
|---|---|
| App | http://localhost:8000 |
| Vite dev server | http://localhost:5173 |
| Mailpit | http://localhost:8025 |
| PostgreSQL | localhost:5432 |
| Redis | localhost:6379 |

### Tài khoản seed

| Email | Mật khẩu | Vai trò |
|---|---|---|
| owner@demo.test | password | owner |
| admin@demo.test | password | admin |
| member@demo.test | password | member |
| viewer@demo.test | password | viewer |

Mật khẩu mặc định hiện tại là `password`, đi ra từ `UserFactory`. Nếu bạn thay factory/seeder sau này, dùng flow reset password cục bộ để tạo lại credential phù hợp.

---

## Cấu trúc thư mục

```
app/
  Kernel/
    Contracts/
    Support/
  Product/
    Projects/
config/
  product.php
database/
  migrations/  seeders/  factories/
docs/
  decisions/
  runbooks/
routes/
tests/                    ← PHPUnit feature tests
deptrac.yaml              ← luật ranh giới ban đầu
```

## Lệnh thường dùng

```bash
# Bootstrap trước khi có Sail
docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app composer:2 composer install

# Dev
./vendor/bin/sail up -d
./vendor/bin/sail composer dev
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail artisan db:seed
./vendor/bin/sail npm install

# Chất lượng (chạy trước khi push)
./vendor/bin/sail composer test
./vendor/bin/sail composer lint
./vendor/bin/sail composer types:check
./vendor/bin/sail composer deptrac
./vendor/bin/sail composer check
```

## Môi trường

```
dev (laptop) → preview (per-PR) → staging → production
```

Chiến lược triển khai dài hạn vẫn nằm trong tài liệu kiến trúc, nhưng working tree hiện tại mới verify ở mức local `Dev ready`.

---

## Tài liệu liên quan

| File | Nội dung |
|---|---|
| [`docs/`](./docs/README.md) | **Mục lục tài liệu** + lối đọc theo vai trò (cổng vào nội dung sâu) |
| [`docs/architecture/`](./docs/README.md) | Kiến trúc: [tổng quan §1–4](./docs/architecture/overview.md), [§5 multi‑tenancy](./docs/architecture/multi-tenancy.md), [§32 kernel/product](./docs/architecture/kernel-product.md), [data‑model](./docs/architecture/data-model.md), [trụ cột §6–28](./docs/architecture/pillars/) |
| [`docs/decisions/`](./docs/decisions/README.md) | 14 ADR (mỗi quyết định 1 file) + template |
| [`docs/guides/`](./docs/guides/fork-from-template.md) | [Fork từ template](./docs/guides/fork-from-template.md) · [Fork & reskin](./docs/guides/reskin-fork.md) · [Thêm Product module](./docs/guides/module-extension.md) |
| [`docs/runbooks/`](./docs/runbooks/README.md) | Vận hành: deploy · rollback · incident/on‑call |
| [`docs/strategy/`](./docs/strategy/pricing-packaging.md) | §29–§35: pricing, analytics, support, lộ trình 14 phase |
| [`CONTRIBUTING.md`](./CONTRIBUTING.md) | Quy trình đóng góp, branch, commit, coding standard |

## License

MIT — xem `LICENSE`. *(Đổi sang `proprietary` nếu fork thành sản phẩm đóng; cập nhật cả `composer.json`.)*
