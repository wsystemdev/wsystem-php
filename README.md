# wsystem-php

PHP client and Laravel validation rule for the [W System API](https://wsystem.dev/?ref=wsystem-php-github): EU VAT (VIES) validation, EORI checks, company lookup, sanctions screening, KYB, Peppol routing and EN 16931 e-invoice validation (XRechnung, ZUGFeRD, Peppol BIS).

> Disclosure: this package is made by W System, the company that runs the API. It works without a key on the free tier (20 calls/day per IP); a [free key](https://api.wsystem.dev/account/free-key?ref=wsystem-php-github) gives 500 units/month.

Zero dependencies beyond `ext-curl` and `ext-json`, PHP 8.1+.

```bash
composer require wsystem/wsystem-php
```

## Validate an EU VAT number

```php
use WSystem\WSystem;

$ws = new WSystem(); // reads WSYSTEM_API_KEY if set
$r = $ws->verifyVat('BE0403170701');

$r['status']; // "valid" | "invalid" | "invalid_format" | "member_state_unavailable" | "unavailable"
$r['valid'];  // true | false | null (null = VIES could not answer, not "invalid")
$r['name'];   // "SA ELECTRABEL"
```

VIES records who asked. Unless you pass your own VAT number as `requester`, the consultation is made with W System's VAT number:

```php
$ws->verifyVat('DE811569869', ['requester' => 'NL123456789B01']);
```

## Laravel

The service provider is auto-discovered. Set `WSYSTEM_API_KEY` in `.env` (optional), then:

```php
use WSystem\Laravel\EuVat;

$request->validate([
    'vat_id' => ['required', new EuVat()],
]);

// or as a string rule
$request->validate(['vat_id' => 'required|eu_vat']);
```

When VIES is down for a member state (it happens daily, see [VIES status](https://api.wsystem.dev/vies/status?ref=wsystem-php-github)) the rule **passes** by default, because an outage doesn't mean the number is invalid and you probably don't want to block a sign-up or checkout. To fail instead:

```php
(new EuVat())->strict()   // or 'eu_vat:strict'
```

Optional config in `config/services.php`:

```php
'wsystem' => ['key' => env('WSYSTEM_API_KEY'), 'timeout' => 30],
```

## Everything else

```php
$ws->verifyVatBatch(['DE811569869', 'FR40303265045']);
$ws->viesStatus();
$ws->verifyEori('DE123456789012345');
$ws->companySearch('Electrabel', ['country' => 'BE']);
$ws->sanctionsScreen('Some Name');
$ws->kybCheck('BE0403170701');
$ws->einvoiceRoute('0208:0403170701');                 // Peppol participant lookup
$ws->einvoiceValidate($xml, ['rules' => 'xrechnung']); // EN 16931 / XRechnung / Peppol BIS
$ws->einvoiceValidate(base64_encode($pdf), ['base64' => true]); // ZUGFeRD / Factur-X PDF
$ws->einvoiceCreate($ws->einvoiceSample());            // build UBL from JSON
$ws->usage();
$ws->plans();
```

Errors throw `WSystem\WSystemException` with `->status` and `->body`. HTTP 402 means the free tier is used up for today; get a [free key](https://api.wsystem.dev/account/free-key?ref=wsystem-php-github).

## Tests

```bash
php tests/run.php         # offline
php tests/live_smoke.php  # a few live free-tier calls
```

API docs: [api.wsystem.dev/docs](https://api.wsystem.dev/docs?ref=wsystem-php-github). Questions: hello@wsystem.dev. MIT licence.
