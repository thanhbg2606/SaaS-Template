# Section Map — §-number → file

Doc kiến trúc gốc (`saas-template-laravel.md`, 35 mục) đã tách theo trụ cột. **Số §-number được giữ nguyên làm ID bền**: mọi tham chiếu dạng `§N` hoặc `§N.x` trong tài liệu/ADR/commit vẫn hợp lệ — tra bảng dưới để tới file. Tiểu mục `§N.x` nằm trong chính file của `§N` (anchor = tiêu đề tiểu mục đó).

| § | Chủ đề | File |
|---|---|---|
| §1–§4 | Tư duy nền tảng, kiến trúc tham chiếu, phân lớp, seam, ID | [architecture/overview.md](./architecture/overview.md) |
| §5 | Multi‑tenancy (Pool + RLS) | [architecture/multi-tenancy.md](./architecture/multi-tenancy.md) |
| §6 | Auth & Authorization | [architecture/pillars/06-auth.md](./architecture/pillars/06-auth.md) |
| §7 | Accounts, Organizations, Workspaces, Teams | [architecture/pillars/07-accounts-teams.md](./architecture/pillars/07-accounts-teams.md) |
| §8 | Onboarding & Activation | [architecture/pillars/08-onboarding.md](./architecture/pillars/08-onboarding.md) |
| §9 | Billing, Subscriptions & Metering | [architecture/pillars/09-billing.md](./architecture/pillars/09-billing.md) |
| §10 | Database Design Patterns | [architecture/pillars/10-database.md](./architecture/pillars/10-database.md) |
| §11 | API Design | [architecture/pillars/11-api.md](./architecture/pillars/11-api.md) |
| §12 | Background Jobs, Queues & Schedulers | [architecture/pillars/12-jobs-queues.md](./architecture/pillars/12-jobs-queues.md) |
| §13 | Real‑time & Eventing | [architecture/pillars/13-realtime-eventing.md](./architecture/pillars/13-realtime-eventing.md) |
| §14 | Email, Notifications & Inbox | [architecture/pillars/14-notifications.md](./architecture/pillars/14-notifications.md) |
| §15 | File Storage, Uploads & CDN | [architecture/pillars/15-files.md](./architecture/pillars/15-files.md) |
| §16 | Search (Full‑Text + Semantic) | [architecture/pillars/16-search.md](./architecture/pillars/16-search.md) |
| §17 | Feature Flags & Experiments | [architecture/pillars/17-feature-flags.md](./architecture/pillars/17-feature-flags.md) |
| §18 | Audit Logs, Activity Feeds & Telemetry | [architecture/pillars/18-audit-activity.md](./architecture/pillars/18-audit-activity.md) |
| §19 | Security, Compliance & Privacy | [architecture/pillars/19-security-compliance.md](./architecture/pillars/19-security-compliance.md) |
| §20 | Performance, Caching & Scaling | [architecture/pillars/20-performance-scaling.md](./architecture/pillars/20-performance-scaling.md) |
| §21 | Observability | [architecture/pillars/21-observability.md](./architecture/pillars/21-observability.md) |
| §22 | Frontend Architecture | [architecture/pillars/22-frontend.md](./architecture/pillars/22-frontend.md) |
| §23 | i18n & Accessibility | [architecture/pillars/23-i18n-a11y.md](./architecture/pillars/23-i18n-a11y.md) |
| §24 | Admin & Internal Tooling | [architecture/pillars/24-admin-tooling.md](./architecture/pillars/24-admin-tooling.md) |
| §25 | Marketing Site, Docs & SEO | [architecture/pillars/25-marketing-docs-seo.md](./architecture/pillars/25-marketing-docs-seo.md) |
| §26 | CI/CD, Environments & Release | [architecture/pillars/26-cicd-release.md](./architecture/pillars/26-cicd-release.md) |
| §27 | Developer Experience (DX) | [architecture/pillars/27-dx.md](./architecture/pillars/27-dx.md) |
| §28 | Testing Strategy | [architecture/pillars/28-testing.md](./architecture/pillars/28-testing.md) |
| §29 | Pricing, Plans & Packaging | [strategy/pricing-packaging.md](./strategy/pricing-packaging.md) |
| §30 | Product Analytics & Growth | [strategy/analytics-growth.md](./strategy/analytics-growth.md) |
| §31 | Customer Support & Success | [strategy/support.md](./strategy/support.md) |
| **§32** | **Tách Kernel / Product** | [architecture/kernel-product.md](./architecture/kernel-product.md) |
| §33–§35 + Đúc kết | Lộ trình 14 phase, bẫy, cheat sheet | [strategy/roadmap-14-phase.md](./strategy/roadmap-14-phase.md) |

> **Lưu ý §32:** trong doc gốc, §32 được kéo lên cạnh §5 (cùng là "quyết định xương sống") nên số 32 nằm trước §6–§31. Khi tách file, thứ tự không còn gây nhầm; **số §32 vẫn giữ nguyên** để không vỡ ~30 tham chiếu hiện có. Đừng đánh số lại các §.
