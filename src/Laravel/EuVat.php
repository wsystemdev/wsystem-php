<?php

declare(strict_types=1);

namespace WSystem\Laravel;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use WSystem\WSystem;
use WSystem\WSystemException;

/**
 * Laravel validation rule: the value must be an EU VAT number that VIES reports as valid.
 *
 *   $request->validate(['vat_id' => ['required', new EuVat()]]);
 *   $request->validate(['vat_id' => 'required|eu_vat']);          // same, via the service provider
 *   $request->validate(['vat_id' => ['required', (new EuVat())->strict()]]);
 *
 * By default a VIES outage (the member state's service is down, `valid: null`) or an
 * API error does NOT fail validation: "VIES is down" is not "this number is invalid",
 * and you usually don't want to block a checkout or sign-up for it. Call ->strict()
 * to fail in those cases too. The last API answer is available via ->result().
 */
class EuVat implements ValidationRule
{
    private bool $strict = false;
    private ?array $result = null;

    public function __construct(private ?WSystem $client = null)
    {
    }

    public function strict(bool $strict = true): static
    {
        $this->strict = $strict;

        return $this;
    }

    /** The decoded /verify answer of the last check (null if the call failed). */
    public function result(): ?array
    {
        return $this->result;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $outcome = $this->check($value);
        if ($outcome === true) {
            return;
        }
        $fail($outcome === false
            ? 'The :attribute is not a valid EU VAT number.'
            : 'The :attribute could not be checked right now (VIES unavailable). Please try again later.');
    }

    /**
     * @return true|false|null true = passes, false = invalid, null = could not be checked and strict mode is on
     */
    public function check(mixed $value): ?bool
    {
        $this->result = null;
        if (!is_string($value) || trim($value) === '') {
            return false;
        }
        try {
            $this->result = $this->client()->verifyVat(trim($value));
        } catch (WSystemException) {
            return $this->strict ? null : true;
        }
        $valid = $this->result['valid'] ?? null;
        if ($valid === true) {
            return true;
        }
        if ($valid === false) {
            return false;
        }

        return $this->strict ? null : true;
    }

    private function client(): WSystem
    {
        if ($this->client === null) {
            $this->client = function_exists('app') && app()->bound(WSystem::class) ? app(WSystem::class) : new WSystem();
        }

        return $this->client;
    }
}
