> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Feature Flags & Experiments
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 17. Feature Flags & Experiments

### 17.1 Ba scope flag

```
flag → environment (dev/staging/prod)
     → workspace (rollout cấp tenant)
     → user (override cá nhân)
```

Resolve: env default → workspace override → user override.

### 17.2 Laravel Pennant (first-party)

```bash
composer require laravel/pennant
```

```php
Feature::define('new-billing-engine', fn (Workspace $ws) =>
    $ws->created_at->isAfter(now()->subMonth())
);

if (Feature::for($workspace)->active('new-billing-engine')) {
    return $this->newPath();
}
```

### 17.3 Văn hóa kill-switch

**"Không nằm sau flag thì không được ship."** Sau 2 tuần rollout ổn định: dọn flag + nhánh code chết.

### 17.4 A/B test

Ship qua cùng hệ flag với assignment ngẫu nhiên. Log assignment + outcome vào analytics. Quyết significance bằng thư viện thống kê / PostHog — đừng nhìn bằng mắt.
