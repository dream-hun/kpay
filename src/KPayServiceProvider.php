<?php

namespace KPay\LaravelKPay;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;
use KPay\LaravelKPay\Http\KPayClient;

class KPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kpay.php', 'kpay');

        $this->app->singleton(KPayClient::class, function ($app) {
            return new KPayClient(
                $app->make(HttpFactory::class),
                $app['config']->get('kpay', [])
            );
        });

        $this->app->alias(KPayClient::class, 'kpay');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/kpay.php' => $this->app->configPath('kpay.php'),
        ], 'kpay-config');

        if (config('kpay.callback.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
    }
}
