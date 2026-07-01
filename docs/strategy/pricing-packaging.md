> **SaaS Template Playbook** › [Mục lục](../README.md) › Chiến lược sản phẩm
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../section-map.md).

# 29. Pricing, Plans & Packaging

### 29.1 Ba trục giá

1. **Per-seat** — collaboration (Slack, Linear). Dễ đoán.
2. **Usage-based** — infra & AI (OpenAI, Vercel). Khớp giá trị, khó budget.
3. **Per-feature tier** — breadth (HubSpot). Cho sales upsell.

Đa số kết hợp cả ba: per-seat × tier + usage add-on.

### 29.2 Tier khởi đầu (ship 3, không phải 6)

```
Free / Hobby   — 1 user, X resource, feature giới hạn   → top of funnel
Starter / Pro  — N user, full feature, $/seat/tháng      → SMB / cá nhân trả tiền
Team / Business— unlimited user, feature nâng cao        → mid-market
Enterprise     — SSO, audit export, DPA riêng, support   → contact sales
```

### 29.3 Sau paywall

Free: core value (scoped) · Pro/Team: depth (advanced field, automation, API) · Enterprise: trust (SSO, SCIM, audit export, SLA).

### 29.4 Trial vs Freemium — chọn một

Trial (14 ngày, full) = áp lực thương mại cao, quyết định nhanh. Freemium = volume top-funnel, conversion khó. **B2B template → mặc định trial.**
