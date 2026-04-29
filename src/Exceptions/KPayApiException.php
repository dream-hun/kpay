<?php

namespace KPay\LaravelKPay\Exceptions;

class KPayApiException extends KPayException
{
    public function __construct(
        public readonly int $retcode,
        public readonly string $reply,
        string $message = '',
    ) {
        parent::__construct($message !== '' ? $message : "K-Pay API error {$retcode}: {$reply}");
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            retcode: (int) ($response['retcode'] ?? 0),
            reply: (string) ($response['reply'] ?? ''),
        );
    }
}
