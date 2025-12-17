<?php

use Illuminate\Support\Facades\Route;
use KPay\LaravelKPay\Http\Controllers\KPayCallbackController;

$path = config('kpay.callback.path', 'kpay/callback');
$middleware = config('kpay.callback.middleware', 'api');

Route::post($path, KPayCallbackController::class)
    ->middleware($middleware)
    ->name('kpay.callback');
