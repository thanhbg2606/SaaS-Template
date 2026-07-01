> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › File Storage, Uploads & CDN
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 15. File Storage, Uploads & CDN

### 15.1 Quy tắc tối thượng

> **Không bao giờ cho byte file đi qua API server.** Client upload trực tiếp lên S3 qua signed URL.

```
[Client] ──GET /upload-url──► [API] ──signed PUT URL──► [Client]
[Client] ──PUT───────────────────────────────────────► [S3]
[Client] ──POST /confirm──► [API] (lưu metadata)
```

### 15.2 Signed URL trong Laravel

Filesystem driver S3 + `temporaryUrl()` (GET) / `temporaryUploadUrl()` (PUT):

```php
// temporaryUploadUrl() trả MẢNG KẾT HỢP ['url' => ..., 'headers' => ...] → destructure theo key:
['url' => $url, 'headers' => $headers] = Storage::disk('s3')->temporaryUploadUrl(
    $key, now()->addMinutes(15)
);
```

Luôn set: TTL (15 phút), ràng buộc `Content-Type`, giới hạn `Content-Length`, prefix key theo tenant: `s3://bucket/{workspace_id}/{file_id}`.

### 15.3 Metadata & scanning

```sql
files (id, workspace_id, uploader_user_id, filename, mime_type,
       size_bytes, s3_key, sha256, status, created_at)
       -- status: pending | uploaded | scanned | quarantined
```

File user upload → scan khi upload (S3 event → Lambda/worker → ClamAV). Tới khi scan xong, `status = pending` và từ chối serve.

### 15.4 CDN

Cloudflare/CloudFront trước S3. Signed URL cho private (5–60 phút). Asset public (avatar) có path vĩnh viễn + cache-bust bằng content hash.
