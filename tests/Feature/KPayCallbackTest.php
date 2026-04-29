<?php

use Illuminate\Support\Facades\Event;
use KPay\LaravelKPay\Events\PaymentFailed;
use KPay\LaravelKPay\Events\PaymentPending;
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

    Event::assertDispatched(PaymentSucceeded::class, fn (PaymentSucceeded $event) => $event->tid === 'T1'
        && $event->refid === 'R1');
});

it('callback dispatches failed event', function (): void {
    Event::fake([PaymentFailed::class]);

    $this->postJson('/kpay/callback', [
        'tid' => 'T2',
        'refid' => 'R2',
        'statusid' => '02',
        'statusdesc' => 'TARGET_AUTHORIZATION_ERROR',
    ])->assertOk();

    Event::assertDispatched(PaymentFailed::class, fn (PaymentFailed $event) => $event->tid === 'T2'
        && $event->refid === 'R2');
});

it('callback dispatches pending event for statusid 03', function (): void {
    Event::fake([PaymentPending::class]);

    $this->postJson('/kpay/callback', [
        'tid' => 'T3',
        'refid' => 'R3',
        'statusid' => '03',
    ])->assertOk();

    Event::assertDispatched(PaymentPending::class, fn (PaymentPending $event) => $event->tid === 'T3'
        && $event->refid === 'R3');
});

it('callback dispatches no event for unknown statusid', function (): void {
    Event::fake([PaymentSucceeded::class, PaymentFailed::class, PaymentPending::class]);

    $this->postJson('/kpay/callback', [
        'tid' => 'T9',
        'refid' => 'R9',
        'statusid' => '99',
    ])->assertOk();

    Event::assertNotDispatched(PaymentSucceeded::class);
    Event::assertNotDispatched(PaymentFailed::class);
    Event::assertNotDispatched(PaymentPending::class);
});

it('event exposes typed properties', function (): void {
    Event::fake([PaymentSucceeded::class]);

    $this->postJson('/kpay/callback', [
        'tid' => 'TID1',
        'refid' => 'REF1',
        'statusid' => '01',
        'statusdesc' => 'Success',
        'payaccount' => '250783000001',
    ])->assertOk();

    Event::assertDispatched(PaymentSucceeded::class, fn (PaymentSucceeded $event): bool => $event->tid === 'TID1'
        && $event->refid === 'REF1'
        && $event->statusid === '01'
        && $event->statusdesc === 'Success'
        && $event->payaccount === '250783000001');
});
