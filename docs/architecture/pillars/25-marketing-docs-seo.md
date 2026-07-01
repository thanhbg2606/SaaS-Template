> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Marketing Site, Docs & SEO
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 25. Marketing Site, Docs & SEO

### 25.1 Ba bề mặt tách biệt

| Bề mặt | Stack | URL |
|---|---|---|
| Marketing | Next.js / Astro | `yourtool.com` |
| Product docs | Mintlify / Docusaurus / Nextra | `yourtool.com/docs` |
| API reference | Stoplight / Redoc / Scribe | `yourtool.com/docs/api` |
| Status page | Instatus / self-host | `status.yourtool.com` |
| Changelog | Markdown trong repo + RSS | `yourtool.com/changelog` |

Đừng nhồi marketing + app + docs vào một app ngày đầu. Build riêng, deploy riêng, link với nhau. (Laravel: dùng **Scribe** để auto-gen API docs từ code.)

### 25.2 SEO cơ bản

Server-render marketing + docs · `<title>` + `<meta description>` per page · Open Graph + Twitter card + share image generator · `sitemap.xml` + `robots.txt` · JSON-LD schema · Lighthouse ≥ 95.

### 25.3 Conversion

Trang pricing rõ ràng + bảng so sánh + FAQ · public roadmap/changelog · logo khách/case study · form liên hệ tới người thật < 24h.
