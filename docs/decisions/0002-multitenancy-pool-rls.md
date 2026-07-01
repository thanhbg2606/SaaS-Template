> [Decisions](./README.md) › ADR-0002
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0002 — Multi-tenancy: mô hình Pool + RLS

**Status:** Accepted

**Context:** Đây là quyết định nền móng, chi phối mọi query. Ba lựa chọn: Pool (một DB, mọi dòng gắn `workspace_id`), Bridge (schema/tenant), Silo (DB/tenant). Đa số khách B2B không cần isolation vật lý ngay.

**Decision:** Mặc định **Pool**. Mọi bảng tenant-scoped có `workspace_id NOT NULL`. Hai lớp phòng thủ: (1) trait `BelongsToTenant` thêm global scope tự động; (2) **Postgres RLS** chặn rò rỉ kể cả khi dev quên filter. Tenant resolve một lần/request từ auth, lưu trong context, set `SET app.workspace_id`. **Silo** để dành làm tier enterprise sau (ADR mới khi cần).

**Consequences:**
- (+) Ops/chi phí tốt nhất, một DB để vận hành.
- (+) RLS là lưới an toàn cho lỗi của con người.
- (−) Mọi query phải có ngữ cảnh tenant; query cross-tenant (admin/analytics) phải đi code path riêng có audit.
- (−) "Noisy neighbor": một tenant lớn ảnh hưởng hiệu năng chung → giải bằng index + partition, hoặc nâng lên Silo cho khách VIP.
