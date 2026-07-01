> [Decisions](./README.md) › ADR-0010
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0010 — Real-time: Laravel Reverb

**Status:** Accepted

**Context:** Cần đẩy update live (notification, presence, collab). Lựa chọn: Pusher (managed) vs tự host.

**Decision:** **Laravel Reverb** (WS server first-party, scale qua Redis) + **Echo** ở client. Channel theo tenant: `private-workspace.{id}.*`. **Quy tắc vàng:** WS event chỉ *invalidate* cache query phía client, không bao giờ ghi thẳng vào store (WS có thể lệch thứ tự/drop/replay).

**Consequences:**
- (+) First-party, tự host, không phí per-message như Pusher.
- (−) Phải tự lo scale/ops Reverb; cần Redis cho multi-node.
