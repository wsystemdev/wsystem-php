# Changelog

## 0.2.0 (2026-10-28)

- New `einvoiceParse()`: reads an incoming UBL, CII, XRechnung or ZUGFeRD/Factur-X invoice (XML, or PDF with `['base64' => true]`) into JSON: parties, dates, references, totals, VAT breakdown, payment/IBAN and lines. Values come back as written; it does not validate.

## 0.1.0 (2026-10-27)

- First release: PHP client for all W System API endpoints, Laravel `EuVat` rule and `eu_vat` string rule with outage-tolerant default and `strict()` mode.
