# Changelog

## 0.3.0

- All 57 API operations, one method per OpenAPI `operationId` (generated `Operations` base class: platform, mandates, inbox, shop, KSeF, billing, e-invoice send/preflight/extract/check/sample, ...). The 0.2 methods keep working.
- `einvoiceCreatePdf()` (ZUGFeRD / Factur-X PDF) and `WSystem::decodePdf()`; credit notes via `type_code` `381` with `preceding_invoice` and `preceding_invoice_date`; `einvoiceSample(['type' => 'credit_note'])`.
- Trailing `$options` on every call: `requestId` (X-Request-Id), `idempotencyKey` (Idempotency-Key), `headers`, `timeout`, `maxRetries`. Constructor: `maxRetries` (default 2), `headers`. Retries with backoff and Retry-After on connection errors, 429, 502, 503, 504 (GET/PUT/DELETE; POST only with an idempotency key).
- Typed exceptions below `WSystemException` (`status`, `body`, `errorCode()`, `docsUrl`, `requestId`): `BadRequestException`, `AuthenticationException`, `PaymentRequiredException`, `NotFoundException`, `ConflictException`, `RateLimitException`, `ServerException`, `ConnectionException`, `TimeoutException`.
- Raw XML / PDF string bodies. Offline tests cover all 57 operations.

## 0.2.0 (2026-10-28)

- New `einvoiceParse()`: reads an incoming UBL, CII, XRechnung or ZUGFeRD/Factur-X invoice (XML, or PDF with `['base64' => true]`) into JSON: parties, dates, references, totals, VAT breakdown, payment/IBAN and lines. Values come back as written; it does not validate.

## 0.1.0 (2026-10-27)

- First release: PHP client for all W System API endpoints, Laravel `EuVat` rule and `eu_vat` string rule with outage-tolerant default and `strict()` mode.
