<?php

namespace KPay\LaravelKPay\Facades;

use Illuminate\Support\Facades\Facade;
use KPay\LaravelKPay\Exceptions\KPayApiException;

/**
 * @method static array pay(array $payload)
 * @method static array checkStatus(string $refid)
 *
 * @throws \InvalidArgumentException
 * @throws KPayApiException
 */
class KPay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'kpay';
    }
}
