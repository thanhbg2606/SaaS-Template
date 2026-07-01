> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Billing, Subscriptions & Metering
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 9. Billing, Subscriptions & Metering

### 9.1 Dùng Stripe (hoặc Paddle/LemonSqueezy nếu muốn họ lo thuế toàn cầu)

**Đừng tự xây billing.** Stripe đã giải mọi edge case bạn gặp ở năm thứ ba.

```bash
composer require laravel/cashier        # Stripe
# hoặc
composer require laravel/cashier-paddle # Paddle (Merchant-of-Record)
```

**Cashier xử lý sẵn:** Checkout, Customer Portal, trial, swap plan, hóa đơn, **và webhook idempotent**. Bạn chỉ cần subscribe các sự kiện và phản ứng.

### 9.2 Ledger subscription nằm trong DB của bạn

Ngay cả khi dùng Cashier, giữ một bảng `subscriptions` provider-agnostic để dễ thêm PayPal/Paddle song song sau này:

```sql
subscriptions (
  id UUID PK, workspace_id UUID, plan_id UUID,
  status TEXT,                   -- trialing | active | past_due | canceled
  payment_provider TEXT,         -- 'stripe' | 'paypal' | 'manual'
  provider_subscription_id TEXT, -- sub_… / I-…
  provider_customer_id TEXT,
  current_period_end TIMESTAMPTZ, cancel_at TIMESTAMPTZ NULL
)
```

### 9.3 Webhook contract (subscribe tối thiểu)

`customer.subscription.{created,updated,deleted}` · `invoice.paid` · `invoice.payment_failed` · `checkout.session.completed`.

**Quy tắc idempotency:** mọi handler webhook phải idempotent (Stripe sẽ retry). Cashier cập nhật state subscription theo kiểu *converge state* (áp lại cùng event ⇒ cùng kết quả) — **nhưng KHÔNG tự dedup theo `event.id`**. Handler tùy biến có side-effect (gửi mail, `INCR`, gọi API ngoài) phải tự lưu `event.id` đã xử lý (DB hoặc Redis TTL 7 ngày) rồi bỏ qua nếu trùng.

### 9.4 Plan model — `features` & `limits` để JSON (thêm gate không cần migrate)

```json
{
  "features": { "sso": false, "audit_log_export": false },
  "limits":   { "members": 10, "projects": 5, "ai_credits_per_month": 1000 }
}
```

### 9.5 Feature gating — mọi paywall là một `can()` + UI prompt

```php
if (! $workspace->can('feature.sso')) {
    return $this->upgradePrompt('SSO có ở gói Team trở lên');
}
```

**Không bao giờ im lặng 403.** Luôn hiện prompt nâng cấp.

### 9.6 Metering — không bao giờ gọi Stripe đồng bộ trong request path

```php
// Nhanh, non-blocking trong request:
app(Meter::class)->increment($workspaceId, 'ai.tokens', $n);
// → ghi Redis (INCR), buffer ghi Postgres/Stripe trong worker
```

### 9.7 Dunning (thanh toán thất bại)

Lần 1: email "Không charge được thẻ" → lần 3 (~7 ngày): hạ về free + email → 30 ngày chưa trả: suspend (read-only) → 60 ngày: hard-delete hoặc chuyển collection. Stripe lo lịch retry (Smart Retries); bạn lo thông điệp trong app.

### 9.8 Trial

14 ngày là chuẩn văn hóa. **Mặc định no-card** cho B2B + banner đếm ngược. Hết trial → read-only + banner nâng cấp, **đừng xóa data**.
