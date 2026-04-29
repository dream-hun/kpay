<?php

namespace KPay\LaravelKPay\Events;

class PaymentPending
{
    public readonly string $tid;

    public readonly string $refid;

    public readonly string $statusid;

    public readonly string $statusdesc;

    public readonly string $payaccount;

    public function __construct(public readonly array $payload)
    {
        $this->tid = (string) ($payload['tid'] ?? '');
        $this->refid = (string) ($payload['refid'] ?? '');
        $this->statusid = (string) ($payload['statusid'] ?? '');
        $this->statusdesc = (string) ($payload['statusdesc'] ?? '');
        $this->payaccount = (string) ($payload['payaccount'] ?? '');
    }
}
