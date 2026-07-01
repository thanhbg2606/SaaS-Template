> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Email, Notifications & Inbox
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 14. Email, Notifications & Inbox

### 14.1 Ba bề mặt notification

| Bề mặt | Provider | Dùng cho |
|---|---|---|
| Email giao dịch | Resend / Postmark / SES | Verify, reset, invite, receipt, dunning |
| Inbox trong app | DB của bạn | Mention, comment, status change |
| Push / SMS | Vonage / OneSignal / APNS | Cảnh báo mobile quan trọng |

### 14.2 Triển khai Laravel

Hệ **Notifications** của Laravel quản tất cả qua channel:

```php
class IssueAssigned extends Notification implements ShouldQueue
{
    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast']; // 3 bề mặt cùng lúc
    }
    public function toMail($notifiable): MailMessage { /* ... */ }
    public function toDatabase($notifiable): array { /* → inbox */ }
}
```

- **Mailable** + Markdown mail cho template; plain-text fallback luôn có.
- Channel `database` = inbox trong app (bảng `notifications` sẵn của Laravel).
- Channel `broadcast` = đẩy live qua Reverb (bell icon + unread count).

### 14.3 Preference từng user

```sql
notification_preferences (user_id, workspace_id, channel, event_type, enabled)
```

Mọi notification check preference trước khi gửi. Mặc định event mới = "on" nhưng luôn cho opt-out một click.

### 14.4 Unsubscribe & digest

Mọi email giao dịch (trừ security/billing) có `List-Unsubscribe` header + link footer, unsubscribe một click. Sự kiện khối lượng lớn (mention, comment) → push real-time nếu online, không thì gom thành digest (giờ/ngày), cấu hình per user.
