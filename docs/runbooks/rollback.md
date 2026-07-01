> **SaaS Template Playbook** › [Mục lục](../README.md) › Vận hành › Rollback

# Runbook — Rollback

Nền tảng: [§26.5 Rollback](../architecture/pillars/26-cicd-release.md).

## Nguyên tắc
- Mỗi release là **artifact bất biến** (image tag `sha256`) → rollback = trỏ lại artifact trước, **< 60s**.
- **Migration forward‑only**: rollback nghĩa là *chưa chạy migration mới*, **không** undo migration đã chạy.
  - Vì migration luôn backward‑compatible (xem [deploy.md](./deploy.md)), app version cũ vẫn chạy được trên DB đã migrate.

## Các bước
1. Xác định artifact tốt gần nhất (tag/sha trước).
2. Trỏ traffic về artifact đó (blue‑green switch / rolling về image cũ).
3. **Không** chạy lệnh "undo migration". Nếu sự cố do migration phá hủy → xử lý bằng forward‑fix (migration mới) chứ không drop ngược.
4. Nếu lỗi do tính năng sau flag → **tắt flag** (kill‑switch) thay vì rollback toàn bộ ([§17.3](../architecture/pillars/17-feature-flags.md)).
5. Ghi nhận: mở post‑mortem ([incident-oncall.md](./incident-oncall.md)).
