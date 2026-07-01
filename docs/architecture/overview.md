> **SaaS Template Playbook** › [Mục lục](../README.md) › Kiến trúc › Tổng quan
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../section-map.md).

# Tổng quan kiến trúc (§1–§4)

## 1. SaaS Template thực chất là gì

Template SaaS là **80% phần "nhàm chán" mà mọi sản phẩm đều phải xây lại**:

- Đăng ký, đăng nhập, reset mật khẩu, SSO, MFA
- Organization / workspace / team / lời mời
- Roles + permissions
- Billing, subscription, plan, đo lường usage, hóa đơn
- Email + notification + inbox trong app
- Audit log + activity feed
- Admin panel
- Feature flags
- Background jobs, scheduled jobs, webhooks
- Upload file + CDN
- API key + rate limiting
- Observability + error tracking
- CI/CD + deploy nhiều môi trường
- Landing page marketing + trang docs

**Nó KHÔNG phải:**
- Logic nghiệp vụ của sản phẩm — đó là 20% riêng bạn xây bên trên.
- Một nền tảng no-code — nó là code starter.
- Một "SaaS đóng hộp" thần kỳ — bạn vẫn cần phán đoán sản phẩm.

Mental model đúng: **hạ tầng cho phần mà mọi SaaS đều có, với các "seam" sạch để domain của bạn cắm vào.**

## 2. Mental model 30 giây

Ba bề mặt deploy, một nguồn sự thật duy nhất:

| Bề mặt | Xây từ | Chạy ở đâu |
|---|---|---|
| Marketing + docs | Next.js static / Astro | CDN (Vercel / Cloudflare Pages) |
| Web app | React SPA hoặc Inertia/Livewire | CDN + edge |
| API + workers | **Laravel** (PHP-FPM / Octane) | Forge/Vapor/Ploi, hoặc Docker trên VM |

Postgres là nguồn sự thật · Redis cho cache + queue · S3 cho file · queue worker cho việc nặng.

## 3. 12 trụ cột của một SaaS production

Thiếu một trụ là sau này "ăn" scope creep. Cột "done" = định nghĩa hoàn thành.

| # | Trụ cột | "Done" trông như thế nào |
|---|---|---|
| 1 | **Identity** | Email/password, OAuth (Google/GitHub), magic link, MFA, SSO (SAML/OIDC), mô hình session + token |
| 2 | **Tenancy** | Ranh giới org/workspace, mọi query lọc `workspace_id`, RBAC + (tùy chọn) ABAC |
| 3 | **Billing** | Stripe đã nối, plan cấu hình được, trial, dunning, đo usage, cổng hóa đơn |
| 4 | **Lifecycle** | Onboarding, xác thực email, mời thành viên, offboarding, xóa tài khoản (sạch GDPR) |
| 5 | **Eventing** | In-process bus → outbox → worker → webhook. Idempotent |
| 6 | **Observability** | Log có cấu trúc + trace + metric + error tracker, tất cả tương quan qua `request_id` + `tenant_id` |
| 7 | **Audit** | Audit log append-only cho mọi action đặc quyền, query được theo tenant |
| 8 | **Notifications** | Email giao dịch + inbox trong app + (tùy) SMS/push, có preference từng user |
| 9 | **Files** | Upload trực tiếp lên S3 qua signed URL; không bao giờ cho byte đi qua API |
| 10 | **Admin** | Dashboard nội bộ cho support: impersonate, refund, suspend, soi tenant |
| 11 | **Flags** | Feature flag theo môi trường + theo tenant + theo user. Văn hóa kill-switch |
| 12 | **DX** | Một lệnh để dev, seed data, test nhanh, docs không nói dối |

## 4. Kiến trúc tham chiếu (bản Laravel)

### 4.1 Cái gì nằm ở đâu

| Mối quan tâm | Công nghệ |
|---|---|
| Nguồn sự thật | Postgres (khuyến nghị, có `pgvector`, `pg_trgm`) |
| Hot reads, session, idempotency key, rate-limit counter | Redis |
| Việc nặng/chậm, retry, scheduled | Queue worker (Horizon) |
| Real-time fanout tới client | Reverb + Redis (multi-node) |
| Analytics khối lượng lớn | ClickHouse / BigQuery (mirror từ Postgres) |
| UI tĩnh | CDN |
| File user upload | S3 + CDN với signed URL |
| Secrets | `.env` (dev) / Vault / Doppler / Forge env (prod) |

