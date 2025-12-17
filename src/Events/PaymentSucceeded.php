<?php

namespace KPay\LaravelKPay\Events;

class PaymentSucceeded
{
    public function __construct(public readonly array $payload)
    {
    }
}
