<?php

declare(strict_types=1);

namespace WSystem;

/**
 * W System API client: EU VAT (VIES), EORI, company registers, sanctions, KYB and
 * EU e-invoicing (EN 16931, XRechnung, ZUGFeRD / Factur-X, Peppol). Needs only ext-curl and ext-json.
 *
 *   $ws = new \WSystem\WSystem();              // no key: free tier, 20 calls/day per IP
 *   $vat = $ws->verifyVat('BE0403170701');     // ['status' => 'valid', 'valid' => true, 'name' => ...]
 *
 * Every API operation is a method named after its OpenAPI operationId (see Operations.php; all 57).
 * Every method takes a trailing $options array: headers, timeout, maxRetries, requestId (X-Request-Id),
 * idempotencyKey (Idempotency-Key).
 */
class WSystem extends Operations
{
    public const VERSION = '0.3.0';
    public const DEFAULT_BASE_URL = 'https://api.wsystem.dev';

    private ?string $apiKey;
    private string $baseUrl;
    private int $timeout;
    private int $maxRetries;
    /** @var array<string,string> */
    private array $defaultHeaders;
    /** @var null|callable(string,string,array<string,string>,?string,int):array */
    private $transport;
    /** @var callable(float):void */
    private $sleeper;

    /**
     * @param array{apiKey?:?string, baseUrl?:string, timeout?:int, maxRetries?:int, headers?:array<string,string>, transport?:callable, sleeper?:callable} $options
     *   transport: optional fn(string $method, string $url, array $headers, ?string $body, int $timeout): [int $status, string $body, array $responseHeaders = []]
     *   (used for tests or a custom HTTP stack).
     */
    public function __construct(array $options = [])
    {
        $key = $options['apiKey'] ?? (getenv('WSYSTEM_API_KEY') ?: null);
        $this->apiKey = $key === '' ? null : $key;
        $this->baseUrl = rtrim($options['baseUrl'] ?? (getenv('WSYSTEM_BASE_URL') ?: self::DEFAULT_BASE_URL), '/');
        $this->timeout = (int) ($options['timeout'] ?? 30);
        $this->maxRetries = (int) ($options['maxRetries'] ?? 2);
        $this->defaultHeaders = $options['headers'] ?? [];
        $this->transport = $options['transport'] ?? null;
        $this->sleeper = $options['sleeper'] ?? static function (float $s): void {
            usleep((int) ($s * 1_000_000));
        };
    }

