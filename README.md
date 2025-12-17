# K-Pay Laravel Package

Laravel 12 package for integrating with the **K-Pay** payment gateway.

Based on the official K-Pay API documentation: https://developers.kpay.africa/documentation.php

## Installation

Require the package via Composer:

```bash
composer require kpay/laravel-kpay
```

Publish the config:

```bash
php artisan vendor:publish --tag=kpay-config
```

## Configuration

Set these environment variables:

```dotenv
KPAY_BASE_URL=https://pay.esicia.com/
KPAY_API_KEY=your_api_key
KPAY_USERNAME=your_username
KPAY_PASSWORD=your_password

KPAY_RETAILER_ID=YOUR_RETAILER_ID
KPAY_RETURL=https://your-app.com/kpay/callback
KPAY_REDIRECTURL=https://your-app.com/payment/return
KPAY_CURRENCY=RWF
```

## Usage

### Initiate a payment

```php
use KPay;

$result = KPay::pay([
    'msisdn' => '250783300000',
    'email' => 'customer@example.com',
    'details' => 'Order #12345',
    'refid' => 'ORDER123456789',
    'amount' => 5000,
    'cname' => 'John Doe',
    'cnumber' => 'CUST001',
    'pmethod' => 'momo', // momo, cc, spenn
]);

// If success == 1, redirect user to $result['url']
```

### Check payment status

```php
use KPay;

$status = KPay::checkStatus('ORDER123456789');
// statusid: 01 (success), 02 (failed), 03 (pending)
```

### Webhook (Callback)

By default the package registers:

- `POST /kpay/callback`

K-Pay requires your endpoint to respond:

```json
{ "tid": "...", "refid": "...", "reply": "OK" }
```

The controller also dispatches events based on `statusid`:

- `KPay\LaravelKPay\Events\PaymentSucceeded`
- `KPay\LaravelKPay\Events\PaymentFailed`
- `KPay\LaravelKPay\Events\PaymentPending`

To customize the path or middleware:

```dotenv
KPAY_CALLBACK_PATH=kpay/callback
KPAY_CALLBACK_MIDDLEWARE=api
KPAY_CALLBACK_ENABLED=true
```

## Testing

```bash
composer test
```

## License

MIT
