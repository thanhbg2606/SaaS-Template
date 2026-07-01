> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Testing Strategy
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 28. Testing Strategy

### 28.1 Kim tự tháp

```
   E2E (Dusk/Playwright)      5–10%   chậm, giá trị
   Integration (DB thật)      20–30%  đòn bẩy lớn nhất
   Unit                       60–70%  feedback nhanh
```

### 28.2 Quy tắc (Pest/PHPUnit)

- **Unit** co-located, chạy nhanh. Laravel **Pest** là default đẹp.
- **Integration** spin up Postgres + Redis thật (testcontainers / service trong CI). Dùng `RefreshDatabase`.
- **E2E** chạy full stack trên tag + main.

### 28.3 Flow user phải E2E

1. Đăng ký → verify email → tạo workspace → activation event đầu.
2. Mời teammate → chấp nhận → cả hai thấy cùng data.
3. Nâng plan → feature mở khóa ngay.
4. Hủy plan → hạ cấp lên lịch cuối kỳ.
5. Quên mật khẩu → reset → login lại.

Vỡ một trong số này = cả sản phẩm vỡ.

### 28.4 Coverage

Nhắm ~70% line trên package logic nặng. Dưới = lỗ hổng; trên 90% = đang test getter tầm thường.
