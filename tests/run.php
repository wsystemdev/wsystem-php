<?php

// Offline tests: no network, no Composer needed. Run: php tests/run.php
declare(strict_types=1);

if (!interface_exists('Illuminate\Contracts\Validation\ValidationRule')) {
    eval('namespace Illuminate\Contracts\Validation; interface ValidationRule { public function validate(string $attribute, mixed $value, \Closure $fail): void; }');
}
spl_autoload_register(static function (string $c): void {
    if (str_starts_with($c, 'WSystem\\')) {
        $f = __DIR__ . '/../src/' . str_replace('\\', '/', substr($c, 8)) . '.php';
        if (is_file($f)) {
            require $f;
        }
    }
});

use WSystem\Laravel\EuVat;
use WSystem\WSystem;
use WSystem\WSystemException;

$fails = 0;
$n = 0;
function check(string $name, bool $ok): void
{
    global $fails, $n;
    $n++;
    echo ($ok ? 'ok   ' : 'FAIL ') . $name . PHP_EOL;
    if (!$ok) {
        $fails++;
    }
}

/** fake transport recording the last call */
function fake(int $status, string $body, ?array &$last = null): callable
{
    return static function (string $m, string $u, array $h, ?string $b, int $t) use ($status, $body, &$last): array {
        $last = compact('m', 'u', 'h', 'b', 't');

        return [$status, $body];
    };
}

putenv('WSYSTEM_API_KEY');

// 1. GET with query + no key header on free tier
$last = null;
$ws = new WSystem(['transport' => fake(200, '{"status":"valid","valid":true,"name":"SA ELECTRABEL"}', $last)]);
$r = $ws->verifyVat('BE0403170701', ['nocache' => true, 'requester' => null]);
check('verifyVat decodes JSON', $r['valid'] === true && $r['name'] === 'SA ELECTRABEL');
check('verifyVat URL', $last['m'] === 'GET' && $last['u'] === 'https://api.wsystem.dev/verify?vat=BE0403170701&nocache=true');
check('no X-API-Key without key', !isset($last['h']['X-API-Key']));
check('User-Agent set', str_starts_with($last['h']['User-Agent'], 'wsystem-php/'));

// 2. key from options and from env
$ws = new WSystem(['apiKey' => 'k_test', 'transport' => fake(200, '{}', $last)]);
$ws->usage();
check('X-API-Key from option', ($last['h']['X-API-Key'] ?? '') === 'k_test' && str_ends_with($last['u'], '/billing/usage'));
putenv('WSYSTEM_API_KEY=k_env');
$ws = new WSystem(['transport' => fake(200, '{}', $last)]);
$ws->plans();
check('X-API-Key from env', ($last['h']['X-API-Key'] ?? '') === 'k_env');
putenv('WSYSTEM_API_KEY');

// 3. POST bodies
$ws = new WSystem(['baseUrl' => 'http://localhost:8000/', 'transport' => fake(200, '{"valid":false}', $last)]);
$ws->einvoiceValidate('<Invoice/>', ['rules' => 'xrechnung']);
check('einvoiceValidate POST body + rules query', $last['m'] === 'POST' && $last['u'] === 'http://localhost:8000/einvoice/validate?rules=xrechnung'
    && json_decode($last['b'], true) === ['xml' => '<Invoice/>'] && $last['h']['Content-Type'] === 'application/json');
$ws->einvoiceValidate('JVBERi0=', ['base64' => true]);
check('einvoiceValidate base64 body, no rules', json_decode($last['b'], true) === ['base64' => 'JVBERi0='] && !str_contains($last['u'], '?'));
$ws->verifyVatBatch(['a' => 'DE811569869', 'b' => 'FR40303265045']);
check('verifyVatBatch list body', json_decode($last['b'], true) === ['vats' => ['DE811569869', 'FR40303265045']]);
$ws->einvoiceRoute('0208:0403170701');
check('einvoiceRoute encodes id', str_ends_with($last['u'], '/einvoice/route?id=0208%3A0403170701'));

// 4. errors
try {
    (new WSystem(['transport' => fake(402, '{"error":"payment required"}')]))->verifyEori('DE123');
    check('402 throws', false);
} catch (WSystemException $e) {
    check('402 throws with free-key hint', $e->status === 402 && str_contains($e->getMessage(), 'free-key'));
}
try {
    (new WSystem(['transport' => fake(422, '{"detail":"vat missing"}')]))->verifyVat('');
    check('422 throws', false);
} catch (WSystemException $e) {
    check('422 message uses detail', $e->status === 422 && str_contains($e->getMessage(), 'vat missing') && $e->body['detail'] === 'vat missing');
}
$r = (new WSystem(['transport' => fake(200, 'plain text')]))->request('GET', '/x');
check('non-JSON body returned as text', $r === 'plain text');

// 5. Laravel rule
function rule(int $status, string $body): EuVat
{
    return new EuVat(new WSystem(['transport' => fake($status, $body)]));
}
function runRule(EuVat $rule, mixed $value): ?string
{
    $msg = null;
    $rule->validate('vat_id', $value, static function (string $m) use (&$msg): void {
        $msg = $m;
    });

    return $msg;
}
check('rule: valid passes', runRule(rule(200, '{"status":"valid","valid":true}'), 'BE0403170701') === null);
check('rule: invalid fails', str_contains((string) runRule(rule(200, '{"status":"invalid","valid":false}'), 'BE0000000000'), 'not a valid'));
check('rule: invalid_format fails', runRule(rule(200, '{"status":"invalid_format","valid":false}'), 'XX1') !== null);
check('rule: empty fails without a call', runRule(rule(500, ''), '  ') !== null);
check('rule: non-string fails', runRule(rule(200, '{"valid":true}'), 123) !== null);
check('rule: VIES outage passes by default', runRule(rule(200, '{"status":"member_state_unavailable","valid":null}'), 'DE811569869') === null);
check('rule: VIES outage fails in strict', str_contains((string) runRule(rule(200, '{"status":"member_state_unavailable","valid":null}')->strict(), 'DE811569869'), 'could not be checked'));
check('rule: API error passes by default', runRule(rule(402, '{}'), 'DE811569869') === null);
check('rule: API error fails in strict', runRule(rule(402, '{}')->strict(), 'DE811569869') !== null);
$r = rule(200, '{"status":"valid","valid":true,"name":"X"}');
runRule($r, ' BE0403170701 ');
check('rule: result() exposes answer', ($r->result()['name'] ?? null) === 'X');

echo PHP_EOL . ($n - $fails) . "/$n passed" . PHP_EOL;
exit($fails ? 1 : 0);
