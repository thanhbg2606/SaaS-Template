> [Decisions](./README.md) › ADR-0009
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0009 — Background jobs: Redis queue + Horizon

**Status:** Accepted

**Context:** Việc nặng/chậm (email, webhook, sync Stripe, embedding) phải rời request path. Cần monitoring + retry.

**Decision:** **Queue driver Redis + Horizon** (dashboard, metric, retry). Fanout dùng `Bus::batch`; workflow nhiều bước dùng job chaining. Job idempotent (`ShouldBeUnique` hoặc dedup key). Scheduler dùng `onOneServer()` để không chạy trùng trên multi-instance. Durable execution (Temporal) **để dành** cho workflow dài/đa bước thật sự.

**Consequences:**
- (+) Trưởng thành, dễ vận hành, Horizon trực quan.
- (−) Redis là điểm phụ thuộc; cần dead-letter + alert DLQ depth.
