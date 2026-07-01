# Architecture Decision Records (ADR)

ADR ghi lại **các quyết định kiến trúc quan trọng kèm lý do** để người sau hiểu "tại sao chọn X" thay vì phải đoán. File này là **index**; mỗi ADR cũng nên có file riêng trong `docs/decisions/NNNN-tieu-de.md` cho dự án thật.

## Cách dùng

- Một quyết định mới → thêm ADR mới (số tăng dần), **không sửa** ADR cũ. Muốn đảo quyết định → ADR mới với `Status: Supersedes ADR-XXXX`.
- Trạng thái: `Proposed` · `Accepted` · `Deprecated` · `Superseded`.
- Format mỗi ADR: **Context** (bối cảnh/vấn đề) → **Decision** (quyết định) → **Consequences** (hệ quả, gồm cả mặt trái).

## Index

| # | Quyết định | Status | Mục liên quan |
|---|---|---|---|
| [0001](./0001-postgresql.md) | PostgreSQL làm CSDL chính | Accepted | §4.2, §10 |
| [0002](./0002-multitenancy-pool-rls.md) | Multi-tenancy: mô hình Pool + RLS | Accepted | §5 |
| [0003](./0003-modular-monolith.md) | Kiến trúc ứng dụng: Modular Monolith (Kernel/Product) | Accepted | §4.5, §32 |
| [0004](./0004-layering.md) | Phân lớp Controller → Service → Repository | Accepted | §4.3 |
| [0005](./0005-auth-strategy.md) | Auth: cookie session (browser) + token (API/mobile) + API key | Accepted | §6 |
| [0006](./0006-billing-stripe-cashier.md) | Billing: Stripe qua Laravel Cashier, bảng subscription provider-agnostic | Accepted | §9 |
| [0007](./0007-uuid-v7.md) | Khóa chính: UUID v7 | Accepted | §4.6, §10.1 |
| [0008](./0008-eventing-outbox.md) | Eventing: in-process Events + outbox (afterCommit); hoãn NATS | Accepted | §12.3, §13 |
| [0009](./0009-jobs-redis-horizon.md) | Background jobs: Redis queue + Horizon | Accepted | §12 |
| [0010](./0010-realtime-reverb.md) | Real-time: Laravel Reverb | Accepted | §13.3 |
| [0011](./0011-search-postgres-scout.md) | Search: Postgres FTS + Scout; pgvector cho semantic | Accepted | §16 |
| [0012](./0012-feature-flags-pennant.md) | Feature flags: Laravel Pennant | Accepted | §17 |
| [0013](./0013-admin-filament.md) | Admin panel: Filament | Accepted | §24.3 |
| [0014](./0014-deptrac-boundaries.md) | Ép ranh giới module bằng deptrac ở CI | Accepted | §32.2 |
| [0015](./0015-frontend-inertia-react.md) | Frontend: Inertia.js + React (TS, Tailwind, shadcn/ui) | Accepted | §22 |

---

Mỗi ADR là một file riêng trong thư mục này (`NNNN-*.md`); thêm quyết định mới bằng cách copy [`template.md`](./template.md). Index trên là chỗ liếc nhanh.