    /**
     * Call any endpoint. Returns the decoded JSON (array) or raw text.
     *
     * @param array<string,mixed> $query
     * @param mixed $body array -> JSON; string -> XML (or PDF when it starts with %PDF)
     * @param array{headers?:array<string,string>, timeout?:int, maxRetries?:int, requestId?:string, idempotencyKey?:string} $options
     * @throws WSystemException on HTTP >= 400 (typed subclasses) and transport errors
     */
    public function request(string $method, string $path, array $query = [], mixed $body = null, array $options = []): mixed
    {
        $query = array_filter($query, static fn ($v) => $v !== null);
        foreach ($query as $k => $v) {
            if (is_bool($v)) {
                $query[$k] = $v ? 'true' : 'false';
            }
        }
        $url = $this->baseUrl . $path . ($query ? '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '');
        $headers = ['Accept' => 'application/json', 'User-Agent' => 'wsystem-php/' . self::VERSION] + $this->defaultHeaders;
        if ($this->apiKey !== null) {
            $headers['X-API-Key'] = $this->apiKey;
        }
        $payload = null;
        if ($body !== null) {
            if (is_string($body)) {
                $payload = $body;
                $headers['Content-Type'] = str_starts_with($body, '%PDF') ? 'application/pdf' : 'application/xml';
            } else {
                $payload = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $headers['Content-Type'] = 'application/json';
            }
        }
        if (!empty($options['requestId'])) {
            $headers['X-Request-Id'] = (string) $options['requestId'];
        }
        if (!empty($options['idempotencyKey'])) {
            $headers['Idempotency-Key'] = (string) $options['idempotencyKey'];
        }
        $headers = ($options['headers'] ?? []) + $headers;
        $timeout = (int) ($options['timeout'] ?? $this->timeout);
        $retries = (int) ($options['maxRetries'] ?? $this->maxRetries);
        // POST is only retried with an Idempotency-Key, otherwise a retry could repeat the work
        $canRetry = in_array($method, ['GET', 'HEAD', 'PUT', 'DELETE', 'OPTIONS'], true)
            || count(array_filter(array_keys($headers), static fn ($k) => strtolower((string) $k) === 'idempotency-key')) > 0;

        for ($attempt = 0;; $attempt++) {
            try {
                $res = $this->transport
                    ? ($this->transport)($method, $url, $headers, $payload, $timeout)
                    : $this->curl($method, $url, $headers, $payload, $timeout);
            } catch (ConnectionException $e) {
                if ($canRetry && $attempt < $retries) {
                    ($this->sleeper)($this->backoff($attempt, null));
                    continue;
                }
                throw $e;
            }
            $status = (int) $res[0];
            $text = (string) $res[1];
            $rh = array_change_key_case((array) ($res[2] ?? []), CASE_LOWER);
            if (in_array($status, [429, 502, 503, 504], true) && $canRetry && $attempt < $retries) {
                $ra = isset($rh['retry-after']) && is_numeric($rh['retry-after']) ? (float) $rh['retry-after'] : null;
                ($this->sleeper)($this->backoff($attempt, $ra));
                continue;
            }
            break;
        }

        $data = $text;
        if ($text !== '') {
            try {
                $data = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $data = $text;
            }
        } else {
            $data = null;
        }

        if ($status >= 400) {
            if ($status === 402) {
                $msg = 'Free tier used up (HTTP 402). Get a free key (500 units/month) at https://api.wsystem.dev/account/free-key or a plan at https://wsystem.dev/pricing';
            } else {
                $detail = is_array($data) ? ($data['detail'] ?? $data['message'] ?? $data['error'] ?? $data) : $text;
                $msg = 'W System API HTTP ' . $status . ': ' . (is_string($detail) ? $detail : json_encode($detail));
            }
            throw WSystemException::forStatus($status, $msg, $data, $rh);
        }

        return $data;
    }

    private function backoff(int $attempt, ?float $retryAfter): float
    {
        if ($retryAfter !== null) {
            return min(max($retryAfter, 0.0), 30.0);
        }

        return min(0.5 * (2 ** $attempt), 8.0) * (0.75 + mt_rand() / mt_getrandmax() / 2);
    }

    /** @return array{0:int,1:string,2:array<string,string>} */
    private function curl(string $method, string $url, array $headers, ?string $payload, int $timeout): array
    {
        $ch = curl_init($url);
        $h = [];
        foreach ($headers as $k => $v) {
            $h[] = $k . ': ' . $v;
        }
        $rh = [];
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $h,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HEADERFUNCTION => static function ($c, string $line) use (&$rh): int {
                if (str_contains($line, ':')) {
                    [$k, $v] = explode(':', $line, 2);
                    $rh[strtolower(trim($k))] = trim($v);
                }

                return strlen($line);
            },
        ]);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        $text = curl_exec($ch);
        if ($text === false) {
            $errno = curl_errno($ch);
            $err = curl_error($ch);
            curl_close($ch);
            $msg = 'W System API request failed: ' . $err;
            throw $errno === CURLE_OPERATION_TIMEDOUT ? new TimeoutException($msg) : new ConnectionException($msg);
        }
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return [$status, (string) $text, $rh];
    }

    // --- 0.2 convenience signatures (kept; every operation is also available under its operationId) ---

    /** @param array{requester?:string, nocache?:bool, fallback?:bool} $opts */
    public function verifyVat(string $vat, array $opts = [], array $options = []): array
    {
        return $this->request('GET', '/verify', ['vat' => $vat] + $opts, null, $options);
    }

    /** @param string[] $vats max 100 */
    public function verifyVatBatch(array $vats, array $opts = [], array $options = []): array
    {
        return $this->request('POST', '/verify/batch', [], ['vats' => array_values($vats)] + $opts, $options);
    }

    public function viesStatus(array $options = []): array
    {
        return $this->request('GET', '/vies/status', [], null, $options);
    }

    public function verifyEori(string $eori, array $options = []): array
    {
        return $this->request('GET', '/eori', ['eori' => $eori], null, $options);
    }

    /** @param string[] $eoris */
    public function verifyEoriBatch(array $eoris, array $options = []): array
    {
        return $this->request('POST', '/eori/batch', [], ['eoris' => array_values($eoris)], $options);
    }

    public function companySearch(string $q, array $opts = [], array $options = []): array
    {
        return $this->lookupCompany($q, $opts, $options);
    }

    public function sanctionsScreen(string $name, array $opts = [], array $options = []): array
    {
        return $this->screenSanctions($name, $opts, $options);
    }

    /** @param array<string,mixed>|string $company */
    public function kybCheck(array|string $company, array $opts = [], array $options = []): array
    {
        return $this->request('POST', '/kyb/check', [], ['company' => $company] + $opts, $options);
    }

    /** "Can I e-invoice this company?": VAT + Peppol directory + country mandate. */
    public function einvoiceRoute(string $id, array $opts = [], array $options = []): array
    {
        return $this->request('GET', '/einvoice/route', ['id' => $id] + $opts, null, $options);
    }

    /**
     * Validate a UBL, CII, XRechnung or ZUGFeRD/Factur-X invoice against EN 16931.
     * Pass a PDF (ZUGFeRD/Factur-X) with ['base64' => true] and base64_encode()d content.
     *
     * @param array{rules?:string, base64?:bool} $opts
     */
    public function einvoiceValidate(string $xml, array $opts = [], array $options = []): array
    {
        $body = !empty($opts['base64']) ? ['base64' => $xml] : ['xml' => $xml];

        return $this->request('POST', '/einvoice/validate', ['rules' => $opts['rules'] ?? null], $body, $options);
    }

    /**
     * Read an incoming UBL, CII, XRechnung or ZUGFeRD/Factur-X invoice into one JSON shape. Does not validate.
     * Pass a PDF with ['base64' => true] and base64_encode()d content.
     *
     * @param array{base64?:bool} $opts
     */
    public function einvoiceParse(string $xml, array $opts = [], array $options = []): array
    {
        $body = !empty($opts['base64']) ? ['base64' => $xml] : ['xml' => $xml];

        return $this->request('POST', '/einvoice/parse', [], $body, $options);
    }

    /** @param array<string,mixed> $invoice (format, type_code 381 + preceding_invoice for credit notes, ...) */
    public function einvoiceCreate(array $invoice, array $options = []): array
    {
        return $this->request('POST', '/einvoice/create', [], $invoice, $options);
    }

    /** Create a ZUGFeRD / Factur-X PDF (format "zugferd") and return the PDF bytes. @param array<string,mixed> $invoice */
    public function einvoiceCreatePdf(array $invoice, array $options = []): string
    {
        return self::decodePdf($this->einvoiceCreate(['format' => 'zugferd'] + $invoice, $options));
    }

    /** PDF bytes from a POST /einvoice/create answer made with format zugferd / zugferd-xrechnung. @param array<string,mixed> $result */
    public static function decodePdf(array $result): string
    {
        $b64 = $result['pdf_base64'] ?? null;
        if (!is_string($b64) || $b64 === '') {
            throw new \InvalidArgumentException("answer has no pdf_base64 (use format 'zugferd')");
        }

        return (string) base64_decode($b64, true);
    }

    /** @param array{type?:string} $params type: invoice | zugferd | credit_note | small_business */
    public function einvoiceSample(array $params = [], array $options = []): array
    {
        return $this->request('GET', '/einvoice/sample', $params, null, $options);
    }

    public function usage(array $options = []): array
    {
        return $this->billingUsage([], $options);
    }

    public function plans(array $options = []): array
    {
        return $this->billingPlans([], $options);
    }
}
