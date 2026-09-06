<?php

namespace App\Integrations\Duitku;

class DuitkuSignature
{
    public static function generateRequestSignature(string $merchantCode, string $merchantOrderId, int $amount, string $apiKey): string
    {
        return md5($merchantCode.$merchantOrderId.$amount.$apiKey);
    }

    public static function generateCallbackSignatureHmac(string $merchantCode, int $amount, string $merchantOrderId, string $apiKey): string
    {
        return hash_hmac('sha256', $merchantCode.(string) $amount.$merchantOrderId, $apiKey);
    }

    public static function generateCallbackSignatureMd5(string $merchantCode, int $amount, string $merchantOrderId, string $apiKey): string
    {
        return md5($merchantCode.$amount.$merchantOrderId.$apiKey);
    }

    public static function verifyCallbackSignature(string $receivedSignature, string $merchantCode, int $amount, string $merchantOrderId, string $apiKey): bool
    {
        $expectedHmac = self::generateCallbackSignatureHmac($merchantCode, $amount, $merchantOrderId, $apiKey);
        if (hash_equals($expectedHmac, $receivedSignature)) {
            return true;
        }

        $expectedMd5 = self::generateCallbackSignatureMd5($merchantCode, $amount, $merchantOrderId, $apiKey);
        if (hash_equals($expectedMd5, $receivedSignature)) {
            return true;
        }

        return false;
    }
}
