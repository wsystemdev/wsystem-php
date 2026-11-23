<?php

declare(strict_types=1);

namespace WSystem;

/** HTTP 409 (for example an idempotency key reused with a different body). */
class ConflictException extends WSystemException
{
}
