> [Decisions](./README.md) › ADR-0011
> Tham chiếu §-number giữ nguyên — bảng tra: [section-map](../section-map.md).

# ADR-0011 — Search: Postgres FTS + Scout; pgvector cho semantic

**Status:** Accepted

**Context:** Cần search ngay từ đầu nhưng chưa muốn dựng Elasticsearch.

**Decision:** Khởi đầu **Postgres FTS** (`tsvector` GIN + `pg_trgm` cho typo) — đủ tới ~10M dòng. Khi cần relevance tuning/fuzzy mạnh → **Laravel Scout** driver **Meilisearch/Typesense**. Semantic search dùng **pgvector** (embedding sinh trong worker, không phải request path). Hybrid = BM25 + vector qua reciprocal rank fusion.

**Consequences:**
- (+) Không thêm hạ tầng giai đoạn đầu; nâng cấp mượt qua Scout.
- (−) FTS Postgres kém linh hoạt hơn search engine chuyên dụng ở relevance.
