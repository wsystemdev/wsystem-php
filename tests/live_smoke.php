<?php
// Live smoke test against https://api.wsystem.dev. Run: WSYSTEM_API_KEY=... php tests/live_smoke.php (key is never printed)
declare(strict_types=1);
foreach (['WSystemException', 'BadRequestException', 'AuthenticationException', 'PaymentRequiredException', 'NotFoundException', 'ConflictException', 'RateLimitException', 'ServerException', 'ConnectionException', 'TimeoutException', 'Operations', 'WSystem'] as $f) {
    require_once __DIR__ . "/../src/$f.php";
}
$ws = new WSystem\WSystem();
$r = $ws->verifyVat('BE0403170701');
echo 'verifyVat: ', $r['status'], ' ', $r['name'] ?? '', PHP_EOL;
$sample = $ws->einvoiceSample(['type' => 'credit_note']);
$res = $ws->einvoiceCreate(['format' => 'zugferd'] + $sample);
echo 'zugferd credit note: valid=', json_encode($res['valid'] ?? null), ' pdf=', isset($res['pdf_base64']) ? 'yes' : 'no', PHP_EOL;
exit(($r['valid'] ?? null) === true && ($res['valid'] ?? null) === true && !empty($res['pdf_base64']) ? 0 : 1);
