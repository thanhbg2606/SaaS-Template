> **SaaS Template Playbook** › [Mục lục](../README.md) › Hướng dẫn › Fork & Reskin

# Reskin / Fork Guide

Quy trình biến template này thành một sản phẩm SaaS có brand riêng — mục tiêu **≤ 30 phút** ra một skeleton chạy được, mang tên và màu của bạn. Đây là tài liệu định nghĩa giá trị cốt lõi của một *template* (so với một *app*).

Nguyên tắc xuyên suốt: **chỉ chạm `app/Product/*` và `config/product.php`; không sửa `app/Kernel/*`.** Sửa kernel = mất khả năng pull update từ template về sau.

---

## Trước khi bắt đầu

- Đã đọc [README](../../README.md) (chạy được local) và nắm §32 trong [kernel-product](../architecture/kernel-product.md).
- Có sẵn: logo (SVG + PNG), bảng màu brand, tài khoản Stripe (test mode).
- Quyết định tên sản phẩm + slug (ví dụ: `AcmeApp`, domain `acme.app`).

---

## Bước 1 — Fork & giữ liên kết với template

```bash
# Clone template thành repo sản phẩm mới
git clone <template-url> acme-app && cd acme-app

# Đổi origin sang repo của bạn
git remote set-url origin <repo-acme-app>

# Giữ template làm remote để pull update kernel sau này
git remote add template-upstream <template-url>
git push -u origin main
```

> `template-upstream` cho phép kéo cải tiến kernel về sau (xem mục cuối).

## Bước 2 — Đổi tên ứng dụng

```bash
# composer.json: đổi "name"
# acme/saas-template → acme/acme-app

# .env
APP_NAME="AcmeApp"
APP_URL=https://app.acme.test
```

- Namespace gốc `App\` giữ nguyên (không cần đổi).
- Cập nhật `composer.json` description, authors nếu muốn.

## Bước 3 — Cấu hình sản phẩm (`config/product.php`)

Đây là **trung tâm tùy biến**. Hầu hết khác biệt giữa các sản phẩm nằm ở file này, không phải code:

```php
return [
    'brand' => [
        'name'          => 'AcmeApp',
        'tagline'       => 'Quản lý X cho team Y',
        'primary_color' => '#FF5C5C',
        'support_email' => 'support@acme.app',
    ],

    'features' => [
        'audit_log_export' => true,
        'custom_domains'   => false,
        'ai_assist'        => true,
    ],

    'plans' => [
        ['name' => 'free',    'price_cents' => 0,    'stripe_price_id' => null,         'limits' => ['members' => 1,  'projects' => 1]],
        ['name' => 'pro',     'price_cents' => 1900, 'stripe_price_id' => 'price_xxx',  'limits' => ['members' => 10, 'projects' => 20]],
        ['name' => 'team',    'price_cents' => 4900, 'stripe_price_id' => 'price_yyy',  'limits' => ['members' => 50, 'projects' => null]],
    ],

    'onboarding' => [
        'activation_event' => 'task.created',   // sự kiện dự báo retention
        'seed_demo_data'   => true,
    ],
];
```

## Bước 4 — Thay tài sản thương hiệu

| File | Thay bằng |
|---|---|
| `public/logo.svg`, `public/logo-dark.svg` | Logo của bạn |
| `public/favicon.ico`, `public/favicon-*.png` | Favicon |
| `public/og-image.png` | Ảnh share mạng xã hội (1200×630) |
| `resources/images/email-logo.png` | Logo trong email |

## Bước 5 — Màu & design token

```js
// tailwind.config.js
export default {
  theme: {
    extend: {
      colors: {
        brand: {
          DEFAULT: '#FF5C5C',   // = product.brand.primary_color
          50: '#...', 500: '#FF5C5C', 600: '#...', 900: '#...',
        },
      },
    },
  },
};
```

Nếu dùng file token tập trung: cập nhật `resources/js/design-tokens.ts` (màu, spacing, radii, typography scale). Filament admin: cập nhật theme trong `app/Kernel/Admin/Providers/FilamentProvider.php` (chỉ phần màu — không sửa logic kernel).

## Bước 6 — Copy marketing

Cập nhật nội dung trong `apps/marketing/content/` (hoặc repo marketing tách riêng): hero, pricing, FAQ, case study. Marketing deploy **riêng** trên CDN — không nhồi vào app Laravel.

## Bước 7 — Cấu hình Stripe

1. Tạo Products + Prices trong Stripe Dashboard (test mode) khớp với `plans` ở Bước 3.
2. Dán `price_id` vào `config/product.php`.
3. Lấy key + webhook secret vào `.env`:

```
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

