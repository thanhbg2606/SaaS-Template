# Contributing Guide

Cảm ơn bạn đã đóng góp. Tài liệu này mô tả quy trình làm việc, chuẩn code và các **quy tắc kiến trúc bắt buộc**. Đọc kèm [`docs/`](./docs/README.md) (kiến trúc) và [`docs/decisions/`](./docs/decisions/README.md) (lý do các quyết định). Tra §-number ở [`docs/section-map.md`](./docs/section-map.md).

---

## 1. Thiết lập môi trường

Xem [`README.md`](./README.md) → "Khởi động nhanh". Tóm tắt: `cp .env.example .env`, `composer install`, `sail up -d`, `sail artisan migrate --seed`, `sail composer dev`.

## 2. Branch strategy

Trunk-based, nhẹ nhàng:

```
main        ← luôn deployable, được bảo vệ (chỉ merge qua PR đã pass CI)
  └── feat/<scope>-<mô-tả-ngắn>     ví dụ: feat/billing-trial-banner
  └── fix/<scope>-<mô-tả-ngắn>      ví dụ: fix/auth-session-fixation
  └── chore/... docs/... refactor/...
```

- Nhánh feature ngắn ngày (< vài ngày), rebase thường xuyên trên `main`.
- Không commit thẳng vào `main`.
- Tag release theo SemVer: `v1.4.0`. Deploy prod kích bằng tag (§26.4).
- Tính năng rủi ro → ship sau **feature flag** (Pennant), không phải nhánh dài. Deploy tách khỏi release.

## 3. Commit convention (Conventional Commits)

```
<type>(<scope>): <mô tả ngắn, thì hiện tại>

[body tùy chọn — giải thích "tại sao", không phải "cái gì"]

[footer tùy chọn: BREAKING CHANGE:, Refs #123]
```

**type:** `feat` · `fix` · `refactor` · `perf` · `test` · `docs` · `chore` · `ci` · `build`
**scope:** tên module (`auth`, `tenancy`, `billing`, `projects`…)

Ví dụ:
```
feat(billing): thêm banner đếm ngược trial
fix(tenancy): set app.workspace_id trước khi query trong job
refactor(auth): tách logic MFA ra MfaService
```

## 4. Quy trình Pull Request

1. Tạo nhánh từ `main`.
2. Code + viết test. Chạy `sail composer check` (lint + stan + deptrac + test) cho **xanh ở local** trước khi push.
3. Mở PR, điền template (mô tả, ảnh chụp nếu là UI, cách test, và mọi exception với constitution nếu có).
4. CI phải pass toàn bộ gate. Cần ≥ 1 review approve.
5. Squash-merge vào `main` (giữ history sạch).

### PR checklist

- [ ] Đã chạy `sail composer check` xanh ở local.
- [ ] Có test cho hành vi mới / bug đã fix.
- [ ] Nếu có ngoại lệ về test, UX, performance, hoặc quality gate, PR đã ghi rõ phạm vi, rủi ro, mitigation, owner, và điều kiện cleanup.
- [ ] Bảng mới (nếu có) theo convention: `id` (UUID v7), `workspace_id`, `created_at`, `updated_at`, có RLS policy.
- [ ] Không vi phạm ranh giới module (deptrac xanh).
- [ ] Action đặc quyền mới có ghi `audit_log`.
- [ ] Endpoint mới có rate limit + validate input (FormRequest).
- [ ] Không hardcode secret; biến mới đã thêm vào `.env.example`.
- [ ] Cập nhật docs nếu thay đổi public API / contract / quyết định kiến trúc (thêm ADR).

## 5. Coding standards

| Khía cạnh | Chuẩn | Công cụ |
|---|---|---|
| Code style | PSR-12 | **Pint** (`sail composer lint`) |
| Static analysis | Larastan level 6+ | `sail composer stan` |
| Ranh giới kiến trúc | Kernel/Product + layering | **deptrac** (`sail composer deptrac`) |
| Test | Pest | `sail composer test` |

