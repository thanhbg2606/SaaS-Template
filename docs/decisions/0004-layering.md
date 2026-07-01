> [Decisions](./README.md) › ADR-0004
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0004 — Phân lớp Controller → Service → Repository

**Status:** Accepted

**Context:** Cần code test được, đổi được, không để logic nghiệp vụ rò vào controller hay HTTP rò vào tầng data.

**Decision:** Ba lớp với allowed-imports nghiêm ngặt. Controller: HTTP + Service interface + Request/Resource. Service: logic domain + repo interface + event bus. Repository: Eloquent/SDK. Controller không chạm DB; Repository không quyết định quyền. deptrac ép quy tắc.

**Consequences:**
- (+) Service test được không cần server; Repository thay được.
- (+) Logic tập trung, dễ đọc.
- (−) Nhiều file/lớp hơn cho thao tác đơn giản — chấp nhận để có nhất quán.
