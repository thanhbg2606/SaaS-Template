> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Security, Compliance & Privacy
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 19. Security, Compliance & Privacy

### 19.1 OWASP không thể thương lượng

- Parameterized query (không bao giờ nối chuỗi SQL) — Eloquent/Query Builder lo sẵn nếu không dùng `whereRaw` ẩu.
- Validate input ở mọi biên (FormRequest + rule).
- Output encoding (Blade `{{ }}` tự escape; cẩn thận với `{!! !!}` và PDF/HTML thô).
- CSRF token trên endpoint cookie-auth (Laravel sẵn).
- CSP header (`Content-Security-Policy: default-src 'self'`).
- HSTS (`Strict-Transport-Security: max-age=63072000; includeSubDomains; preload`).
- Cookie: `Secure; HttpOnly; SameSite=Lax`.
- Validate type + size + MIME khi upload.

### 19.2 Secrets management

- **Không bao giờ commit secret.** Pre-commit hook `gitleaks`/`detect-secrets`.
- Local: `.env` (gitignored). Prod: Vault / Doppler / Infisical / Forge env.
- Rotate khi đổi nhân sự và khi nghi rò rỉ.

### 19.3 Phân loại data

Public (tên workspace) · Private (email, IP, địa chỉ billing) · Sensitive (password hash, OAuth token, API key — encrypt at rest với KMS) · Restricted (PCI/HIPAA/COPPA — outsource cho provider tuân thủ như Stripe, tránh tự lưu).

### 19.4 Compliance theo tier

| Compliance | Khi nào cần |
|---|---|
| **GDPR / CCPA** | Ngày 1 nếu có user EU/US |
| **SOC 2** | Khi prospect enterprise hỏi (3–6 tháng chuẩn bị) |
| **HIPAA** | Chỉ vertical healthcare |
| **PCI-DSS** | Nếu chạm card data — outsource Stripe → SAQ-A |

Cho template: bake sẵn **primitive GDPR** (endpoint export data, xóa tài khoản, consent log, tag data residency). Hoãn SOC 2 tới khi có tiền trên bàn.

### 19.5 Primitive GDPR

- **Export my data**: zip mọi dòng user sở hữu dạng JSON.
- **Delete my account**: ẩn danh PII, giữ audit log với `user_id = NULL`.
- **Consent log**: `consents (user_id, type, version, granted_at, ip)`.
- **DPA** + **Subprocessor list** (trang public) + **Data residency** (tag tenant + route).
