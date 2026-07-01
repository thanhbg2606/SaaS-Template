> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Frontend Architecture
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 22. Frontend Architecture

> **Stack đã chốt: Inertia.js v2 + React + TypeScript** (Tailwind + shadcn/ui + Radix) — xem [ADR-0015](../../decisions/0015-frontend-inertia-react.md). Một deployable: Laravel giữ routing/middleware (§4.2, `Auth`, `Tenant`), React render UI, không SPA tách rời/không CORS. App chỉ là bề mặt **đã đăng nhập**; marketing/docs tách riêng trên CDN (§25). Khởi tạo từ Laravel 12 starter kit React. *(Đảo quyết định = ADR mới `Supersedes ADR-0015`.)*

### 22.1 Tách state nghiêm ngặt

Với Inertia, **server state chủ yếu đến qua Inertia props**; chỉ thêm TanStack Query cho phần async ngoài Inertia.

| Loại state | Công cụ | Quy tắc |
|---|---|---|
| **Server state** | TanStack Query | Mọi thứ từ API. Không nhân bản vào client store |
| **Client UI state** | Zustand / React state | Selection, modal, draft, presence |
| **URL state** | Router | Filter, tab, pagination — thứ share được |
| **Form state** | React Hook Form + Zod | Validation co-located với schema |

### 22.2 Design system

**Tailwind** (atomic) + **shadcn/ui** (copy-paste rồi sở hữu) + **Radix** (accessibility). Một file token (màu, spacing, radii). **Storybook** một story mỗi component (default + loading + error + empty + long-text).

### 22.3 UX chi tiết quan trọng

Keyboard shortcut (Cmd-K) · toast một provider · global confirm modal · format date qua một util · skeleton cho fetch > 200ms · optimistic update cho action user · error boundary cấp route.

### 22.4 Vị trí code (giữ ranh giới Kernel/Product)

```
resources/js/
  app.tsx, ssr.tsx           ← bootstrap Inertia
  layouts/                   ← app shell (sidebar, workspace switcher)
  components/ui/             ← shadcn/ui primitives (design system dùng chung)
  design-tokens.ts           ← màu/spacing/typography (đồng bộ tailwind.config.js — xem reskin-guide §5)
  Pages/                     ← trang KERNEL: auth, billing, settings
app/Product/<Module>/Pages/  ← trang DOMAIN (vd Tasks/Pages/Index.tsx)
```

Cấu hình Inertia **resolve page đa-root** — `resources/js/Pages/**` (Kernel) + `app/Product/**/Pages/**` (Product) — để ranh giới Kernel/Product áp cả ở frontend (§32). Domain UI dùng *chung* design system + permission helper, không fork shell.

