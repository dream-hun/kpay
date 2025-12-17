<?php

namespace KPay\LaravelKPay\Events;

class PaymentFailed
{
    public function __construct(public readonly array $payload)
    {
    }
}