### Quy ước đặt tên

- Class: `PascalCase`. Method/biến: `camelCase`. Hằng: `UPPER_SNAKE`.
- Bảng DB: số nhiều, `snake_case` (`workspaces`, `team_memberships`).
- Controller mỏng (≤ 20 dòng) → logic ở Service. FormRequest validate. API Resource serialize.
- Action/event/audit dùng dạng `domain.verb`: `issue.created`, `billing.plan.changed`.

## 6. Quy tắc kiến trúc BẮT BUỘC

Đây là phần CI và reviewer soi kỹ nhất (chi tiết §4, §5, §32):

1. **Kernel không bao giờ import Product.** Phụ thuộc một chiều. (deptrac chặn.)
2. **Module không truy cập Eloquent model của module khác.** Chỉ qua public service interface + domain event.
3. **Phân lớp:** Controller không chạm DB; Repository không quyết định quyền. (deptrac chặn nhảy lớp.)
4. **Mọi query tenant-scoped lọc `workspace_id`** — dùng trait `BelongsToTenant` (global scope) + RLS làm lớp 2.
5. **Bảng mới** theo convention ID + `workspace_id` + timestamps + RLS policy.
6. **Phát event trong transaction** dùng `ShouldDispatchAfterCommit` / `$afterCommit = true` (outbox — §12.3).
7. **Không gọi bên thứ ba (Stripe/S3/HTTP) đồng bộ trong request path** — đẩy vào queue.
8. **Side-effect thanh toán** chỉ qua webhook Stripe (idempotent), không phải request path.
9. **Cross-cutting** (mail, storage, bus, cache, flags) dùng qua **interface** trong `Kernel/Contracts`, không gọi SDK trực tiếp.
10. **Migration append-only** — không sửa migration đã chạy.

## 7. Testing

Kim tự tháp: Unit (60–70%) · Integration với DB thật (20–30%) · E2E (5–10%). Chi tiết §28.

- Unit/feature: Pest, co-located. Dùng `RefreshDatabase` cho test chạm DB.
- Bắt buộc có test cho 5 flow then chốt nếu PR động tới: signup→verify→workspace→activation · invite→accept · upgrade→unlock · cancel→downgrade · reset password.
- Nhắm ~70% coverage trên package logic nặng. Đừng test getter tầm thường.

```bash
sail composer test                          # toàn bộ
sail artisan test --filter=BillingTest      # một nhóm
sail artisan test --coverage                # kèm coverage
```

## 8. CI gates (GitHub Actions, < 10 phút)

```
1. Install deps (cache composer + npm)
2. Pint (lint)        ─┐
3. Larastan (stan)     ├─ song song, fail fast
4. deptrac (ranh giới) ─┘
5. Pest (Postgres + Redis làm service)
6. E2E (chỉ trên main + tag)
```

PR không merge được nếu bất kỳ gate nào đỏ.

## 9. Definition of Done

Một thay đổi "done" khi: code + test xanh ở CI · review approve · docs cập nhật (kèm ADR nếu là quyết định kiến trúc) · không nợ kỹ thuật mới không được ghi nhận · deploy được sau merge (backward-compatible với DB hiện tại — §26.3).

Ngoài ra, mọi thay đổi hoàn tất phải tuân thủ constitution hiện hành: chất lượng
code có bằng chứng, test coverage phù hợp với rủi ro, UX nhất quán, và tác động
performance đã được kiểm tra hoặc ghi nhận ngoại lệ rõ ràng.

---

## Khi thêm quyết định kiến trúc

Nếu PR thay đổi một lựa chọn kiến trúc (đổi provider, đổi pattern, thêm dependency lớn), **viết một ADR mới** trong [`docs/decisions/`](./docs/decisions/README.md) (copy [`template.md`](./docs/decisions/template.md)) và thêm dòng vào index. Quyết định không có ADR = quyết định sẽ bị quên lý do.
