<?php

namespace App\Providers;

use App\Services\Payments\FakeGateway;
use App\Services\Payments\GatewayException;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaystackGateway;
use App\Services\Reports\ReportCurrency;
use App\Support\LiveUpdates;
use Carbon\CarbonImmutable;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One per request, so every report shares rates and missing-rate warnings.
        $this->app->scoped(ReportCurrency::class);

        // One live-update signal per area per request or queued job.
        $this->app->scoped(LiveUpdates::class);

        $this->app->singleton(PaymentGateway::class, function () {
            return match (config('services.payments.driver')) {
                'paystack' => new PaystackGateway(
                    secretKey: (string) config('services.paystack.secret_key'),
                    baseUrl: (string) config('services.paystack.base_url'),
                ),
                'fake' => new FakeGateway,
                default => throw new GatewayException('Unknown payment driver ['.config('services.payments.driver').'].'),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        if (app()->isProduction()) {
            URL::forceHttps();
        }

        $proxies = config('app.trusted_proxies');
        if (is_string($proxies) && $proxies !== '') {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
