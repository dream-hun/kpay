<?php

namespace KPay\LaravelKPay\Enums;

enum PaymentMethod: string
{
    case Momo = 'momo';
    case Card = 'cc';
    case Spenn = 'spenn';
}
