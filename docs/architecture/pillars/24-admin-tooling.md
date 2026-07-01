> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Admin & Internal Tooling
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 24. Admin & Internal Tooling

### 24.1 Xây ngày đầu. Đừng bỏ qua.

Bạn sẽ trực support-debug cả năm. Admin panel trả lại vốn trong tuần thứ hai.

### 24.2 Cái gì nằm trong đó

Search user/workspace · **impersonate** (read-only mặc định) · suspend/unsuspend workspace · force-verify email · refund/credit · đổi plan/quota · re-send webhook · replay failed job · soi Stripe customer · override feature flag per tenant.

### 24.3 Triển khai Laravel

- **Filament** (free, rất mạnh, nhanh) hoặc **Nova** (paid, first-party) — dựng CRUD admin trong vài giờ.
- Cùng codebase, gate sau claim `is_internal_admin`.
- Hostname riêng (`admin.yourtool.com`) + route group riêng.
- Mọi action audit-log với `actor_user_id` = **nhân viên** (không phải user bị impersonate).
- **MFA bắt buộc**, session time-box (re-auth mỗi 30 phút), IP-allowlist tùy chọn.

### 24.4 BI cho team business

Sales/CS/finance hỏi cùng kiểu câu mỗi tuần ("MRR theo plan?", "trial→paid theo nguồn?"). Dựng BI dashboard chạy trên **read replica** (không bao giờ trên primary OLTP) ngay khi có khách trả tiền: **Apache Superset** (license Apache 2.0, mặc định an toàn) hoặc **Metabase** (UX dễ hơn nhưng AGPL — verify pháp lý nếu định embed vào sản phẩm). Cùng auth gate như admin (SSO + MFA).
