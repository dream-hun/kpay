<?php

namespace KPay\LaravelKPay\Tests;

use KPay\LaravelKPay\KPayServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [KPayServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('kpay.base_url', 'https://pay.esicia.com/');
        $app['config']->set('kpay.api_key', 'test-api-key');
        $app['config']->set('kpay.username', 'test-user');
        $app['config']->set('kpay.password', 'test-pass');

        $app['config']->set('kpay.defaults', [
            'currency' => 'RWF',
            'retailerid' => 'RID123',
            'returl' => 'https://example.test/kpay/callback',
            'redirecturl' => 'https://example.test/redirect',
            'logourl' => null,
        ]);

        $app['config']->set('kpay.callback.enabled', true);
        $app['config']->set('kpay.callback.path', 'kpay/callback');
        $app['config']->set('kpay.callback.middleware', 'api');
    }
}
