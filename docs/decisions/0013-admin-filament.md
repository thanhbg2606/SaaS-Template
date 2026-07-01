> [Decisions](./README.md) › ADR-0013
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0013 — Admin panel: Filament

**Status:** Accepted

**Context:** Cần admin nội bộ ngày đầu (impersonate, suspend, refund, override flag). Lựa chọn: Nova (paid), Filament (free), tự build, Retool.

**Decision:** **Filament** — free, nhanh dựng CRUD, cộng đồng lớn. Gate sau claim `is_internal_admin`, hostname riêng (`admin.`), **MFA bắt buộc**, session time-box, mọi action audit-log với staff là actor.

**Consequences:**
- (+) Miễn phí, tốc độ build cao, đủ tốt cho user nội bộ.
- (−) UI ý kiến riêng của Filament; tùy biến sâu cần học framework của nó.
