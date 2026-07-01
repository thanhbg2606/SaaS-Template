> **SaaS Template Playbook** › [Mục lục](../README.md) › Kiến trúc › Quyết định xương sống
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../section-map.md).

# 5. Multi-tenancy — quyết định nền móng

Lựa chọn kiến trúc hệ trọng nhất. Quyết định ngày đầu và **ép buộc bằng code**.

### 5.1 Ba mô hình

| Mô hình | Mô tả | Khi nào dùng |
|---|---|---|
| **Pool (shared)** | Một DB, mọi dòng gắn `workspace_id` | **Mặc định cho B2B SaaS.** Ops/chi phí tốt nhất |
| **Bridge (silo schema)** | Một DB, mỗi tenant một schema | Mid-enterprise; migrate được từng tenant |
| **Silo (isolated DB)** | Mỗi tenant một DB | Tenant bị quản chế (bank, healthcare), khách VIP |

**Khuyến nghị:** Bắt đầu với **Pool**. Thêm Silo sau như gói enterprise. Đừng làm cả ba ngày đầu.

### 5.2 Triển khai Pool trong Laravel

**Cách 1 — Global Scope + Trait (đơn giản, kiểm soát cao):**

```php
// app/Kernel/Tenancy/BelongsToTenant.php
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (! $model->workspace_id && tenant()) {
                $model->workspace_id = tenant()->id;
            }
        });
    }
}

// TenantScope: tự động thêm WHERE workspace_id = ?
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($id = optional(tenant())->id) {
            $builder->where($model->getTable().'.workspace_id', $id);
        }
    }
}
```

Mọi model tenant-scoped chỉ cần `use BelongsToTenant;`. Để query xuyên tenant (admin) thì `Model::withoutGlobalScope(TenantScope::class)` — và route này phải đi qua code path riêng, có audit.

**Cách 2 — Package `stancl/tenancy`:** hỗ trợ cả single-DB (pool) lẫn multi-DB (silo). Mạnh khi bạn cần đường nâng cấp lên silo. `spatie/laravel-multitenancy` đơn giản hơn nếu chỉ cần switch connection.

### 5.3 Postgres RLS — lớp phòng thủ chiều sâu

Kể cả khi dev quên `WHERE workspace_id = ?`, RLS chặn rò rỉ:

```sql
ALTER TABLE issues ENABLE ROW LEVEL SECURITY;

CREATE POLICY issue_tenant_isolation ON issues
    USING (workspace_id = NULLIF(current_setting('app.workspace_id', true), '')::uuid);
```

`current_setting(..., true)` (tham số `missing_ok = true`) trả `NULL` thay vì ném lỗi khi GUC chưa set (job/worker/migration chạy ngoài Tenant middleware); `NULLIF(..., '')` tránh lỗi cast chuỗi rỗng. Khi chưa có tenant → policy khớp 0 dòng (fail-closed), không vỡ runtime.

Trong Tenant middleware (Laravel):

```php
// SET không nhận bound parameter (utility statement) → dùng set_config() qua SELECT có bind:
DB::statement("SELECT set_config('app.workspace_id', ?, false)", [(string) tenant()->id]);
// tham số thứ 3: false = phạm vi session; true = phạm vi transaction (tương đương SET LOCAL)
```

> Global Scope = lớp 1 (tiện). RLS = lớp 2 (an toàn). Dùng cả hai.

### 5.4 Quy tắc "two-actor"

Mọi query có 2 tham số ngầm: `actor_user_id` (ai đang hỏi) + `tenant_id` (đang hành động trong tenant nào). Đừng chỉ chấp nhận "user đã đăng nhập" — cùng một user có thể thuộc nhiều workspace.

### 5.5 Resolve tenant

- **Subdomain**: `acme.app.yourtool.com` → `acme` → tra workspace.
- **Path**: `app.yourtool.com/w/acme/...`
- **Header**: `X-Workspace-ID: <uuid>` (tốt cho API, nhưng UI cần workspace switcher).

Chọn một và giữ nguyên.
