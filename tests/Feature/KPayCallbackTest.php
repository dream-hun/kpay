<?php

use Illuminate\Support\Facades\Event;
use KPay\LaravelKPay\Events\PaymentFailed;
use KPay\LaravelKPay\Events\PaymentSucceeded;

it('callback acknowledges with required response shape', function (): void {
    $response = $this->postJson('/kpay/callback', [
        'tid' => 'A441489693051',
        'refid' => 'ORDER123456789',
        'statusid' => '01',
        'statusdesc' => 'Successfully processed transaction.',
        'momtransactionid' => '616730887',
    ]);

    $response->assertOk()->assertJson([
        'tid' => 'A441489693051',
        'refid' => 'ORDER123456789',
        'reply' => 'OK',
    ]);
});

it('callback dispatches success event', function (): void {
    Event::fake([PaymentSucceeded::class]);

    $this->postJson('/kpay/callback', [
        'tid' => 'T1',
        'refid' => 'R1',
        'statusid' => '01',
    ])->assertOk();

    Event::assertDispatched(PaymentSucceeded::class, function (PaymentSucceeded $event) {
        return ($event->payload['tid'] ?? null) === 'T1'
            && ($event->payload['refid'] ?? null) === 'R1';
    });
});

it('callback dispatches failed event', function (): void {
    Event::fake([PaymentFailed::class]);

    $this->postJson('/kpay/callback', [
        'tid' => 'T2',
        'refid' => 'R2',
        'statusid' => '02',
        'statusdesc' => 'TARGET_AUTHORIZATION_ERROR',
    ])->assertOk();

    Event::assertDispatched(PaymentFailed::class, function (PaymentFailed $event) {
        return ($event->payload['tid'] ?? null) === 'T2'
            && ($event->payload['refid'] ?? null) === 'R2';
    });
});
