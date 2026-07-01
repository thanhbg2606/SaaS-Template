> [Decisions](./README.md) › ADR-0003
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0003 — Kiến trúc ứng dụng: Modular Monolith (Kernel/Product)

**Status:** Accepted

**Context:** Mục tiêu là một **template** dễ fork + reskin + một team nhỏ vận hành. Microservices gây phức tạp phân tán, khó deploy, và fork nhiều service rất khổ.

**Decision:** **Modular Monolith** — một deployable, một Postgres. Tách hai trục: module (`app/Kernel/*` không import `app/Product/*`) và layer (Controller→Service→Repository). Module giao tiếp qua interface + domain event. Hexagonal "lite" chỉ ở biên hạ tầng (Contracts), không nhồi DDD đầy đủ.

**Consequences:**
- (+) Tốc độ và đơn giản của monolith; dễ fork/reskin.
- (+) Ranh giới sạch → có thể tách service sau mà không viết lại.
- (−) Cần kỷ luật để ranh giới không vỡ → ép bằng deptrac (ADR-0014).
- (−) Scale theo process, không theo service — đủ tới hàng nghìn khách trên một box trước khi cần tách.
