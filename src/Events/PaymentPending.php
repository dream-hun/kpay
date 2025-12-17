<?php

namespace KPay\LaravelKPay\Events;

class PaymentPending
{
    public function __construct(public readonly array $payload)
    {
    }
}
