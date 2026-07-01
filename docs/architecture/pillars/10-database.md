> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Database Design Patterns
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 10. Database Design Patterns

### 10.1 Conventions

- Tên bảng số nhiều (Laravel convention: `users`, `issues`).
- Mọi bảng có: `id` (UUID v7), `created_at`, `updated_at`, và `workspace_id` (nếu tenant-scoped).
- Soft delete: `deleted_at` + partial unique index `WHERE deleted_at IS NULL` — Laravel `SoftDeletes` trait.
- Bảng history append-only cho thứ cần provenance (audit log, billing event, webhook).

### 10.2 Migrations

- **Luôn tiến.** Không sửa migration đã chạy — tạo cái mới để fix.
- **Online migration**: không block ghi trên bảng nóng. Thêm cột nullable → backfill theo batch → thêm NOT NULL ở migration sau.
- Chạy tự động khi deploy (có gate/dry-run cho prod).

### 10.3 Index trả tiền thuê

- Mọi foreign key.
- Mọi cột trong `WHERE` bạn thực sự lọc (chạy `EXPLAIN ANALYZE`).
- `(workspace_id, status, created_at DESC)` cho query "list X cho tenant" điển hình.
- Partial index cho soft delete.

### 10.4 Transactions

- Bọc mọi thao tác multi-write trong transaction: `DB::transaction(fn () => …)`.
- **Đừng giữ transaction xuyên qua HTTP/RPC.** Đọc trước, làm việc bên ngoài, ghi nhanh.
- DB write + phát event nằm cùng transaction (xem outbox §12).

### 10.5 Ergonomics — và cảnh báo về Eloquent

Eloquent năng suất nhưng là loại ORM "giấu SQL" mà playbook gốc lưu ý. Hai cái bẫy:
- **N+1 ngầm**: dùng `with()` eager-load cho hot path. Bật `Model::preventLazyLoading()` ở dev/staging để bắt sớm.
- Với báo cáo nặng/analytics, cân nhắc Query Builder thô hoặc raw SQL thay vì hydrate cả nghìn model.
- Seeder tạo data thực tế cho dev (`php artisan db:seed`).
