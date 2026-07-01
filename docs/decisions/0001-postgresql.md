> [Decisions](./README.md) › ADR-0001
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0001 — PostgreSQL làm CSDL chính

**Status:** Accepted

**Context:** Cần một CSDL quan hệ làm nguồn sự thật. Template hướng tới search full-text, semantic search (vector), tenant isolation cấp DB, và partition cho bảng lớn (audit log).

**Decision:** Dùng **PostgreSQL 16+** thay vì MySQL. Bật các extension: `pg_trgm` (typo tolerance), `pgvector` (embedding/semantic search). Dùng **Row-Level Security (RLS)** làm lớp phòng thủ tenant.

**Consequences:**
- (+) Một DB lo 90% việc: FTS, vector, RLS, JSONB, partition — không cần thêm hệ thống.
- (+) RLS cho isolation tenant ở tầng DB (lớp 2 sau global scope).
- (−) Team quen MySQL cần học khác biệt (RLS, một số cú pháp).
- (−) Một số package Laravel mặc định nghĩ tới MySQL — cần kiểm tra migration.
