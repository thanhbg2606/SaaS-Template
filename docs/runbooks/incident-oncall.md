> **SaaS Template Playbook** › [Mục lục](../README.md) › Vận hành › Incident & on‑call

# Runbook — Incident & On‑call

Nền tảng: [§21.4 On‑call & runbook](../architecture/pillars/21-observability.md) và [§21.3 Golden signals + SLO](../architecture/pillars/21-observability.md).

## Khi nhận alert
1. **Phân loại mức**: Sev‑1 (mất dịch vụ / rò rỉ dữ liệu) · Sev‑2 (suy giảm nặng) · Sev‑3 (nhỏ).
2. **Pivot tín hiệu bằng `request_id` + `tenant_id`**: từ lỗi Sentry → trace → log → request ([§21.1](../architecture/pillars/21-observability.md)).
3. **Khoanh vùng**: golden signals — Latency (p50/p95/p99) · Traffic · Errors (5xx/4xx) · Saturation (CPU/mem/DB pool/**queue depth**).

## Sự cố thường gặp → hành động nhanh
| Triệu chứng | Việc cần làm |
|---|---|
| Một query chậm hạ cả API | `SET LOCAL statement_timeout`; kiểm `EXPLAIN ANALYZE`; xem index ([§20.5](../architecture/pillars/20-performance-scaling.md)) |
| Worker fail âm thầm / DLQ tăng | Kiểm Horizon; alert DLQ depth; replay job ([§12](../architecture/pillars/12-jobs-queues.md)) |
| Webhook Stripe lặp/sai state | Kiểm idempotency theo `event.id` ([§9.3](../architecture/pillars/09-billing.md)) |
| Nghi rò rỉ tenant | Kiểm RLS + global scope; query cross‑tenant phải đi code path có audit ([§5](../architecture/multi-tenancy.md)) |
| Tính năng mới gây lỗi | **Tắt feature flag** trước, điều tra sau ([§17.3](../architecture/pillars/17-feature-flags.md)) |
| Release lỗi | [rollback.md](./rollback.md) |

## Sau sự cố
- **Burn error budget**? Nếu hết → đóng băng feature, ưu tiên reliability ([§21.3 SLO](../architecture/pillars/21-observability.md)).
- **Post‑mortem blameless** cho mọi Sev‑1/2, lưu trong repo (vd `docs/runbooks/postmortems/`).
