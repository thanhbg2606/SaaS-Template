> **SaaS Template Playbook** › [Mục lục](../README.md) › Hướng dẫn › Thêm Product module

# Module Extension Guide

Cách thêm một **Product module** mới mà không đụng Kernel, và tham chiếu các **contract + hook** mà kernel cung cấp để domain cắm vào. Đây là tài liệu bạn mở mỗi khi xây tính năng nghiệp vụ mới.

Tham chiếu: [§4 tổng quan](../architecture/overview.md), [§32 kernel/product](../architecture/kernel-product.md) · ranh giới ép bởi `deptrac` (ADR-0014).

---

## Quy tắc vàng (deptrac chặn nếu vi phạm)

1. Product **chỉ phụ thuộc vào Kernel qua interface** trong `App\Kernel\Contracts`, không gọi implementation cụ thể.
2. Product module này **không truy cập Eloquent model** của Product module khác — chỉ qua public Service interface + domain event.
3. **Kernel không bao giờ import Product** (một chiều).
4. Trong module: Controller → Service → Repository. Controller không chạm DB.

---

## Giải phẫu một Product module

```
app/Product/Tasks/
  Models/
    Task.php
  Repositories/
    TaskRepository.php            (interface)
    EloquentTaskRepository.php    (implementation)
  Services/
    TaskService.php
  Http/
    Controllers/TaskController.php
    Requests/CreateTaskRequest.php
    Resources/TaskResource.php
  Policies/
    TaskPolicy.php
  Events/
    TaskCreated.php
  Listeners/
    SendTaskCreatedNotification.php
  Providers/
    TasksServiceProvider.php
database/migrations/xxxx_create_tasks_table.php
routes/product.php                (route khai báo ở đây, load bởi provider)
tests/Feature/Tasks/...
```

---

## Quy trình thêm module mới (ví dụ: `Tasks`)

### 1. Migration (theo convention + RLS)

Xem [data-model](../architecture/data-model.md) → "Bảng Product cắm vào". Bắt buộc: `id` UUID v7, `workspace_id` NOT NULL, timestamps, soft delete nếu cần, actor đa hình, index `(workspace_id, status, created_at)`, **RLS policy**.

```php
Schema::create('tasks', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('workspace_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->string('status')->default('open');
    $table->uuid('created_by_id')->nullable();
    $table->string('created_by_type')->nullable();
    $table->softDeletes();
    $table->timestamps();
    $table->index(['workspace_id', 'status', 'created_at']);
});

DB::statement('ALTER TABLE tasks ENABLE ROW LEVEL SECURITY');
DB::statement("CREATE POLICY task_tenant_isolation ON tasks
    USING (workspace_id = NULLIF(current_setting('app.workspace_id', true), '')::uuid)");
```

### 2. Model (dùng trait kernel)

```php
namespace App\Product\Tasks\Models;

use App\Kernel\Tenancy\BelongsToTenant;   // global scope workspace_id
use Illuminate\Database\Eloquent\Concerns\HasUuids;   // Laravel 11+ → UUID v7 mặc định
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use BelongsToTenant, HasUuids, SoftDeletes;

    protected $fillable = ['title', 'status'];
}
```

### 3. Repository (interface + impl)

```php
interface TaskRepository
{
    public function create(string $workspaceId, array $attrs): Task;
    public function paginate(string $workspaceId, array $filters): CursorPaginator;
}

class EloquentTaskRepository implements TaskRepository
{
    public function create(string $workspaceId, array $attrs): Task
    {
        return Task::create([...$attrs, 'workspace_id' => $workspaceId]);
    }
    // ...
}
```

### 4. Service (logic domain + kernel contracts + event)

```php
use App\Kernel\Contracts\Meter;       // ✅ interface kernel
use App\Kernel\Contracts\Flags;

class TaskService
{
    public function __construct(
        private TaskRepository $tasks,
        private Meter $meter,
        private Flags $flags,
    ) {}

    public function create(Actor $actor, string $workspaceId, array $data): Task
    {
        return DB::transaction(function () use ($actor, $workspaceId, $data) {
            $task = $this->tasks->create($workspaceId, $data);

            $this->meter->increment($workspaceId, 'task.created', 1);
            TaskCreated::dispatch($task);   // afterCommit (outbox)

            return $task;
        });
    }
}
```

### 5. Controller (mỏng) + FormRequest + Resource

```php
class TaskController extends Controller
{
    public function __construct(private TaskService $svc) {}

    public function store(CreateTaskRequest $request): JsonResponse
    {
        $task = $this->svc->create(
            $request->actor(), tenant()->id, $request->validated()
        );
        return TaskResource::make($task)->response()->setStatusCode(201);
    }
}
```

### 6. Policy (RBAC qua spatie/permission)

```php
class TaskPolicy
{
    public function update(User $user, Task $task): bool
    {
        // User toàn cục → so với tenant ĐANG hành động, không phải thuộc tính trên User.
        return $user->hasPermissionTo('task.update')
            && tenant()->id === $task->workspace_id;
    }
}
```

### 7. Event chỉ phát sau commit (outbox)

