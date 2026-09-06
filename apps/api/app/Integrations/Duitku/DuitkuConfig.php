<?php

namespace App\Integrations\Duitku;

class DuitkuConfig
{
    public function __construct(
        public readonly string $env = 'sandbox',
        public readonly string $merchantCode = 'D12345',
        public readonly string $apiKey = 'sandbox_api_key_12345',
        public readonly string $baseUrl = 'https://sandbox.duitku.com/webapi/api/merchant/',
        public readonly string $callbackUrl = 'http://localhost:8000/api/v1/webhooks/duitku',
        public readonly string $returnUrl = 'http://localhost:3000/client/invoices',
        public readonly int $timeout = 15,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            env: config('services.duitku.env', 'sandbox'),
            merchantCode: config('services.duitku.merchant_code', 'D12345'),
            apiKey: config('services.duitku.api_key', 'sandbox_api_key_12345'),
            baseUrl: config('services.duitku.base_url', 'https://sandbox.duitku.com/webapi/api/merchant/'),
            callbackUrl: config('services.duitku.callback_url', 'http://localhost:8000/api/v1/webhooks/duitku'),
            returnUrl: config('services.duitku.return_url', 'http://localhost:3000/client/invoices'),
            timeout: (int) config('services.duitku.timeout', 15),
        );
    }
}
