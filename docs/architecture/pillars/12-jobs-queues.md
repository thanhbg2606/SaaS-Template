> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Background Jobs, Queues & Schedulers
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 12. Background Jobs, Queues & Schedulers

### 12.1 Hệ job trong Laravel

| Thành phần | Laravel |
|---|---|
| Queue driver | Redis (khuyến nghị), database, SQS |
| Dashboard + monitoring | **Horizon** (Redis) |
| Fan-out (gửi 1000 email) | **`Bus::batch([...])`** |
| Workflow nhiều bước | **Job chaining** (`Bus::chain`) |
| Scheduled | **Scheduler** (`routes/console.php` / `app/Console`) + một cron `* * * * * php artisan schedule:run` |
| Rate-limit job | `WithoutOverlapping`, `RateLimited` middleware |

### 12.2 Idempotency

Mọi handler phải chịu được gọi hai lần. Dùng unique key `(job_type, dedup_key)` hoặc check-then-act trong transaction. Job có `ShouldBeUnique` để tránh trùng.

### 12.3 Outbox pattern — "DB write + phát event" cùng transaction

Đây chính là chỗ **Events/Listeners + `ShouldDispatchAfterCommit`** của Laravel giải bài toán:

```php
DB::transaction(function () use ($order) {
    $this->orders->create($order);
    OrderCreated::dispatch($order);   // listener queue, bắn SAU commit
});
```

Để event/job chỉ phát sau khi transaction commit thành công:

```php
class OrderCreated implements ShouldDispatchAfterCommit { /* ... */ }
// hoặc với job:
class SyncToStripe implements ShouldQueue
{
    public $afterCommit = true;
}
```

Nếu cần đảm bảo mạnh hơn (cross-service, replay), dựng bảng `outbox` thật + một worker poll.

### 12.4 Cron / scheduled

Dùng **một** scheduler đã dedup — không phải `cron` per box (sẽ chạy trùng trên multi-instance). Laravel Scheduler + `onOneServer()` (cần Redis/DB cache lock) giải đúng việc này. Log mỗi lần chạy + thời lượng vào bảng `cron_runs`.

### 12.5 Long-running progress

Cho job user thấy được ("Đang import 50.000 contact…"): lưu dòng `job` với `status`, `progress_pct`, `total`, `current`. Worker cập nhật mỗi N item. UI poll `GET /jobs/{id}` hoặc subscribe WS. `Bus::batch` cho `progress()` sẵn.

### 12.6 Tầng trên queue: durable execution

Khi job thành **workflow nhiều bước có thể dừng hàng giờ, fan-out + join, sống sót qua crash giữa bước, cần exactly-once đầu-cuối** (onboarding subscription, pipeline nhiều ngày, agent chờ phê duyệt người) → queue bắt đầu đuối. Đó là lúc cân nhắc **Temporal** (PHP SDK có) hoặc giữ trong Laravel với job chaining + batch + state machine table cho tới khi thực sự cần.
