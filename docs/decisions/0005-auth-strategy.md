> [Decisions](./README.md) › ADR-0005
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0005 — Auth: cookie session + token + API key

**Status:** Accepted

**Context:** Nhiều bề mặt: web SPA, mobile/CLI, public API, integration. Mỗi cái có nhu cầu bảo mật khác.

**Decision:** Dùng cơ chế lai theo bề mặt (không trộn trong cùng bề mặt):
- Browser → **cookie session HttpOnly** (Sanctum SPA mode), dễ revoke.
- Mobile/CLI → **token ngắn hạn + refresh**.
- Public API → **API key** per-workspace, có scope, hash `sha256`.
Backend dùng **Fortify** (login/2FA/reset headless) + **Socialite** (Google/GitHub OAuth). **Passport** chỉ thêm khi cần làm OAuth2 server cho bên thứ ba.

**Consequences:**
- (+) Mỗi bề mặt dùng cơ chế phù hợp; tận dụng package trưởng thành thay vì tự viết auth (tarpit).
- (−) Nhiều luồng cần test (session fixation, refresh rotation, API key revoke).
- (−) Phụ thuộc nhiều package Laravel auth — cần nắm rõ ranh giới mỗi cái.
