> **SaaS Template Playbook** › [Mục lục](../README.md) › Hướng dẫn › Fork từ template

# Fork from Template Guide

Guide này dành cho **team nội bộ đã biết Laravel** và cần hai thứ:

1. Fork template xong thì **chạy được local trong 10–15 phút**
2. Biết rõ **bước tiếp theo để rebrand và bắt đầu build feature đầu tiên**

Nếu bạn cần playbook đầy đủ hơn cho việc đổi brand, pricing, Stripe, asset, và lộ trình reskin kỹ hơn, đọc tiếp [Reskin / Fork Guide](./reskin-fork.md). File này là đường ngắn nhất để từ template sang một repo sản phẩm đang phát triển được.

---

## Guide này dùng khi nào

Mở guide này khi bạn vừa:

- fork template sang một repo sản phẩm mới
- clone repo về máy local lần đầu
- muốn biết nên chạm vào file nào trước, tránh lạc trong `docs/`

Không dùng guide này để thiết kế kiến trúc sâu hoặc thêm module lớn. Khi tới bước đó, nhảy sang [Module Extension Guide](./module-extension.md) và các tài liệu trong [`docs/architecture/`](../architecture/).

---

## Mental model 2 phút

Template này đang ở trạng thái:

- có sẵn scaffold Laravel + React + Inertia chạy được
- local dev mặc định bằng Sail
- có seam ban đầu cho kiến trúc `Kernel / Product`
- có demo accounts và smoke test để team vào việc nhanh

Quy tắc quan trọng nhất:

- **Code domain của sản phẩm đi vào `app/Product/*`**
- **Không sửa `app/Kernel/*` nếu chưa thật sự cần**
- **Mọi lệnh dự án chạy trong Docker; sau bootstrap thì dùng `./vendor/bin/sail ...`**

Nếu cần lý do kiến trúc đằng sau quy tắc này, đọc [§32 Tách Kernel / Product](../architecture/kernel-product.md).

---

## Pha 1 — Fork xong và boot local

### 1. Clone repo sản phẩm mới

```bash
git clone <repo-san-pham-moi> my-saas && cd my-saas
```

Nếu đây là một fork chính thức của template, nên giữ thêm remote trỏ về template gốc để sau này kéo cải tiến nền móng:

```bash
git remote add template-upstream <repo-template-goc>
git remote -v
```

### 2. Chuẩn bị env

```bash
cp .env.example .env
```

Ở vòng đầu, chỉ cần kiểm tra các giá trị này là đủ:

```dotenv
APP_NAME="SaaS Template"
APP_URL=http://localhost:8000
APP_PORT=8000
DB_CONNECTION=pgsql
REDIS_HOST=redis
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### 3. Cài dependencies

```bash
docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app composer:2 composer install
```

Frontend dependency cài qua Sail để không phụ thuộc host:

```bash
./vendor/bin/sail npm install
```

### 4. Boot stack local

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
```

### 5. Chạy smoke check

```bash
./vendor/bin/sail artisan test tests/Feature/TemplateSmokeTest.php
```

Nếu ổn, bạn có thể bắt đầu quy trình dev mặc định:

```bash
./vendor/bin/sail composer dev
```

### 6. Kiểm tra nhanh bằng mắt

- App: `http://localhost:8000`
- Vite: `http://localhost:5173`
- Mailpit: `http://localhost:8025`

Demo accounts mặc định:

- `owner@demo.test` / `password`
- `admin@demo.test` / `password`
- `member@demo.test` / `password`
- `viewer@demo.test` / `password`

Nếu tới đây app chạy được, fork của bạn đã đạt mức **Dev ready**.

---

## Pha 2 — Rebrand tối thiểu để thoát khỏi trạng thái template

Mục tiêu của pha này không phải hoàn thiện sản phẩm, mà là làm cho repo thôi mang cảm giác “copy của template”.

### 1. Đổi tên app và metadata

Chạm tối thiểu các chỗ sau:

- `composer.json`
  - đổi `name`
  - đổi `description` nếu cần
- `.env`
  - `APP_NAME`
  - `APP_URL`
- `config/product.php`
  - `brand.name`
  - `brand.tagline`
  - `brand.primary_color`
  - `brand.support_email`

### 2. Quyết định có giữ demo seed hay không

Mặc định template đang seed 4 tài khoản demo để tiện local dev.

