> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Real-time & Eventing
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 13. Real-time & Eventing

### 13.1 In-process event bus (xương sống)

Laravel Events/Listeners chính là in-process bus. Listener ghi derived state, enqueue job, broadcast qua WS. **Subscriber đăng ký trước publisher** — thứ tự quan trọng, document trong `EventServiceProvider`.

### 13.2 WebSocket vs SSE

| Nhu cầu | Dùng |
|---|---|
| Hai chiều (chat, collab editing) | **WebSocket** |
| Chỉ server → client (dashboard live, notification) | **SSE** (đơn giản hơn) |

### 13.3 Triển khai trong Laravel

- **Laravel Reverb** — WS server first-party (released 2024), tự host, scale qua Redis.
- **Echo** — client subscribe channel.
- Hoặc Pusher (managed) nếu không muốn tự host.

Channel có thể map theo tenant: `private-workspace.{workspaceId}.issues`.

### 13.4 Quy tắc Realtime ↔ Cache (vàng)

> **WS event invalidate cache query. Không bao giờ ghi thẳng vào client store.**

Vì WS message có thể đến lệch thứ tự, bị drop, bị replay. Invalidate là idempotent; ghi thẳng thì không.

```js
// Frontend (TanStack Query)
echo.private(`workspace.${wsId}.issues`)
    .listen('IssueUpdated', ({ id }) => {
        queryClient.invalidateQueries(['issue', id])
    })
```
