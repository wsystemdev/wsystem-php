<?php

declare(strict_types=1);

namespace WSystem\Laravel;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use WSystem\WSystem;

/**
 * Auto-discovered by Laravel. Binds a shared WSystem client (key from
 * WSYSTEM_API_KEY, or config('services.wsystem.key')) and registers the
 * `eu_vat` rule; `eu_vat:strict` also fails when VIES is unavailable.
 */
class WSystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WSystem::class, function ($app) {
            $cfg = $app['config']->get('services.wsystem', []);

            return new WSystem(array_filter([
                'apiKey' => $cfg['key'] ?? null,
                'baseUrl' => $cfg['base_url'] ?? null,
                'timeout' => $cfg['timeout'] ?? null,
            ], static fn ($v) => $v !== null));
        });
    }

    public function boot(): void
    {
        Validator::extend('eu_vat', function ($attribute, $value, $parameters) {
            $rule = new EuVat($this->app->make(WSystem::class));
            if (in_array('strict', $parameters, true)) {
                $rule->strict();
            }

            return $rule->check($value) === true;
        }, 'The :attribute is not a valid EU VAT number.');
    }
}
