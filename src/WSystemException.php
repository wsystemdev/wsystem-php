<?php

declare(strict_types=1);

namespace WSystem;

/** Thrown for non-2xx API answers and transport errors. */
class WSystemException extends \RuntimeException
{
    /** @param mixed $body decoded JSON body (or raw text) of the error response */
    public function __construct(string $message, public readonly int $status = 0, public readonly mixed $body = null)
    {
        parent::__construct($message, $status);
    }
}
