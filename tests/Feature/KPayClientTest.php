<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use KPay\LaravelKPay\Facades\KPay;

it('sends a pay request with required headers and defaults', function (): void {
    Http::fake([
        'https://pay.esicia.com/*' => Http::response([
            'reply' => 'PENDING',
            'url' => 'https://pay.esicia.com/checkout/A123',
            'success' => 1,
            'tid' => 'T123',
            'refid' => 'ORDER123',
            'retcode' => 0,
        ]),
    ]);

    $response = KPay::pay([
        'msisdn' => '250783000001',
        'email' => 'customer@example.com',
        'details' => 'Order #12345',
        'refid' => 'ORDER123',
        'amount' => 5000,
        'cname' => 'John Doe',
        'cnumber' => 'CUST001',
        'pmethod' => 'momo',
    ]);

    expect($response['success'])->toBe(1);

    Http::assertSent(function (Request $request) {
        $data = $request->data();

        return $request->method() === 'POST'
            && $request->url() === 'https://pay.esicia.com/'
            && $request->hasHeader('Kpay-Key', 'test-api-key')
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('test-user:test-pass'))
            && ($data['action'] ?? null) === 'pay'
            && ($data['currency'] ?? null) === 'RWF'
            && ($data['retailerid'] ?? null) === 'RID123'
            && ($data['returl'] ?? null) === 'https://example.test/kpay/callback'
            && ($data['redirecturl'] ?? null) === 'https://example.test/redirect';
    });
});

it('sends a checkstatus request', function (): void {
    Http::fake([
        'https://pay.esicia.com/*' => Http::response([
            'tid' => 'A441489693051',
            'refid' => 'ORDER123456789',
            'momtransactionid' => '616730887',
            'statusid' => '01',
            'statusdesc' => 'Successfully processed transaction.',
        ], 200),
    ]);

    $response = KPay::checkStatus('ORDER123456789');

    expect($response['statusid'])->toBe('01');

    Http::assertSent(function (Request $request) {
        $data = $request->data();

        return $request->method() === 'POST'
            && $request->url() === 'https://pay.esicia.com/'
            && ($data['action'] ?? null) === 'checkstatus'
            && ($data['refid'] ?? null) === 'ORDER123456789';
    });
});
