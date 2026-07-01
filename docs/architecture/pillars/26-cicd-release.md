> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › CI/CD, Environments & Release Strategy
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 26. CI/CD, Environments & Release Strategy

### 26.1 Thang môi trường

```
dev (laptop) → ephemeral preview (per-PR) → staging → production
```

Preview env per PR (URL riêng + DB seed). Staging mirror config prod, DB riêng. Production là môi trường duy nhất khách thấy.

### 26.2 CI pipeline (giữ < 10 phút) — GitHub Actions

```
1. Install deps (cache composer + npm)
2. Lint (Pint) + static analysis (PHPStan/Larastan)  — song song
3. Unit tests (Pest)                                  — song song
4. Build artifact
5. Integration tests (Postgres + Redis làm service)
6. E2E (Dusk / Playwright) — chỉ trên main + tag
7. Deploy preview (PR) / staging (main) / prod (tag)
```

Fail fast: lint + static analysis trước test.

### 26.3 Migration khi deploy

Chạy **tự động** trước app code (`php artisan migrate --force`). Luôn backward-compatible: app N+1 phải chạy với DB version N (tạm thời lúc rollout). Migration phá hủy (drop column) → "2-deploy dance": ngừng đọc → deploy → drop ở deploy sau.

### 26.4 Release strategy

Blue-green / rolling — không bao giờ stop-the-world. Canary cho thay đổi rủi ro (1% → 10% → 50% → 100% với metric gate). **Feature flag tách deploy khỏi release**: deploy bất kỳ lúc nào, release khi sẵn sàng.

### 26.5 Rollback

Mỗi release là một artifact bất biến (container image tag sha256). Rollback < 60s về artifact trước. Migration forward-only → rollback nghĩa là *chưa chạy migration mới*, không phải undo.

### 26.6 Host ở đâu (và khi nào đổi)

| Giai đoạn | Host |
|---|---|
| Local dev | Docker (Laravel **Sail**) |
| Deploy prod đầu | **Forge** (VPS) / **Vapor** (serverless AWS) / Ploi |
| Giai đoạn có lãi | **Hetzner** + Forge quản, hoặc DO |
| Enterprise/compliance | AWS/GCP/Azure |

Laravel có lợi thế: **Forge** (provision + deploy + SSL + queue worker + scheduler trên VPS bất kỳ) và **Vapor** (serverless, auto-scale) là path ít ops nhất.
