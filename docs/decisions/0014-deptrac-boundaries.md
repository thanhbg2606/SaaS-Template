> [Decisions](./README.md) › ADR-0014
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0014 — Ép ranh giới module bằng deptrac ở CI

**Status:** Accepted

**Context:** Modular Monolith chỉ giữ giá trị nếu ranh giới Kernel/Product và layering không vỡ. Dựa vào kỷ luật con người là không đủ.

**Decision:** Dùng **deptrac** định nghĩa layer (Kernel, Product, Controller, Service, Repository) và ruleset cấm: Kernel→Product, Controller→Repository (nhảy lớp), Product module này → Product module khác (trừ qua interface). Chạy trong CI như một gate bắt buộc.

**Consequences:**
- (+) Vi phạm bị chặn tự động ngay khi mở PR.
- (−) Cần bảo trì `deptrac.yaml` khi cấu trúc đổi; đôi khi cần exception có chủ đích (ghi rõ lý do).
