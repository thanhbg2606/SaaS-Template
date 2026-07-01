> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › API Design
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 11. API Design

### 11.1 REST là mặc định; GraphQL là ngoại lệ

REST + JSON cho 90% endpoint (dễ đoán, cache được, debug được). GraphQL chỉ khi data graph lồng sâu + nhiều client. gRPC cho service-to-service nội bộ.

### 11.2 Resource conventions

```
GET    /api/v1/projects           list
POST   /api/v1/projects           create
GET    /api/v1/projects/{id}      read
PATCH  /api/v1/projects/{id}      partial update (ưu tiên hơn PUT)
DELETE /api/v1/projects/{id}      delete
GET    /api/v1/projects/{id}/issues   sub-collection
```

Dùng **API Resources** của Laravel để serialize (thêm prefix display ID ở đây). Versioning bằng route group prefix `v1`.

### 11.3 Pagination — cursor, không offset

Dùng `cursorPaginate()` (offset vỡ khi có insert đồng thời). Trả `{ items, next_cursor, has_more }`. Cap `limit` ở 100.

### 11.4 Error envelope (một dạng, mọi nơi)

```json
{
  "error": {
    "code": "validation_error",
    "message": "Title is required",
    "fields": { "title": "must not be empty" },
    "request_id": "req_01HMZ..."
  }
}
```

Tùy biến Laravel exception handler để trả đúng dạng này. Luôn kèm `request_id` (header + body).

### 11.5 Idempotency

Cho POST tạo resource/side-effect: nhận header `Idempotency-Key`. Cache `(workspace_id, key) → response` trong Redis 24h. Trả response cache khi retry. Viết một middleware riêng.

### 11.6 Rate limiting

Per API key + per IP + per workspace. Token bucket trong Redis. Trả `429` + `Retry-After`. Laravel có sẵn `RateLimiter` + `throttle` middleware:

```php
RateLimiter::for('api', fn ($request) =>
    Limit::perMinute(100)->by($request->ip())
);
```

Surface header `X-RateLimit-*`.

### 11.7 Webhooks (đi ra)

Endpoint per-workspace đăng ký trong settings. Ký mọi payload: `X-Signature: sha256=<hmac>`. Kèm `X-Event-Id` (idempotency) + `X-Timestamp` (chống replay). Retry exponential backoff (1m, 5m, 30m, 2h, 12h) — đẩy qua queue.
