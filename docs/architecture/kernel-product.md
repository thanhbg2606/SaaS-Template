> **SaaS Template Playbook** › [Mục lục](../README.md) › Kiến trúc › Quyết định xương sống
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../section-map.md).

# 32. Tách kernel / product

Để template fork được cho từng sản phẩm, code domain phải tách sạch khỏi hạ tầng.

### 32.1 Cấu trúc thư mục

```
app/
  Kernel/              ← mọi SaaS đều có
    Auth/  Tenancy/  Billing/  Notifications/
    Audit/  Admin/  Files/  Search/  Flags/  Support/
    Contracts/         ← các interface (seam)
  Product/             ← domain của bạn
    Models/  Services/  Http/  Jobs/  Events/
config/
  product.php          ← brand, features, plans (config-over-code)
```

### 32.2 Quy tắc cứng

- `Kernel/` **không bao giờ import** `Product/`. Phụ thuộc một chiều.
- `Product/` mở rộng kernel qua hook/interface, **không sửa kernel**.
- Bảng tenant-scoped mới theo đúng convention: `id`, `workspace_id`, `created_at`, có RLS policy.
- Domain event publish trên cùng event bus.
- UI domain dùng cùng design system + permission helper.

### 32.3 Config-over-code

Hầu hết tùy biến "theo sản phẩm" nên là config, không phải code:

```php
// config/product.php
return [
    'brand'    => ['name' => 'MyApp', 'primary_color' => '#5B5BD6'],
    'features' => ['audit_log_export' => true, 'custom_domains' => false],
    'plans'    => [
        ['name' => 'starter', 'price_cents' => 1900, 'limits' => ['members' => 5]],
    ],
];
```

### 32.4 Điểm cắm domain (hook)

| Hook | Ví dụ dùng |
|---|---|
| `OnSignup(user, workspace)` | Tự tạo demo project |
| `OnActivated(workspace)` | Gửi welcome email + báo Slack |
| `MeterEvent(name, qty)` | Đo usage riêng cho domain |
| `RenderEmail(template, data)` | Email giao dịch riêng domain |

Trong Laravel: các hook này là **Event** (kernel phát) + **Listener** (product implement), hoặc binding interface.

### 32.5 Checklist reskin (phút, không phải ngày)

- [ ] Cập nhật `config/product.php`
- [ ] Thay logo, favicon, OG image
- [ ] Cập nhật màu trong `tailwind.config.js`
- [ ] Cập nhật copy marketing
- [ ] Tạo Stripe products + prices, dán ID vào config
- [ ] Thêm model domain vào `app/Product/Models/`
- [ ] Wire route/trang domain
- [ ] Cập nhật seeder với demo data domain
