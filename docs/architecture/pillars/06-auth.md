> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Auth & Authorization
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 6. Auth & Authorization

### 6.1 Phương thức auth cần hỗ trợ

- **Email + password** (luôn luôn — kể cả khi có SSO).
- **Magic link** (UX tốt nhất cho sản phẩm low-stakes).
- **OAuth**: Google + GitHub tối thiểu. Apple nếu có app iOS.
- **MFA**: TOTP — dễ thêm, tín hiệu tin cậy lớn.
- **Passkeys (WebAuthn)** — ngày càng được kỳ vọng.
- **SSO (SAML 2.0 + OIDC)** — gate sau gói enterprise.
- **API keys** — per-workspace, có scope, revoke được, hash bằng `sha256`.
- **PATs** — cho CLI, có rotation.

### 6.2 Packages Laravel

| Nhu cầu | Package |
|---|---|
| Backend auth (login/register/2FA/reset) | **Fortify** (headless) |
| SPA session + API token | **Sanctum** |
| OAuth2 server đầy đủ (cấp token cho bên thứ ba) | **Passport** |
| Đăng nhập OAuth (Google/GitHub…) | **Socialite** |
| UI scaffolding (nếu muốn) | Breeze / Jetstream |

**Quy tắc session vs JWT:** cookie session (HttpOnly, server-side, dễ revoke) cho browser → Sanctum SPA mode. Token ngắn hạn cho mobile/CLI. API key cho integration. Đừng trộn cơ chế trong cùng một bề mặt.

### 6.3 Authorization — RBAC trước, ABAC nếu cần

Bắt đầu với **RBAC**: roles `owner | admin | member | viewer`.

```bash
composer require spatie/laravel-permission
```

Tập trung mọi logic quyền vào **Policy** + **Gate**. Đừng inline `if ($user->role === 'admin')` trong controller.

```php
// app/Kernel/Auth/Policies/ProjectPolicy.php
public function update(User $user, Project $project): bool
{
    // User là identity TOÀN CỤC (thuộc nhiều workspace qua memberships) → đối chiếu
    // workspace ĐANG hành động (tenant đã resolve), KHÔNG dùng thuộc tính trên User.
    return $user->hasPermissionTo('project.update')
        && tenant()->id === $project->workspace_id;
}
```

Chỉ thêm **ABAC** (ví dụ "user X chỉ sửa resource có `assignee_id = user.id`") khi RBAC sinh ra điều kiện không bảo trì nổi.

### 6.4 Checklist bảo mật auth

- [ ] Password hash bằng `argon2id` (hoặc bcrypt cost ≥ 12) — Laravel `bcrypt` mặc định, đổi sang `argon2id` trong `config/hashing.php`.
- [ ] Chống email enumeration (cùng response cho "không tìm thấy email" và "sai mật khẩu").
- [ ] Rate limit `/login` (5/phút/IP + 10/giờ/email) — RateLimiter.
- [ ] Lockout sau N lần sai, có email cảnh báo.
- [ ] CSRF trên endpoint cookie-auth.
- [ ] Rotate session ID khi login (chống session fixation) — `session()->regenerate()`.
- [ ] Logout hủy session phía server.
- [ ] Refresh token rotate khi dùng; revoke cả "family" nếu phát hiện reuse.
- [ ] Reset token single-use, hết hạn 1h, gửi tới email đã verify.
- [ ] MFA backup code sinh ra, hiện một lần, hash khi lưu.
