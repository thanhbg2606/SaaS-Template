> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Observability — Logs, Metrics, Traces, Errors
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 21. Observability — Logs, Metrics, Traces, Errors

### 21.1 Bốn tín hiệu (tương quan)

| Tín hiệu | Công cụ | Trả lời |
|---|---|---|
| **Logs** | Loki / Datadog / CloudWatch | Đã xảy ra gì? |
| **Metrics** | Prometheus / Grafana | Bao nhiêu, nhanh chậm, thường xuyên? |
| **Traces** | Jaeger / Tempo / Honeycomb | Thời gian tiêu ở đâu? |
| **Errors** | **Sentry** | Cái gì vỡ, reproduce sao? |

Cả bốn chia sẻ **`request_id`** + **`tenant_id`** để pivot giữa nhau.

### 21.2 Trong Laravel

- **Sentry Laravel SDK** — error tracking, nối với trace.
- **Pulse** (first-party) — perf monitoring (slow query, slow job, slow request).
- **Telescope** (first-party) — debug assistant cho **dev/staging** (đừng bật prod public).
- **OpenTelemetry** — package `open-telemetry/opentelemetry-php` + auto-instrumentation; export vendor-neutral.
- Structured logging: cấu hình Monolog ra JSON, mọi log trong request kèm `request_id`, `workspace_id`, `user_id`. Một event một dòng, key snake_case, **không interpolate chuỗi** (`"user $id did X"` phá structured search).

### 21.3 Bốn golden signal + SLO

Latency (p50/p95/p99) · Traffic (req/s) · Errors (5xx + 4xx then chốt) · Saturation (CPU/mem/DB pool/queue depth). Định nghĩa 1–2 SLO (vd "99.9% request < 500ms / 30 ngày → error budget 43 phút/tháng"). Burn hết budget → đóng băng feature, sửa reliability.

### 21.4 On-call & runbook

Mỗi alert có **runbook URL** trong text. Runbook sống trong repo (`docs/runbooks/`), không phải Confluence. Post-mortem cho mọi Sev-1/2: blameless, in-repo.
