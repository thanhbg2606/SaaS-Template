> **SaaS Template Playbook** › [Mục lục](../README.md) › Chiến lược sản phẩm
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../section-map.md).

# 30. Product Analytics & Growth

### 30.1 Hai stack analytics

Product (**PostHog** / Mixpanel / Amplitude — "user activate? convert? churn?") vs Engineering (OTel → Grafana — "hệ thống khỏe không?"). **PostHog** gộp analytics + session replay + flag + A/B trong một.

### 30.2 Event phải track từ ngày đầu

`signed_up` · `activated` · `<core_action>_created` · `invited_member` / `member_accepted` · `upgraded_plan` / `cancelled_subscription` · `viewed_paywall` / `clicked_upgrade`. Mọi event có `workspace_id` + `user_id`.

### 30.3 Funnel phải đo

Đăng ký → email-verified → workspace-created → activated · Activation → invite → user thứ hai activated · Free → paywall view → upgrade · Subscribed → renewal (LTV/churn).

### 30.4 Cohort retention

Vẽ retention theo cohort tuần đăng ký. SaaS khỏe có đường "smile" — giảm ngắn hạn, phẳng/lên dài hạn. Retention về 0 thì marketing không cứu nổi sản phẩm.