### 4.2 Chuỗi middleware (thứ tự quan trọng — wire một lần, đừng đảo)

```
Request
  → [1] Recovery / Exception handler  (Laravel xử lý sẵn, nối Sentry)
  → [2] RequestID         (gán/nhận X-Request-ID, đưa vào context log)
  → [3] Logger            (bind request_id vào logger)
  → [4] Tracing           (OTel span)
  → [5] CORS              (config/cors.php, allowlist origin)
  → [6] RateLimit         (throttle middleware, Redis)
  → [7] Auth              (Sanctum: verify session/token → set Actor)
  → [8] Tenant            (resolve workspace_id → set context + SET app.workspace_id)
  → [9] CSRF              (VerifyCsrfToken, chỉ endpoint cookie)
  → [10] Idempotency      (POST có Idempotency-Key)
  → Controller → Service → Repository
```

**Quy tắc:** Auth **trước** Tenant (cần biết actor mới resolve được workspace). RateLimit **trước** Auth (chặn abuse chưa đăng nhập sớm). Recovery ngoài cùng để panic ở đâu cũng trả 500 sạch.

### 4.3 Kiến trúc phân lớp (Controller → Service → Repository)

| Lớp | Biết về | Cấm |
|---|---|---|
| **Controller** | HTTP, Service interface, Request/Resource | DB, SQL, SDK bên thứ ba |
| **Service** | Logic domain, Service khác, Repository interface, Event bus | Kiểu HTTP (`Request`, `Response`) |
| **Repository** | Eloquent/Query Builder, model | HTTP, business rule, repo khác |

Controller **không bao giờ** chạm DB. Repo **không bao giờ** quyết định một action có được phép hay không. Đây là thứ làm Service test được mà không cần server, và Repo thay được.

### 4.4 Form chuẩn của một Controller

Mọi controller trông giống nhau. Lệch khỏi mẫu = reviewer flag.

```php
public function store(CreateProjectRequest $request): JsonResponse
{
    $actor       = $request->actor();                 // set bởi Auth middleware
    $workspaceId = tenant()->id;                      // set bởi Tenant middleware

    $project = $this->projects->create(
        $actor, $workspaceId, $request->validated()
    );

    return ProjectResource::make($project)
        ->response()->setStatusCode(201);
}
```

Validate ở `FormRequest`, logic ở Service. Nếu controller vượt 20 dòng → đẩy logic xuống lớp dưới.

### 4.5 Các "seam" (interface) giữ template tái dùng được

Mỗi năng lực cross-cutting là một **interface** trong `app/Kernel/Contracts/`. Product import interface; binding chọn implementation lúc khởi động (trong Service Provider). Đổi provider = đổi một dòng `bind()`.

```php
interface Mailer    { public function send(Message $msg): void; }
interface Storage   { public function presignPut(string $key, PutOptions $o): string; }
interface Bus       { public function publish(string $subject, array $payload): void; }
interface Meter     { public function increment(string $wsId, string $metric, int $n): void; }
interface Flags     { public function enabled(string $key, FlagScope $scope): bool; }
interface Cache     { public function remember(string $key, int $ttl, Closure $cb): mixed; }   // tag-invalidation
```

> 6 contract chuẩn: **Mailer · Storage · Bus · Meter · Flags · Cache** — đồng bộ với [README](../../README.md) (cấu trúc thư mục) và [guides/module-extension](../guides/module-extension.md) (bảng Kernel Contracts).

### 4.6 ID conventions

- **UUID v7** cho mọi khóa chính — sắp xếp được theo thời gian (PK + thứ tự thời gian trong một cột). Laravel 11+ dùng trait `HasUuids` — từ Laravel 11 nó sinh **UUID v7** mặc định (`Str::uuid7()`). *(Lưu ý: không có trait tên `HasVersion7Uuids`; framework chỉ có `HasUuids` và `HasVersion4Uuids`.)*
- **Prefixed display ID** trong API: `proj_01HMZ…`, `inv_01HMZ…`. DB lưu UUID thô; serializer (API Resource) thêm prefix. Tiết kiệm thời gian debug khi khách paste ID vào ticket.
