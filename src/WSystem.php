<?php

declare(strict_types=1);

namespace WSystem;

/**
 * W System API client: EU VAT (VIES), EORI, company registers, sanctions,
 * KYB and EU e-invoicing (EN 16931 / Peppol). Needs only ext-curl and ext-json.
 *
 *   $ws = new \WSystem\WSystem();              // no key: free tier, 20 calls/day per IP
 *   $vat = $ws->verifyVat('BE0403170701');     // ['status' => 'valid', 'valid' => true, 'name' => ...]
 */
class WSystem
{
    public const VERSION = '0.1.0';
    public const DEFAULT_BASE_URL = 'https://api.wsystem.dev';

    private ?string $apiKey;
    private string $baseUrl;
    private int $timeout;
    /** @var null|callable(string,string,array<string,string>,?string,int):array{0:int,1:string} */
    private $transport;

    /**
     * @param array{apiKey?:?string, baseUrl?:string, timeout?:int, transport?:callable} $options
     *   transport: optional fn(string $method, string $url, array $headers, ?string $body, int $timeout): [int $status, string $body]
     *   (used for tests or a custom HTTP stack).
     */
    public function __construct(array $options = [])
    {
        $key = $options['apiKey'] ?? (getenv('WSYSTEM_API_KEY') ?: null);
        $this->apiKey = $key === '' ? null : $key;
        $this->baseUrl = rtrim($options['baseUrl'] ?? self::DEFAULT_BASE_URL, '/');
        $this->timeout = (int) ($options['timeout'] ?? 30);
        $this->transport = $options['transport'] ?? null;
    }

    /**
     * Call any endpoint. Returns the decoded JSON (array) or raw text.
     *
     * @param array<string,mixed> $query
     * @throws WSystemException on HTTP >= 400 or transport errors
     */
    public function request(string $method, string $path, array $query = [], mixed $body = null): mixed
    {
        $query = array_filter($query, static fn ($v) => $v !== null);
        foreach ($query as $k => $v) {
            if (is_bool($v)) {
                $query[$k] = $v ? 'true' : 'false';
            }
        }
        $url = $this->baseUrl . $path . ($query ? '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '');
        $headers = ['Accept' => 'application/json', 'User-Agent' => 'wsystem-php/' . self::VERSION];
        if ($this->apiKey !== null) {
            $headers['X-API-Key'] = $this->apiKey;
        }
        $payload = null;
        if ($body !== null) {
            $headers['Content-Type'] = 'application/json';
            $payload = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        [$status, $text] = $this->transport
            ? ($this->transport)($method, $url, $headers, $payload, $this->timeout)
            : $this->curl($method, $url, $headers, $payload);

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
                $detail = is_array($data) ? ($data['detail'] ?? $data['error'] ?? $data) : $text;
                $msg = 'W System API HTTP ' . $status . ': ' . (is_string($detail) ? $detail : json_encode($detail));
            }
            throw new WSystemException($msg, $status, $data);
        }

        return $data;
    }

    /** @return array{0:int,1:string} */
    private function curl(string $method, string $url, array $headers, ?string $payload): array
    {
        $ch = curl_init($url);
        $h = [];
        foreach ($headers as $k => $v) {
            $h[] = $k . ': ' . $v;
        }
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $h,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        $text = curl_exec($ch);
        if ($text === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new WSystemException('W System API request failed: ' . $err);
        }
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return [$status, (string) $text];
    }

    // VAT (VIES)

    /** @param array{requester?:string, nocache?:bool, fallback?:bool} $opts */
    public function verifyVat(string $vat, array $opts = []): array
    {
        return $this->request('GET', '/verify', ['vat' => $vat] + $opts);
    }

    /** @param string[] $vats max 100 */
    public function verifyVatBatch(array $vats, array $opts = []): array
    {
        return $this->request('POST', '/verify/batch', [], ['vats' => array_values($vats)] + $opts);
    }

    public function viesStatus(): array
    {
        return $this->request('GET', '/vies/status');
    }

    // EORI

    public function verifyEori(string $eori): array
    {
        return $this->request('GET', '/eori', ['eori' => $eori]);
    }

    /** @param string[] $eoris */
    public function verifyEoriBatch(array $eoris): array
    {
        return $this->request('POST', '/eori/batch', [], ['eoris' => array_values($eoris)]);
    }

    // Company registers, sanctions, KYB

    public function companySearch(string $q, array $opts = []): array
    {
        return $this->request('GET', '/company', ['q' => $q] + $opts);
    }

    public function sanctionsScreen(string $name, array $opts = []): array
    {
        return $this->request('GET', '/sanctions/screen', ['name' => $name] + $opts);
    }

    /** @param array<string,mixed>|string $company */
    public function kybCheck(array|string $company, array $opts = []): array
    {
        return $this->request('POST', '/kyb/check', [], ['company' => $company] + $opts);
    }

    // E-invoicing

    /** "Can I e-invoice this company?": VAT + Peppol directory + country mandate. */
    public function einvoiceRoute(string $id, array $opts = []): array
    {
        return $this->request('GET', '/einvoice/route', ['id' => $id] + $opts);
    }

    /**
     * Validate a UBL, CII, XRechnung or ZUGFeRD/Factur-X invoice against EN 16931.
     * Pass a PDF (ZUGFeRD/Factur-X) with ['base64' => true] and base64_encode()d content.
     *
     * @param array{rules?:string, base64?:bool} $opts
     */
    public function einvoiceValidate(string $xml, array $opts = []): array
    {
        $body = !empty($opts['base64']) ? ['base64' => $xml] : ['xml' => $xml];

        return $this->request('POST', '/einvoice/validate', ['rules' => $opts['rules'] ?? null], $body);
    }

    /** @param array<string,mixed> $invoice */
    public function einvoiceCreate(array $invoice): array
    {
        return $this->request('POST', '/einvoice/create', [], $invoice);
    }

    public function einvoiceSample(): array
    {
        return $this->request('GET', '/einvoice/sample');
    }

    // Account

    public function usage(): array
    {
        return $this->request('GET', '/billing/usage');
    }

    public function plans(): array
    {
        return $this->request('GET', '/billing/plans');
    }
}
