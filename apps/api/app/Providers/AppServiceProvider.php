<?php

namespace App\Providers;

use App\Domains\Payments\Contracts\PaymentGateway;
use App\Integrations\Duitku\DuitkuClient;
use App\Integrations\Duitku\DuitkuConfig;
use App\Integrations\Duitku\DuitkuPaymentGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DuitkuConfig::class, fn () => DuitkuConfig::fromConfig());

        $this->app->singleton(DuitkuClient::class, fn ($app) => new DuitkuClient($app->make(DuitkuConfig::class)));

        $this->app->bind(PaymentGateway::class, DuitkuPaymentGateway::class);
    }

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip())
            );
        });
    }
}
