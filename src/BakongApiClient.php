<?php

declare(strict_types=1);

namespace Konthaina\Khqr;

final class BakongApiClient
{
    public const DEFAULT_BASE_URL = 'https://api-bakong.nbc.gov.kh';

    private string $baseUrl;
    private string $accessToken;
    private int $timeout;

    public function __construct(string $baseUrlOrToken, ?string $accessToken = null, int $timeout = 30)
    {
        if ($accessToken === null) {
            $accessToken = $baseUrlOrToken;
            $baseUrlOrToken = self::DEFAULT_BASE_URL;
        }

        $accessToken = trim($accessToken);
        if ($accessToken === '') {
            throw new \InvalidArgumentException('Access token is required');
        }

        $this->baseUrl = rtrim($baseUrlOrToken, '/');
        $this->accessToken = $accessToken;
        $this->timeout = $timeout;
    }

    public static function fromAccessToken(string $accessToken, int $timeout = 30): self
    {
        return new self(self::DEFAULT_BASE_URL, $accessToken, $timeout);
    }

    /**
     * Check transaction status by md5 hash.
     *
     * @return array<string, mixed>
     */
    public function checkTransactionByMd5(string $md5): array
    {
        $md5 = trim($md5);
        if ($md5 === '') {
            throw new \InvalidArgumentException('md5 is required');
        }

        return $this->postJson('/v1/check_transaction_by_md5', ['md5' => $md5]);
    }

    /**
     * Convenience helper: compute md5 from QR payload and check status.
     *
     * @return array<string, mixed>
     */
    public function checkTransactionByQr(string $qr): array
    {
        return $this->checkTransactionByMd5(md5($qr));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function postJson(string $path, array $payload): array
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);

        if ($body === false) {
            throw new \RuntimeException('Failed to encode JSON payload.');
        }

        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                throw new \RuntimeException('Failed to initialize cURL.');
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => $this->timeout,
            ]);

            $response = curl_exec($ch);
            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new \RuntimeException('Bakong API request failed: ' . $error);
            }

            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $this->decodeResponse($response, $status);
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $body,
                'timeout' => $this->timeout,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        $status = $this->extractHttpStatus($http_response_header ?? []);

        if ($response === false) {
            throw new \RuntimeException('Bakong API request failed.');
        }

        return $this->decodeResponse($response, $status);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(string $response, int $status): array
    {
        if ($status >= 400 || $status === 0) {
            throw new \RuntimeException('Bakong API returned HTTP ' . $status . ': ' . $response);
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid JSON response from Bakong API.');
        }

        return $decoded;
    }

    /**
     * @param array<int, string> $headers
     */
    private function extractHttpStatus(array $headers): int
    {
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\\/\\S+\\s+(\\d+)/i', $header, $matches) === 1) {
                return (int) $matches[1];
            }
        }

        return 0;
    }
}
