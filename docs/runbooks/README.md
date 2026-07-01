> **SaaS Template Playbook** › [Mục lục](../README.md) › Vận hành

# Runbooks

Quy trình vận hành ngắn, **thực thi được**, sống trong repo (không phải Confluence). Nền tảng lý thuyết ở [§26 CI/CD & Release](../architecture/pillars/26-cicd-release.md) và [§21 Observability](../architecture/pillars/21-observability.md).

| Runbook | Khi nào dùng |
|---|---|
| [deploy.md](./deploy.md) | Mỗi lần release lên staging/production |
| [rollback.md](./rollback.md) | Release lỗi, cần quay về artifact trước |
| [incident-oncall.md](./incident-oncall.md) | Có alert / sự cố Sev‑1/Sev‑2 |

> Mỗi alert phải có **link tới runbook tương ứng** trong nội dung alert. Mỗi Sev‑1/2 phải có **post‑mortem blameless** lưu trong repo.
