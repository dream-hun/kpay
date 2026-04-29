<?php

namespace KPay\LaravelKPay\Enums;

enum PaymentStatus: string
{
    case Succeeded = '01';
    case Failed = '02';
    case Pending = '03';
}
