> **SaaS Template Playbook** › [Mục lục](../README.md) › Vận hành › Deploy

# Runbook — Deploy

Chi tiết chiến lược: [§26 CI/CD & Release](../architecture/pillars/26-cicd-release.md). Đây là checklist thao tác.

## Tiền đề
- CI xanh toàn bộ gate (Pint · Larastan · deptrac · Pest). Xem [CONTRIBUTING §8](../../CONTRIBUTING.md).
- Mỗi release là **artifact bất biến** (container image tag `sha256`).
- Thang môi trường: `dev → preview (per‑PR) → staging → production`.

## Các bước
1. **Tag release** theo SemVer (`v1.4.0`) — tag kích pipeline deploy production.
2. **Migration chạy tự động trước app code**: `php artisan migrate --force`.
   - Bắt buộc **backward‑compatible**: app phiên bản N+1 phải chạy được với DB version N trong lúc rolling.
   - Migration phá hủy (drop column) → **"2‑deploy dance"**: ngừng đọc cột (deploy 1) → drop cột (deploy 2).
3. **Release kiểu rolling/blue‑green** — không stop‑the‑world. Thay đổi rủi ro → canary `1% → 10% → 50% → 100%` kèm metric gate.
4. **Feature flag tách deploy khỏi release**: deploy bất kỳ lúc nào, bật tính năng khi sẵn sàng ([§17](../architecture/pillars/17-feature-flags.md)).
5. **Smoke check sau deploy**: health endpoint, login, 1 luồng nghiệp vụ chính, dashboard golden signals ([§21.3](../architecture/pillars/21-observability.md)).

## Nếu hỏng
→ [rollback.md](./rollback.md).
