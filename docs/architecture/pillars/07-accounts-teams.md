> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Accounts, Organizations, Workspaces, Teams
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 7. Accounts, Organizations, Workspaces, Teams

### 7.1 Phân cấp chuẩn

```
User ─┬─► Membership ─► Workspace (tenant)
      │                   ├── Teams (nhóm con)
      │                   ├── Resources (projects, issues…)
      │                   ├── Subscription (Stripe)
      │                   └── Settings (branding, SSO…)
      └─► Personal account (tùy chọn — cho gói solo)
```

`User` là identity toàn cục. `Membership` nối user với workspace kèm role.

### 7.2 Bảng tối thiểu

```
users (id, email, password, email_verified_at, mfa_enabled, created_at)
workspaces (id, slug, name, plan, owner_user_id, created_at)
memberships (id, user_id, workspace_id, role, status, invited_by, joined_at)
invites (id, workspace_id, email, role, token_hash, expires_at, accepted_at)
teams (id, workspace_id, name, parent_team_id NULL)
team_memberships (id, team_id, user_id, role)
api_keys (id, workspace_id, name, prefix, hash, scopes JSON, last_used_at, revoked_at)
```

### 7.3 Lời mời (Invites)

- Email một token signed single-use (hết hạn 7 ngày) — dùng `Signed URL` của Laravel.
- Chấp nhận → tạo dòng `membership`.
- **Quan trọng:** nếu người được mời đã có tài khoản → chỉ gắn membership, đừng ép họ qua flow đăng ký lại.

### 7.4 Offboarding & xóa

- **Xóa tài khoản** (GDPR): ẩn danh PII, giữ audit log với `user_id = NULL` + `display_name = "Deleted user"`.
- **Rời workspace**: chỉ xóa dòng membership.
- **Xóa workspace**: soft-delete 30 ngày có restore. Hard-delete sau grace period qua scheduled job.
