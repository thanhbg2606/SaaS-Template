> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Audit Logs, Activity Feeds & Telemetry
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 18. Audit Logs, Activity Feeds & Telemetry

### 18.1 Ba thứ khác nhau, hay bị nhầm

| Khái niệm | Đối tượng | Lưu trữ | Tính chất |
|---|---|---|---|
| **Audit log** | Compliance/security | Nhiều năm | Bất biến, append-only |
| **Activity feed** | End user ("Alice đổi tiêu đề") | Tháng | Tóm tắt, sửa được |
| **Telemetry/analytics** | Team bạn | Tháng–năm | Tổng hợp, ẩn danh |

Đừng dùng một bảng cho cả ba.

### 18.2 Audit log

```sql
audit_logs (
  id UUID PK, workspace_id UUID, actor_user_id UUID NULL,
  actor_type TEXT,   -- user | api_key | system
  action TEXT,       -- "issue.delete", "billing.plan.change", "auth.login"
  target_type TEXT, target_id UUID, metadata JSON,
  ip_address INET, user_agent TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
-- không bao giờ UPDATE/DELETE; partition theo tháng
```

Package Laravel: **`spatie/laravel-activitylog`** hoặc `owen-it/laravel-auditing`. Log mọi action đặc quyền: đổi settings, đổi role, đổi billing, mời/xóa member, xóa file, login, đổi mật khẩu, bật/tắt MFA.

### 18.3 Export

User gói enterprise muốn export audit log (CSV/JSON/Splunk). Build endpoint sau feature flag.
