> [Decisions](./README.md) › ADR-0012
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0012 — Feature flags: Laravel Pennant

**Status:** Accepted

**Context:** Cần tách deploy khỏi release, rollout theo tenant/user, kill-switch.

**Decision:** **Laravel Pennant** (first-party). Resolve theo 3 scope: env default → workspace override → user override. Văn hóa: "không nằm sau flag thì không ship". Dọn flag + nhánh chết sau 2 tuần rollout ổn định.

**Consequences:**
- (+) First-party, tích hợp sẵn, có scope linh hoạt.
- (−) Cần kỷ luật dọn flag cũ kẻo tích nợ.
