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
use KPay\LaravelKPay\Enums\PaymentMethod;

$result = KPay::pay([
    'msisdn'  => '250783300000',
    'email'   => 'customer@example.com',
    'details' => 'Order #12345',
    'refid'   => 'ORDER123456789',
    'amount'  => 5000,
    'cname'   => 'John Doe',
    'cnumber' => 'CUST001',
    'pmethod' => PaymentMethod::Momo, // or 'momo', 'cc', 'spenn'
]);

// If success == 1, redirect user to $result['url']
```

### Payment method amount limits (RWF)

| Method | `pmethod` | Minimum | Maximum |
|--------|-----------|--------:|--------:|
| Mobile Money (MTN / Airtel) | `momo` | 100 | 5,000,000 |
| Credit / Debit Card | `cc` | 1,000 | 10,000,000 |
| SPENN wallet | `spenn` | 100 | 1,000,000 |

### Check payment status

```php
use KPay;

$status = KPay::checkStatus('ORDER123456789');
// statusid: '01' (success), '02' (failed), '03' (pending)
```

### Webhook (Callback)

By default the package registers:

- `POST /kpay/callback`

K-Pay sends a POST to your callback URL and requires the endpoint to respond:

```json
{ "tid": "...", "refid": "...", "reply": "OK" }
```

The controller dispatches events based on `statusid`. K-Pay callbacks only send `01` (success) or `02` (failed); `03` (pending) is only returned by `checkStatus`.

| `statusid` | Event dispatched |
|------------|-----------------|
| `01` | `KPay\LaravelKPay\Events\PaymentSucceeded` |
| `02` | `KPay\LaravelKPay\Events\PaymentFailed` |
| `03` | `KPay\LaravelKPay\Events\PaymentPending` |

Each event exposes typed properties alongside the raw `$payload` array:

```php
use KPay\LaravelKPay\Events\PaymentSucceeded;

// In your listener:
public function handle(PaymentSucceeded $event): void
{
    $event->tid;        // K-Pay transaction ID
    $event->refid;      // your merchant reference
    $event->statusid;   // '01'
    $event->statusdesc; // human-readable status
    $event->payaccount; // mobile/card account used
    $event->payload;    // full raw payload array
}
```

To customize the path or middleware:

```dotenv
KPAY_CALLBACK_PATH=kpay/callback
KPAY_CALLBACK_MIDDLEWARE=api
KPAY_CALLBACK_ENABLED=true
```

## Enums

```php
use KPay\LaravelKPay\Enums\PaymentMethod;
use KPay\LaravelKPay\Enums\PaymentStatus;

PaymentMethod::Momo->value;   // 'momo'
PaymentMethod::Card->value;   // 'cc'
PaymentMethod::Spenn->value;  // 'spenn'

PaymentStatus::Succeeded->value; // '01'
PaymentStatus::Failed->value;    // '02'
PaymentStatus::Pending->value;   // '03'
```

## Error handling

The package throws typed exceptions you can catch:

```php
use KPay\LaravelKPay\Exceptions\KPayApiException;
use KPay\LaravelKPay\Exceptions\KPayException;

try {
    $result = KPay::pay([...]);
} catch (KPayApiException $e) {
    // API-level error returned by K-Pay
    $e->retcode; // e.g. 604
    $e->reply;   // e.g. 'DUPLICATE_REFID'
} catch (\InvalidArgumentException $e) {
    // Validation failure (missing field, invalid pmethod, amount out of range)
} catch (KPayException $e) {
    // Any other package exception
}
```

### K-Pay API error codes

| `retcode` | `reply` | Description |
|-----------|---------|-------------|
| 0 | PENDING | Payment initiated successfully |
| 600 | INVALID_REQUEST | Missing or invalid parameters |
| 601 | INVALID_API_KEY | API key inactive or not found |
| 602 | INVALID_AUTH | Authentication credentials invalid |
| 603 | IP_NOT_WHITELISTED | Unauthorized IP address |
| 604 | DUPLICATE_REFID | Reference ID already processed |
| 605 | AMOUNT_OUT_OF_RANGE | Amount outside allowed limits |
| 606 | TARGET_AUTHORIZATION_ERROR | Provider rejected transaction |
| 607 | INSUFFICIENT_FUNDS | Insufficient customer balance |
| 608 | TIMEOUT | Transaction timed out |
| 609 | CANCELLED | Customer cancelled |

## Testing

```bash
composer test
```

## Contributing

Please follow our commit message convention (Conventional Commits). See `CONTRIBUTING.md`.

## License

MIT
