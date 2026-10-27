<?php
// Live smoke test against https://api.wsystem.dev (uses the free tier, a few calls). Run: php tests/live_smoke.php
declare(strict_types=1);
require __DIR__ . '/../src/WSystemException.php';
require __DIR__ . '/../src/WSystem.php';
$ws = new WSystem\WSystem();
$r = $ws->verifyVat('BE0403170701');
echo 'verifyVat: ', $r['status'], ' ', $r['name'] ?? '', PHP_EOL;
$s = $ws->viesStatus();
echo 'viesStatus: ', is_array($s) ? 'ok' : 'unexpected', PHP_EOL;
$x = $ws->einvoiceSample();
echo 'einvoiceSample: ', is_string($x) ? strlen($x) . ' bytes' : json_encode(array_keys((array) $x)), PHP_EOL;
exit(($r['valid'] ?? null) === true ? 0 : 1);