- Nếu team vẫn cần dữ liệu mẫu để build nhanh: giữ `PRODUCT_DEMO_SEED_DATA=true`
- Nếu repo sản phẩm không muốn có demo accounts mặc định nữa: đặt `PRODUCT_DEMO_SEED_DATA=false`

```dotenv
PRODUCT_DEMO_SEED_DATA=false
```

Sau khi đổi cờ này, chạy lại:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

### 3. Thay các asset thương hiệu khi đã sẵn sàng

Khi có đủ brand assets, xem checklist sâu hơn ở [Reskin / Fork Guide](./reskin-fork.md). Ở vòng đầu, chỉ cần chốt:

- tên sản phẩm
- màu chủ đạo
- support email
- có hay không giữ demo seed data

Đừng cố làm toàn bộ Stripe, marketing, asset, onboarding trong cùng một nhịp nếu team chỉ mới cần bắt đầu code feature.

---

## Pha 3 — Bắt đầu feature đầu tiên

Khi local đã chạy và brand tối thiểu đã đổi xong, bước tiếp theo là **đưa domain thật của sản phẩm vào `app/Product/*`**.

### Bạn nên chạm vào đâu trước

- `app/Product/*`
  - model/service/controller/repository/event của domain
- `database/migrations/*`
  - bảng domain mới
- `routes/*`
  - route cho feature mới
- `config/product.php`
  - feature flags, plans, onboarding config
- `tests/Feature/*`
  - smoke/integration test cho feature đầu tiên

### Bạn chưa nên chạm vào đâu

- `app/Kernel/*`
  - trừ khi đang chủ động thay đổi hạ tầng dùng chung
- tài liệu kiến trúc sâu
  - nếu team chỉ đang cần build feature đầu tiên

### Luồng khuyên dùng cho feature đầu tiên

1. Chọn một domain thật của sản phẩm, ví dụ `Tasks`, `Projects`, `Invoices`
2. Tạo cấu trúc module dưới `app/Product/<Domain>/`
3. Viết migration + model + service + controller cho happy path nhỏ nhất
4. Viết test cho flow đó
5. Chạy quality gate:

```bash
./vendor/bin/sail composer check
```

6. Khi cần pattern chuẩn hơn cho module, đọc [Module Extension Guide](./module-extension.md)

### Dấu hiệu bạn đang đi đúng hướng

- feature mới chủ yếu nằm trong `app/Product/*`
- `config/product.php` bắt đầu phản ánh product thật
- `composer check` vẫn xanh
- bạn không phải sửa `Kernel` để ship feature domain đầu tiên

---

## Checklist “ngày đầu sau khi fork”

- [ ] Clone được repo sản phẩm mới
- [ ] Thêm `template-upstream` remote nếu cần giữ đường pull từ template
- [ ] Copy `.env.example` sang `.env`
- [ ] `docker run ... composer install` xong
- [ ] `./vendor/bin/sail up -d` chạy được
- [ ] `./vendor/bin/sail artisan migrate --seed` chạy được
- [ ] `./vendor/bin/sail artisan test tests/Feature/TemplateSmokeTest.php` xanh
- [ ] Đổi `APP_NAME`
- [ ] Cập nhật `config/product.php` với brand tối thiểu
- [ ] Quyết định có giữ `PRODUCT_DEMO_SEED_DATA` hay không
- [ ] Chọn feature/domain đầu tiên sẽ build trong `app/Product/*`

---

## Khi nào đọc tài liệu nào tiếp theo

- Muốn đổi brand, asset, pricing, Stripe, marketing rõ ràng hơn:
  - [Reskin / Fork Guide](./reskin-fork.md)
- Muốn thêm module/domain mới theo đúng boundary:
  - [Module Extension Guide](./module-extension.md)
- Muốn hiểu tại sao template tách `Kernel / Product`:
  - [§32 — Kernel / Product](../architecture/kernel-product.md)
- Muốn nắm tổng thể hệ thống trước khi đụng phần hạ tầng:
  - [Architecture Overview](../architecture/overview.md)

---

## Tóm tắt ngắn

Nếu chỉ nhớ 5 điều, hãy nhớ:

1. Chạy local trước, đừng rebrand mọi thứ ngay.
2. `config/product.php` là điểm tùy biến product đầu tiên.
3. `PRODUCT_DEMO_SEED_DATA` quyết định có giữ dữ liệu mẫu hay không.
4. Feature thật của sản phẩm đi vào `app/Product/*`.
5. Trước khi push, chạy `./vendor/bin/sail composer check`.
