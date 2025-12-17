<?php

namespace KPay\LaravelKPay\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use KPay\LaravelKPay\Events\PaymentFailed;
use KPay\LaravelKPay\Events\PaymentPending;
use KPay\LaravelKPay\Events\PaymentSucceeded;

class KPayCallbackController
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();

        $statusId = (string) ($payload['statusid'] ?? '');

        if ($statusId === '01') {
            event(new PaymentSucceeded($payload));
        } elseif ($statusId === '02') {
            event(new PaymentFailed($payload));
        } elseif ($statusId === '03') {
            event(new PaymentPending($payload));
        }

        return response()->json([
            'tid' => (string) ($payload['tid'] ?? ''),
            'refid' => (string) ($payload['refid'] ?? ''),
            'reply' => 'OK',
        ]);
    }
}
