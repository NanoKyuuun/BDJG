<?php

namespace App\Integrations\Duitku;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class DuitkuClient
{
    public function __construct(
        protected DuitkuConfig $config
    ) {}

    public function createInvoice(array $params): Response
    {
        $url = rtrim($this->config->baseUrl, '/').'/v2/inquiry';

        return Http::timeout($this->config->timeout)
            ->acceptJson()
            ->asJson()
            ->post($url, $params);
    }

    public function checkTransaction(string $merchantOrderId): Response
    {
        $signature = hash('md5', $this->config->merchantCode.$merchantOrderId.$this->config->apiKey);

        $url = rtrim($this->config->baseUrl, '/').'/transactionStatus';

        return Http::timeout($this->config->timeout)
            ->acceptJson()
            ->asJson()
            ->post($url, [
                'merchantCode' => $this->config->merchantCode,
                'merchantOrderId' => $merchantOrderId,
                'signature' => $signature,
            ]);
    }
}
