<?php

namespace App\Providers;

use App\Services\Payments\FakeGateway;
use App\Services\Payments\GatewayException;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaystackGateway;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
