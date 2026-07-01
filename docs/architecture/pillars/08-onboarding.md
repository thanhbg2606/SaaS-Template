> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Onboarding & Activation
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 8. Onboarding & Activation

Cửa sổ 5 phút giữa lúc đăng ký và "giá trị đầu tiên" là UX đòn bẩy cao nhất bạn từng xây.

### 8.1 Flow đăng ký

```
1. /signup → email + password (hoặc OAuth)
2. Gửi email xác thực NGAY (nhưng đừng chặn vào app)
3. Bước "tạo workspace"
4. Vào sản phẩm với guided tour một lần
5. Kích "aha-moment" đầu tiên trong ≤ 3 click
```

### 8.2 Activation event

Định nghĩa **một activation event** — action dự báo retention (Slack: gửi 2000 tin; Dropbox: upload 1 file; Linear: tạo 3 issue). Lưu `activated_at` trên workspace, bắn từ event bus, và **trigger lifecycle email** từ đó.

### 8.3 Empty states là bề mặt sản phẩm

Mọi list view cần empty state: một câu ngữ cảnh + nút CTA chính + (tùy) hook "import từ CSV/Jira".
