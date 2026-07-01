> [Decisions](./README.md) › ADR-0015
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0015 — Frontend: Inertia.js + React (TypeScript)

**Status:** Accepted

**Context:** §22 (Frontend) trước đây để mở giữa ba hướng: SPA React tách rời (Sanctum API), **Inertia.js**, và **Livewire**. Để build được Phase 1–5 (skeleton, auth UI, workspace switcher, trang pricing/checkout) cần chốt một stack. Ràng buộc: template là **modular monolith một deployable** (ADR-0003), ưu tiên DX "một lệnh để dev" (§27), và §22.2 đã chuẩn hóa design system quanh **React + Tailwind + shadcn/ui + Radix**. Marketing/docs là bề mặt tách riêng trên CDN (§25) nên app chỉ phục vụ phần đã đăng nhập (SEO/SSR không phải ràng buộc của app).

**Decision:** Dùng **Inertia.js v2 + React + TypeScript** cho bề mặt app, với **Tailwind + shadcn/ui + Radix** làm design system.

- **Routing/middleware** do Laravel giữ (chuỗi §4.2, `Auth`, `Tenant`) — không dựng lại auth/tenancy trong SPA tách rời, không CORS, một build/một deploy.
- **Route type-safe** qua **Laravel Wayfinder** (§27.5).
- **State** theo §22.1: server state chủ yếu qua **Inertia props** (thêm **TanStack Query** chỉ cho phần async ngoài Inertia); client UI state **Zustand**; form **React Hook Form + Zod**.
- Khởi tạo từ **Laravel 12 starter kit (React/Inertia)** — có sẵn auth scaffolding (Fortify) + Tailwind + shadcn.
- **Vị trí code** giữ ranh giới Kernel/Product (xem §22.4 mới): trang Kernel ở `resources/js/Pages`, trang Product ở `app/Product/<Module>/Pages` (cấu hình Inertia resolve đa-root).
- **Loại trừ:** SPA React tách rời (thêm CORS + 2 deploy, thừa cho monolith) và **Livewire** (lệch khỏi giả định React của §22).

**Consequences:**
- (+) Một deployable, không CORS; tận dụng nguyên routing/middleware/auth/tenancy của Laravel.
- (+) Đúng design system §22.2 (React + shadcn) → adopt §22 không ma sát; starter kit rút ngắn Phase 1–2.
- (+) Ranh giới Kernel/Product áp được cả ở frontend (Inertia multi-root) — nhất quán §32.
- (−) Cần Node/Vite trong pipeline build; SSR (nếu muốn) phải cấu hình `ssr.tsx` riêng.
- (−) Team cần biết React/TypeScript. Nếu sau này có bề mặt thuần admin/CRUD muốn ít JS, có thể dùng Filament (đã là ADR-0013) thay vì kéo Livewire vào — tránh hai mô hình frontend song song.
- (−) Đảo quyết định (vd sang Livewire) sẽ là một ADR mới `Supersedes ADR-0015` và viết lại §22.
