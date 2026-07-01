> [Decisions](./README.md) › ADR-0006
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0006 — Billing: Stripe qua Cashier, bảng subscription provider-agnostic

**Status:** Accepted

**Context:** Billing có vô số edge case (proration, dunning, thuế, trial). Tự xây là sai lầm. Nhưng cũng cần đường thêm provider khác (PayPal/Paddle) cho thị trường non-card.

**Decision:** **Laravel Cashier (Stripe)** cho Checkout + Customer Portal + webhook idempotent. Nhưng **ledger subscription nằm trong DB của ta**, bảng `subscriptions` có cột `payment_provider` (`stripe`/`paypal`/`manual`) để thêm provider song song sau. Mỗi provider một webhook handler ghi vào cùng state machine. Metering không bao giờ gọi Stripe đồng bộ trong request path.

**Consequences:**
- (+) Tránh tự xây billing; Cashier lo retry/idempotency.
- (+) Thêm PayPal/Paddle sau = swap webhook handler, không viết lại.
- (−) State subscription tồn tại hai nơi (Stripe + DB) → phải đồng bộ qua webhook cẩn thận.
