<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use KPay\LaravelKPay\Enums\PaymentMethod;
use KPay\LaravelKPay\Exceptions\KPayApiException;
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

it('accepts a PaymentMethod enum for pmethod', function (): void {
    Http::fake([
        'https://pay.esicia.com/*' => Http::response(['reply' => 'PENDING', 'retcode' => 0, 'success' => 1]),
    ]);

    KPay::pay([
        'msisdn' => '250783000001',
        'email' => 'customer@example.com',
        'details' => 'Order',
        'refid' => 'ORDER1',
        'amount' => 5000,
        'cname' => 'John Doe',
        'cnumber' => 'CUST001',
        'pmethod' => PaymentMethod::Momo,
    ]);

    Http::assertSent(fn (Request $r) => ($r->data()['pmethod'] ?? null) === 'momo');
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

it('throws when api_key is not configured', function (): void {
    config(['kpay.api_key' => '']);

    KPay::pay([
        'msisdn' => '250783000001',
        'email' => 'customer@example.com',
        'details' => 'Order',
        'refid' => 'REF1',
        'amount' => 5000,
        'cname' => 'John',
        'cnumber' => 'C1',
        'pmethod' => 'momo',
    ]);
})->throws(\InvalidArgumentException::class, 'api_key');

it('throws when a required field is missing', function (): void {
    KPay::pay([
        'msisdn' => '250783000001',
        'email' => 'customer@example.com',
        // 'details' intentionally omitted
        'refid' => 'REF1',
        'amount' => 5000,
        'cname' => 'John',
        'cnumber' => 'C1',
        'pmethod' => 'momo',
    ]);
})->throws(\InvalidArgumentException::class, 'details');

it('throws when pmethod is invalid', function (): void {
    KPay::pay([
        'msisdn' => '250783000001',
        'email' => 'customer@example.com',
        'details' => 'Order',
        'refid' => 'REF1',
        'amount' => 5000,
        'cname' => 'John',
        'cnumber' => 'C1',
        'pmethod' => 'invalid_method',
    ]);
})->throws(\InvalidArgumentException::class, 'invalid_method');

it('throws when momo amount is below 100', function (): void {
    KPay::pay([
        'msisdn' => '250783000001',
        'email' => 'customer@example.com',
        'details' => 'Order',
        'refid' => 'REF1',
        'amount' => 50,
        'cname' => 'John',
        'cnumber' => 'C1',
        'pmethod' => 'momo',
    ]);
})->throws(\InvalidArgumentException::class, 'momo');

it('throws when momo amount exceeds 5000000', function (): void {
    KPay::pay([
        'msisdn' => '250783000001',
        'email' => 'customer@example.com',
        'details' => 'Order',
        'refid' => 'REF1',
        'amount' => 5_000_001,
        'cname' => 'John',
        'cnumber' => 'C1',
        'pmethod' => 'momo',
    ]);
})->throws(\InvalidArgumentException::class, 'momo');

it('throws when cc amount is below 1000', function (): void {
    KPay::pay([
        'msisdn' => '250783000001',
        'email' => 'customer@example.com',
        'details' => 'Order',
        'refid' => 'REF1',
        'amount' => 500,
        'cname' => 'John',
        'cnumber' => 'C1',
        'pmethod' => 'cc',
    ]);
})->throws(\InvalidArgumentException::class, 'cc');

it('throws when spenn amount exceeds 1000000', function (): void {
    KPay::pay([
        'msisdn' => '250783000001',
        'email' => 'customer@example.com',
        'details' => 'Order',
        'refid' => 'REF1',
        'amount' => 1_000_001,
        'cname' => 'John',
        'cnumber' => 'C1',
        'pmethod' => 'spenn',
    ]);
})->throws(\InvalidArgumentException::class, 'spenn');

it('throws KPayApiException when retcode is 604', function (): void {
    Http::fake([
        'https://pay.esicia.com/*' => Http::response([
            'retcode' => 604,
            'reply' => 'DUPLICATE_REFID',
        ]),
    ]);

    KPay::pay([
        'msisdn' => '250783000001',
        'email' => 'customer@example.com',
        'details' => 'Order',
        'refid' => 'ORDER123',
        'amount' => 5000,
        'cname' => 'John',
        'cnumber' => 'C1',
        'pmethod' => 'momo',
    ]);
})->throws(KPayApiException::class, '604');

it('throws KPayApiException with correct retcode', function (): void {
    Http::fake([
        'https://pay.esicia.com/*' => Http::response([
            'retcode' => 605,
            'reply' => 'AMOUNT_OUT_OF_RANGE',
        ]),
    ]);

    try {
        KPay::pay([
            'msisdn' => '250783000001',
            'email' => 'customer@example.com',
            'details' => 'Order',
            'refid' => 'ORDER1',
            'amount' => 5000,
            'cname' => 'John',
            'cnumber' => 'C1',
            'pmethod' => 'momo',
        ]);
    } catch (KPayApiException $e) {
        expect($e->retcode)->toBe(605)
            ->and($e->reply)->toBe('AMOUNT_OUT_OF_RANGE');

        return;
    }

    $this->fail('Expected KPayApiException was not thrown.');
});

it('throws when refid is empty in checkStatus', function (): void {
    KPay::checkStatus('');
})->throws(\InvalidArgumentException::class, 'refid');