4. Cấu hình webhook endpoint trong Stripe trỏ tới `https://app.acme.test/stripe/webhook` (subscribe các event ở §9.3). Test local bằng `stripe listen --forward-to localhost/stripe/webhook`.

## Bước 8 — Phần còn lại của `.env`

```
DB_CONNECTION=pgsql
DB_DATABASE=acme_app
REDIS_URL=...

# Auth OAuth
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...

# Storage (S3/R2)
AWS_BUCKET=acme-app
AWS_ENDPOINT=...          # nếu dùng R2/MinIO
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...

# Real-time
REVERB_APP_KEY=...
REVERB_APP_SECRET=...

# Email
RESEND_API_KEY=...
MAIL_FROM_ADDRESS="hi@acme.app"

# Observability
SENTRY_LARAVEL_DSN=...
POSTHOG_KEY=...
```

## Bước 9 — Thêm domain models

Đây là 20% riêng của bạn. Tạo module Product mới — **không đụng kernel**. Quy trình chi tiết: [module-extension](./module-extension.md). Tóm tắt:

```
app/Product/Tasks/
  Models/Task.php          (use BelongsToTenant, HasUuids, SoftDeletes)
  Services/TaskService.php
  Http/Controllers/TaskController.php
  Http/Requests/CreateTaskRequest.php
  Http/Resources/TaskResource.php
  Policies/TaskPolicy.php
  Events/TaskCreated.php
database/migrations/xxxx_create_tasks_table.php   (theo convention + RLS)
```

## Bước 10 — Wire routes & pages

```php
// routes/product.php (được load bởi ProductServiceProvider)
Route::middleware(['auth:sanctum', 'tenant'])->prefix('api/v1')->group(function () {
    Route::apiResource('tasks', TaskController::class);
});
```

Trang UI: thêm vào `app/Product/.../Pages` (Inertia/Livewire/SPA tùy stack frontend).

## Bước 11 — Cập nhật seeder domain

```php
// database/seeders/ProductSeeder.php
// Tạo demo data domain: 100 task mẫu, 1 workspace demo cho browse
```

Chạy lại: `sail artisan migrate:fresh --seed`.

## Bước 12 — Verify reskin

Chạy checklist khói + test:

```bash
sail composer check          # lint + stan + deptrac + test phải XANH
sail artisan migrate:fresh --seed
sail composer dev
```

Kiểm tra bằng mắt:
- [ ] Logo + màu brand hiển thị đúng ở app + email (Mailpit).
- [ ] Đăng ký → tạo workspace → activation event domain bắn.
- [ ] Trang pricing hiện đúng plan từ `config/product.php`.
- [ ] Chọn plan → Stripe Checkout (test) → subscription tạo đúng.
- [ ] Admin Filament truy cập được, action audit-log.
- [ ] `deptrac` xanh (không lỡ tay import kernel).

---

## Pull update kernel từ template về sau

Khi template cải tiến kernel (vá bảo mật, thêm tính năng hạ tầng), kéo về:

**Cách 1 — Cherry-pick (kiểm soát cao):**
```bash
git fetch template-upstream
git log template-upstream/main --oneline      # xem commit kernel mới
git cherry-pick <commit-hash>                  # chỉ lấy commit kernel cần
```

**Cách 2 — Script upgrade (copy non-product paths):**
```bash
bin/upgrade-kernel        # copy app/Kernel, config kernel, deps; bỏ qua app/Product
```

> Vì **kernel không bao giờ import product** (ADR-0003), update kernel hiếm khi đụng code domain của bạn — đó là toàn bộ lý do của việc tách Kernel/Product.

---

## Checklist reskin (gộp)

- [ ] Fork + set `template-upstream` remote
- [ ] Đổi `composer.json` name + `APP_NAME`
- [ ] Cập nhật `config/product.php` (brand, features, plans, onboarding)
- [ ] Thay logo, favicon, OG image, email logo
- [ ] Cập nhật `tailwind.config.js` + design token
- [ ] Cập nhật copy marketing
- [ ] Tạo Stripe products/prices → dán `price_id`
- [ ] Điền `.env` (auth, storage, reverb, mail, observability)
- [ ] Thêm domain models vào `app/Product`
- [ ] Wire routes + pages
- [ ] Cập nhật seeder domain
- [ ] `sail composer check` xanh + smoke test thủ công
