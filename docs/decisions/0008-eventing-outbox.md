> [Decisions](./README.md) › ADR-0008
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0008 — Eventing: in-process Events + outbox; hoãn NATS

**Status:** Accepted

**Context:** Cần "DB write + phát event" nhất quán trong transaction, và fanout tới worker/WS. Kafka/NATS là phức tạp chưa cần thiết ở monolith.

**Decision:** Dùng **Laravel Events/Listeners** làm in-process bus. Event/job phát trong transaction dùng `ShouldDispatchAfterCommit` / `$afterCommit = true` (giải pattern outbox). Khi cần đảm bảo mạnh hơn (cross-service, replay) thì dựng bảng `outbox` + worker poll. **NATS JetStream để dành** khi đã tách nhiều service (ADR mới khi đó).

**Consequences:**
- (+) Không thêm hạ tầng; đủ cho monolith.
- (+) Bus là interface → đổi sang NATS sau là swap adapter.
- (−) "At-least-once" cross-service cần outbox table thật khi vượt monolith.
