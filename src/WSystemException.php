<?php

declare(strict_types=1);

namespace WSystem;

/**
 * Base class of everything the client throws: API answers with HTTP >= 400 (status, body, code, docsUrl, requestId)
 * and transport errors (status 0).
 */
class WSystemException extends \RuntimeException
{
    public readonly ?string $apiCode;
    public readonly ?string $docsUrl;
    public readonly ?string $requestId;

    /**
     * @param mixed $body decoded JSON body (or raw text) of the error response
     * @param array<string,string> $headers response headers, lower-case keys
     */
    public function __construct(string $message, public readonly int $status = 0, public readonly mixed $body = null, public readonly array $headers = [])
    {
        parent::__construct($message, $status);
        $d = is_array($body) ? $body : [];
        $detail = $d['detail'] ?? null;
        $this->apiCode = (is_array($detail) ? ($detail['code'] ?? null) : null) ?? (isset($d['error']) && is_string($d['error']) ? $d['error'] : null) ?? ($d['code'] ?? null);
        $this->docsUrl = $d['docs_url'] ?? $d['docs'] ?? null;
        $this->requestId = $headers['x-request-id'] ?? null;
    }

    /** Machine-readable error code from the API (for example "missing_input"), when it sent one. */
    public function errorCode(): ?string
    {
        return $this->apiCode;
    }

    public static function forStatus(int $status, string $message, mixed $body, array $headers): self
    {
        $cls = match (true) {
            $status === 400 || $status === 422 => BadRequestException::class,
            $status === 401 || $status === 403 => AuthenticationException::class,
            $status === 402 => PaymentRequiredException::class,
            $status === 404 => NotFoundException::class,
            $status === 409 => ConflictException::class,
            $status === 429 => RateLimitException::class,
            $status >= 500 => ServerException::class,
            default => self::class,
        };

        return new $cls($message, $status, $body, $headers);
    }
}
