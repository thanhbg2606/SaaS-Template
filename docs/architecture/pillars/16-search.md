> **SaaS Template Playbook** › [Mục lục](../../README.md) › Kiến trúc › Trụ cột › Search (Full-Text + Semantic)
> Tham chiếu §-number giữ nguyên làm ID bền — bảng tra: [section-map](../../section-map.md).

# 16. Search (Full-Text + Semantic)

### 16.1 Bắt đầu với Postgres

```sql
CREATE INDEX idx_issue_search ON issues
    USING GIN (to_tsvector('english', title || ' ' || coalesce(content, '')));
-- pg_trgm cho typo tolerance:
CREATE INDEX idx_issue_title_trgm ON issues USING GIN (title gin_trgm_ops);
```

Carry tới ~10M dòng dễ dàng.

### 16.2 Laravel Scout khi cần search engine

```bash
composer require laravel/scout
```

Driver: **database** (đơn giản) → **Meilisearch** / **Typesense** (fuzzy + relevance tuning, DX tốt) → Elasticsearch (scale + analytics). Chỉ cần `use Searchable;` trên model.

### 16.3 Vector / semantic

```sql
CREATE EXTENSION vector;
ALTER TABLE documents ADD COLUMN embedding vector(1536);
CREATE INDEX ON documents USING hnsw (embedding vector_cosine_ops);
```

Sinh embedding qua OpenAI/model local **trong worker** sau khi content đổi (không phải request path). Có package `pgvector/pgvector-php` + Scout driver vector.

### 16.4 Hybrid search

Kết hợp BM25 (keyword) + vector (semantic) bằng reciprocal rank fusion:
`score(doc) = 1/(k + rank_bm25) + 1/(k + rank_vector)`. Đánh bại từng cái riêng lẻ cho product search.