```php
class TaskCreated implements ShouldDispatchAfterCommit
{
    public function __construct(public Task $task) {}
}
```

### 8. Routes (versioned)

```php
// routes/product.php
Route::middleware(['auth:sanctum', 'tenant'])->prefix('api/v1')->group(function () {
    Route::apiResource('tasks', TaskController::class);
});
```

### 9. Đăng ký module qua ServiceProvider

```php
class TasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaskRepository::class, EloquentTaskRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/product.php');
        // Migration đặt ở database/migrations/ gốc → Laravel TỰ auto-load, không cần
        // loadMigrationsFrom. (Chỉ gọi loadMigrationsFrom khi để migration TRONG module.)
        Gate::policy(Task::class, TaskPolicy::class);
    }
}
```

Thêm vào `bootstrap/providers.php` (Laravel 11+).

### 10. Test (Pest)

```php
it('tạo task và bắn event sau commit', function () {
    Event::fake([TaskCreated::class]);
    $actor = actingAsMember();      // helper test có workspace

    postJson('/api/v1/tasks', ['title' => 'Demo'])
        ->assertCreated();

    Event::assertDispatched(TaskCreated::class);
    expect(Task::count())->toBe(1);
});
```

---

## Tham chiếu Kernel Contracts (ports)

Inject các interface này trong Service; **không** gọi SDK trực tiếp. Implementation bind ở kernel Service Provider — đổi provider là đổi một dòng `bind()`.

| Contract | Dùng để | Implementation mặc định |
|---|---|---|
| `App\Kernel\Contracts\Mailer` | Gửi email giao dịch | `ResendMailer` |
| `App\Kernel\Contracts\Storage` | Sinh signed URL upload/download | `S3Storage` |
| `App\Kernel\Contracts\Bus` | Publish domain event cross-cutting | `InProcessBus` (→ NATS sau) |
| `App\Kernel\Contracts\Meter` | Đo usage cho billing | `RedisMeter` |
| `App\Kernel\Contracts\Flags` | Kiểm tra feature flag | `PennantFlags` |
| `App\Kernel\Contracts\Cache` | Cache có tag-invalidation | `RedisCache` |

Ví dụ dùng:

```php
// File upload — qua Storage contract, không gọi S3 SDK
$url = $this->storage->presignPut($key, new PutOptions(ttl: 900, maxBytes: 100_000_000));

// Feature gating — qua Flags contract
if (! $this->flags->enabled('ai-assist', FlagScope::workspace($wsId))) {
    throw new FeatureLockedException('ai-assist');
}

// Email — qua Mailer contract
$this->mailer->send(new TaskAssignedMessage($task, $assignee));
```

---

## Tham chiếu Kernel Hooks (điểm cắm domain)

Kernel phát các event lifecycle; Product implement listener để cắm vào — **không sửa kernel**.

| Hook (event kernel) | Khi nào bắn | Ví dụ listener domain |
|---|---|---|
| `Kernel\Lifecycle\UserSignedUp` | User đăng ký xong | Tạo demo project cho workspace |
| `Kernel\Lifecycle\WorkspaceCreated` | Workspace mới tạo | Seed dữ liệu khởi tạo domain |
| `Kernel\Lifecycle\WorkspaceActivated` | Activation event bắn | Gửi welcome email + báo Slack |
| `Kernel\Billing\PlanChanged` | Đổi gói | Enforce limit domain, archive overflow |
| `Kernel\Billing\SubscriptionCanceled` | Hủy subscription | Chuyển workspace sang read-only |
| `Kernel\Tenancy\MemberInvited` | Mời thành viên | Logic domain khi có member mới |

Đăng ký listener trong `ProductServiceProvider` hoặc `EventServiceProvider`:

```php
Event::listen(WorkspaceCreated::class, SeedDomainDataListener::class);
Event::listen(WorkspaceActivated::class, SendDomainWelcomeListener::class);
```

> Activation event của riêng bạn khai báo trong `config/product.php` → `onboarding.activation_event` (ví dụ `task.created`). Kernel theo dõi và bắn `WorkspaceActivated` lần đầu event đó xảy ra.

---

## Checklist khi thêm module

- [ ] Migration theo convention (UUID v7, `workspace_id`, timestamps, index) + **RLS policy**.
- [ ] Model dùng `BelongsToTenant, HasUuids` (+ `SoftDeletes` nếu cần).
- [ ] Repository tách interface ↔ impl; Service nhận interface.
- [ ] Service dùng kernel **qua contract**, không gọi SDK trực tiếp.
- [ ] Controller mỏng (≤ 20 dòng); FormRequest validate; API Resource serialize.
- [ ] Policy cho RBAC; mọi action đặc quyền ghi audit log.
- [ ] Event nghiệp vụ phát trong transaction (`ShouldDispatchAfterCommit`).
- [ ] Route versioned (`api/v1`), có `auth:sanctum` + `tenant` middleware.
- [ ] Đăng ký ServiceProvider; thêm vào `bootstrap/providers.php`.
- [ ] Test Pest (gồm test event + tenant isolation).
- [ ] `sail composer deptrac` xanh (không vi phạm ranh giới).
