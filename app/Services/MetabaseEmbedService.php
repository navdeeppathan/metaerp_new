<?php

namespace App\Services;

class MetabaseEmbedService
{
    public function generateEmbedUrl(int $dashboardId): string
    {
        $siteUrl = rtrim(env('METABASE_SITE_URL', 'https://metabasebi.metaenterprices.co.uk'), '/');
        $secretKey = env('METABASE_SECRET_KEY', 'c83f87aa44d407a9a549eb468e1f0f5378847d07331deb2b621956b96c5c4c0f');
        $expirationMinutes = (int) env('METABASE_EXPIRATION_MINUTES', 10);

        $now = time();
        $payload = [
            'resource' => ['dashboard' => $dashboardId],
            'params' => (object) [],
            'exp' => $now + ($expirationMinutes * 60),
            'iat' => $now,
        ];

        $token = $this->encodeJwt($payload, $secretKey);

        return "{$siteUrl}/embed/dashboard/{$token}#bordered=true&titled=true";
    }

    private function encodeJwt(array $payload, string $secret): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $b64Header = $this->base64UrlEncode(json_encode($header));
        $b64Payload = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "{$b64Header}.{$b64Payload}", $secret, true);
        $b64Signature = $this->base64UrlEncode($signature);

        return "{$b64Header}.{$b64Payload}.{$b64Signature}";
    }

    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
