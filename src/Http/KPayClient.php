<?php

namespace KPay\LaravelKPay\Http;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use InvalidArgumentException;
use KPay\LaravelKPay\Enums\PaymentMethod;
use KPay\LaravelKPay\Exceptions\KPayApiException;

readonly class KPayClient
{
    public function __construct(
        private HttpFactory $http,
        private array $config
    ) {
    }

    /**
     * Initiate a payment (action=pay).
     *
     * Required payload fields per K-Pay docs:
     * msisdn, email, details, refid, amount, cname, cnumber, pmethod,
     * retailerid, returl, redirecturl
     *
     * @throws InvalidArgumentException
     * @throws KPayApiException
     * @throws RequestException|ConnectionException
     */
    public function pay(array $payload): array
    {
        if (isset($payload['pmethod']) && $payload['pmethod'] instanceof PaymentMethod) {
            $payload['pmethod'] = $payload['pmethod']->value;
        }

        $payload = array_merge(
            [
                'action' => 'pay',
                'currency' => $this->config['defaults']['currency'] ?? 'RWF',
                'retailerid' => $this->config['defaults']['retailerid'] ?? null,
                'returl' => $this->config['defaults']['returl'] ?? null,
                'redirecturl' => $this->config['defaults']['redirecturl'] ?? null,
                'logourl' => $this->config['defaults']['logourl'] ?? null,
            ],
            $payload
        );

        $payload['action'] = 'pay';

        $this->assertConfigured();
        $this->assertRequired($payload);
        $this->assertPaymentMethod($payload['pmethod']);
        $this->assertAmountRange((int) $payload['amount'], $payload['pmethod']);

        $payload = array_filter($payload, static fn ($v) => $v !== null);

        return $this->request($payload);
    }

    /**
     * Check payment status (action=checkstatus) by refid.
     *
     * @throws InvalidArgumentException
     * @throws KPayApiException
     * @throws RequestException|ConnectionException
     */
    public function checkStatus(string $refid): array
    {
        $this->assertConfigured();

        if (trim($refid) === '') {
            throw new InvalidArgumentException('K-Pay refid is required.');
        }

        return $this->request([
            'action' => 'checkstatus',
            'refid' => $refid,
        ]);
    }

    /**
     * @throws KPayApiException
     * @throws RequestException|ConnectionException
     */
    private function request(array $payload): array
    {
        $response = $this->client()->post('/', $payload);
        $response->throw();

        $data = $response->json() ?? [];

        $retcode = $data['retcode'] ?? null;
        if ($retcode !== null && (int) $retcode !== 0) {
            throw KPayApiException::fromResponse($data);
        }

        return $data;
    }

    private function client(): PendingRequest
    {
        return $this->http
            ->createPendingRequest()
            ->baseUrl($this->baseUrl())
            ->acceptJson()
            ->asJson()
            ->withHeaders($this->headers());
    }

    private function baseUrl(): string
    {
        $baseUrl = (string) ($this->config['base_url'] ?? 'https://pay.esicia.com/');

        return rtrim($baseUrl, '/');
    }

    private function headers(): array
    {
        $apiKey = (string) ($this->config['api_key'] ?? '');
        $username = (string) ($this->config['username'] ?? '');
        $password = (string) ($this->config['password'] ?? '');

        return [
            'Content-Type' => 'application/json',
            'Kpay-Key' => $apiKey,
            'Authorization' => 'Basic '.base64_encode($username.':'.$password),
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertConfigured(): void
    {
        if (trim((string) ($this->config['api_key'] ?? '')) === '') {
            throw new InvalidArgumentException('K-Pay api_key is not configured (kpay.api_key).');
        }

        if (trim((string) ($this->config['username'] ?? '')) === '') {
            throw new InvalidArgumentException('K-Pay username is not configured (kpay.username).');
        }

        if (trim((string) ($this->config['password'] ?? '')) === '') {
            throw new InvalidArgumentException('K-Pay password is not configured (kpay.password).');
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertRequired(array $payload): void
    {
        $requiredKeys = [
            'msisdn',
            'email',
            'details',
            'refid',
            'amount',
            'cname',
            'cnumber',
            'pmethod',
            'retailerid',
            'returl',
            'redirecturl',
        ];
        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $payload) || $payload[$key] === '' || $payload[$key] === null) {
                throw new InvalidArgumentException("K-Pay payload missing required field: $key");
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertPaymentMethod(string $pmethod): void
    {
        if (PaymentMethod::tryFrom($pmethod) === null) {
            $accepted = implode(', ', array_column(PaymentMethod::cases(), 'value'));
            throw new InvalidArgumentException(
                "K-Pay pmethod '{$pmethod}' is invalid. Accepted values: {$accepted}."
            );
        }
    }

    /**
     * Amount limits per payment method (RWF):
     *   momo:  100 – 5,000,000
     *   cc:    1,000 – 10,000,000
     *   spenn: 100 – 1,000,000
     *
     * @throws InvalidArgumentException
     */
    private function assertAmountRange(int $amount, string $pmethod): void
    {
        [$min, $max] = match ($pmethod) {
            PaymentMethod::Momo->value  => [100, 5_000_000],
            PaymentMethod::Card->value  => [1_000, 10_000_000],
            PaymentMethod::Spenn->value => [100, 1_000_000],
            default                     => [null, null],
        };

        if ($min === null) {
            return;
        }

        if ($amount < $min || $amount > $max) {
            throw new InvalidArgumentException(
                "K-Pay amount {$amount} is out of range for pmethod '{$pmethod}' (min: {$min}, max: {$max} RWF)."
            );
        }
    }
}
