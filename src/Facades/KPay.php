<?php

namespace KPay\LaravelKPay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array pay(array $payload)
 * @method static array checkStatus(string $refid)
 */
class KPay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'kpay';
    }
}
