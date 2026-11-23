<?php

declare(strict_types=1);

namespace WSystem;

/** HTTP 429. */
class RateLimitException extends WSystemException
{
    public function retryAfter(): ?float
    {
        return isset($this->headers['retry-after']) && is_numeric($this->headers['retry-after']) ? (float) $this->headers['retry-after'] : null;
    }
}
