> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Performance, Caching & Scaling
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 20. Performance, Caching & Scaling

### 20.1 Latency budget

API user-facing < 500ms p95. Đặt làm budget cứng. Vượt → tối ưu hoặc async hóa.

### 20.2 Tầng cache

```
[CDN]            — asset public, docs public, trang marketing
[App-level]      — Redis (hot reads, computed view, rate-limit counter)
[DB query cache] — Postgres shared buffers
[DB read replica]— route endpoint đọc nặng (search, report)
```

### 20.3 Quy tắc

- **Invalidation > duration.** Luôn biết một giá trị cache bị invalidate thế nào. Đừng set TTL dài "phòng hờ".
- **Tag-based invalidation**: key cache theo `(workspace_id, kind, version)`, bump version khi ghi. Laravel `Cache::tags()`.

### 20.4 Chống N+1

`EXPLAIN ANALYZE` trên hot endpoint · `with()` eager-load · `Model::preventLazyLoading()` ở dev · CI check log slow query (`pg_stat_statements`).

### 20.5 Scale Postgres (thứ tự)

1. **Index** — sửa cái thiếu trước (90% "chậm" là "thiếu index").
2. **Connection pooling** — PgBouncer transaction mode.
3. **Read replica** — route report đọc nặng (Laravel multiple DB connection).
4. **Partition** — theo `workspace_id`/`created_at` cho bảng khổng lồ.
5. **Vertical scaling** — box to hơn (đi xa bất ngờ).
6. **Sharding** — chỉ khi bắt buộc. Last resort.

### 20.6 Laravel Octane (lưu ý riêng)

PHP truyền thống bootstrap lại mỗi request. **Octane** (Swoole/RoadRunner) giữ app trong memory → throughput cao hơn nhiều. Bẫy: state rò rỉ giữa request (static, singleton chứa data user). Audit kỹ trước khi bật.
