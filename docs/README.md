# Tài liệu — SaaS Template Playbook (Laravel)

Đây là **mục lục** của toàn bộ tài liệu. Cổng vào dự án (quick‑start, chạy local) là [`../README.md`](../README.md); file này điều hướng phần *nội dung sâu*.

> **Nếu chỉ đọc 2 mục, hãy đọc [§5 Multi‑tenancy](./architecture/multi-tenancy.md) và [§32 Tách Kernel/Product](./architecture/kernel-product.md)** — hai quyết định này chi phối mọi thứ còn lại. Đọc thêm [§9 Billing](./architecture/pillars/09-billing.md) và [§19 Security](./architecture/pillars/19-security-compliance.md) là đủ để làm chủ playbook.

Tài liệu được tổ chức theo **thể loại** (Diátaxis‑lite): *reference* (kiến trúc) · *how‑to* (guides) · *explanation* (decisions) · *operations* (runbooks) · *strategy*.

---

## Bắt đầu từ đâu (theo vai trò)

| Bạn là… | Đọc theo thứ tự |
|---|---|
| **Fork để làm sản phẩm mới** | [guides/reskin-fork](./guides/reskin-fork.md) → [architecture/overview](./architecture/overview.md) → [kernel-product (§32)](./architecture/kernel-product.md) → ADR [0002](./decisions/0002-multitenancy-pool-rls.md) & [0003](./decisions/0003-modular-monolith.md) |
| **Thêm một domain module** | [guides/module-extension](./guides/module-extension.md) → [architecture/data-model](./architecture/data-model.md) → trụ cột liên quan trong [pillars/](./architecture/pillars/) |
| **Vận hành (deploy / on‑call)** | [runbooks/](./runbooks/) → [§26 CI/CD](./architecture/pillars/26-cicd-release.md) → [§21 Observability](./architecture/pillars/21-observability.md) |
| **Đóng góp code** | [../CONTRIBUTING.md](../CONTRIBUTING.md) → [decisions/](./decisions/) (hiểu "tại sao" trước khi đổi) |

---

## Mục lục

### Kiến trúc (reference — "hệ thống xây thế nào")
- [Tổng quan kiến trúc (§1–§4)](./architecture/overview.md) — mental model, 12 trụ cột, kiến trúc tham chiếu, phân lớp, seam/contract, ID conventions
- [§5 — Multi‑tenancy](./architecture/multi-tenancy.md) · [§32 — Tách Kernel / Product](./architecture/kernel-product.md) — **hai quyết định xương sống**
- [Mô hình dữ liệu (ERD + schema kernel)](./architecture/data-model.md)

#### Trụ cột (§6–§28) — `architecture/pillars/`
| | | |
|---|---|---|
| [§6 Auth](./architecture/pillars/06-auth.md) | [§7 Accounts/Teams](./architecture/pillars/07-accounts-teams.md) | [§8 Onboarding](./architecture/pillars/08-onboarding.md) |
| [§9 Billing](./architecture/pillars/09-billing.md) | [§10 Database](./architecture/pillars/10-database.md) | [§11 API](./architecture/pillars/11-api.md) |
| [§12 Jobs & Queues](./architecture/pillars/12-jobs-queues.md) | [§13 Real‑time & Eventing](./architecture/pillars/13-realtime-eventing.md) | [§14 Notifications](./architecture/pillars/14-notifications.md) |
| [§15 Files](./architecture/pillars/15-files.md) | [§16 Search](./architecture/pillars/16-search.md) | [§17 Feature Flags](./architecture/pillars/17-feature-flags.md) |
| [§18 Audit & Activity](./architecture/pillars/18-audit-activity.md) | [§19 Security & Compliance](./architecture/pillars/19-security-compliance.md) | [§20 Performance & Scaling](./architecture/pillars/20-performance-scaling.md) |
| [§21 Observability](./architecture/pillars/21-observability.md) | [§22 Frontend](./architecture/pillars/22-frontend.md) | [§23 i18n & a11y](./architecture/pillars/23-i18n-a11y.md) |
| [§24 Admin & Tooling](./architecture/pillars/24-admin-tooling.md) | [§25 Marketing/Docs/SEO](./architecture/pillars/25-marketing-docs-seo.md) | [§26 CI/CD & Release](./architecture/pillars/26-cicd-release.md) |
| [§27 DX](./architecture/pillars/27-dx.md) | [§28 Testing](./architecture/pillars/28-testing.md) | |

### Hướng dẫn (how‑to — "tôi cần làm X") — `guides/`
- [Fork & Reskin](./guides/reskin-fork.md) — biến template thành sản phẩm có brand riêng (≤ 30 phút)
- [Thêm Product module](./guides/module-extension.md) — thêm domain mới mà không đụng Kernel + tham chiếu Kernel Contracts

### Quyết định kiến trúc (explanation — "tại sao chọn thế") — `decisions/`
- [Index 14 ADR + cách viết ADR mới](./decisions/README.md) · khuôn: [template.md](./decisions/template.md)

### Vận hành (operations) — `runbooks/`
- [Index runbooks](./runbooks/README.md) · [Deploy](./runbooks/deploy.md) · [Rollback](./runbooks/rollback.md) · [Incident & on‑call](./runbooks/incident-oncall.md)

### Chiến lược sản phẩm (§29–§35) — `strategy/`
- [§29 Pricing & Packaging](./strategy/pricing-packaging.md) · [§30 Analytics & Growth](./strategy/analytics-growth.md) · [§31 Support](./strategy/support.md)
- [Lộ trình 14 phase, bẫy & cheat sheet (§33–§35)](./strategy/roadmap-14-phase.md)
- [**Implementation plan — Xương sống (Phase 1–5)**](./strategy/implementation-plan-phase1-5.md) — plan build nền móng (skeleton→billing)

---

## Quy ước

- **Số §-number được giữ nguyên làm ID bền** dù file đã tách. Tra cứu nhanh `§x.y → file#anchor` ở [section-map.md](./section-map.md).
- Tên file ASCII/kebab‑case; nội dung tiếng Việt. ADR đặt `NNNN-slug.md`, append‑only (không sửa ADR cũ — đảo quyết định bằng ADR mới).
- Mỗi file có breadcrumb ở đầu để biết vị trí trong cây.
