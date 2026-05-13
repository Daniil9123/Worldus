<?php

declare(strict_types=1);

namespace Worldus\Tests;

class E2EClient
{
    private string $baseUrl;
    private array $cookies = [];

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function get(string $path): array
    {
        return $this->request('GET', $path, []);
    }

    public function post(string $path, array $data): array
    {
        return $this->request('POST', $path, $data);
    }

    public function followRedirect(array $response): array
    {
        $location = $response['headers']['location'] ?? '';
        if ($location === '') {
            return $response;
        }

        return $this->get($this->resolveUrl($location));
    }

    public function extractPasswordFieldName(string $html): string
    {
        if (preg_match('/name="(password_real_[0-9]+)"/', $html, $matches)) {
            return $matches[1];
        }

        throw new \RuntimeException('Password field name not found in registration form.');
    }

    private function request(string $method, string $path, array $data): array
    {
        $url = $this->resolveUrl($path);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Worldus-E2E-Test/1.0');

        if (!empty($this->cookies)) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->buildCookieHeader());
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return [
                'status' => 0,
                'headers' => [],
                'body' => $curlError,
            ];
        }

        $rawHeaders = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        $headers = $this->parseHeaders($rawHeaders);
        $this->storeCookies($rawHeaders);

        return [
            'status' => $status,
            'headers' => $headers,
            'body' => $body,
        ];
    }

    private function resolveUrl(string $path): string
    {
        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        if (strpos($path, '/') === 0) {
            $parts = parse_url($this->baseUrl);
            $scheme = $parts['scheme'] ?? 'http';
            $host = $parts['host'] ?? 'localhost';
            $port = isset($parts['port']) ? ':' . $parts['port'] : '';
            $basePath = $parts['path'] ?? '';

            return $scheme . '://' . $host . $port . $basePath . $path;
        }

        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    private function buildCookieHeader(): string
    {
        $pairs = [];
        foreach ($this->cookies as $name => $value) {
            $pairs[] = $name . '=' . $value;
        }

        return implode('; ', $pairs);
    }

    private function parseHeaders(string $rawHeaders): array
    {
        $headers = [];
        $lines = preg_split('/\r?\n/', trim($rawHeaders));
        foreach ($lines as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }

            [$name, $value] = explode(':', $line, 2);
            $headers[strtolower(trim($name))] = trim($value);
        }

        return $headers;
    }

    private function storeCookies(string $rawHeaders): void
    {
        $lines = preg_split('/\r?\n/', trim($rawHeaders));
        foreach ($lines as $line) {
            if (stripos($line, 'Set-Cookie:') !== 0) {
                continue;
            }

            $cookie = trim(substr($line, strlen('Set-Cookie:')));
            $pair = explode(';', $cookie, 2)[0];
            [$name, $value] = explode('=', $pair, 2) + [null, null];

            if ($name !== null) {
                $this->cookies[$name] = $value;
            }
        }
    }
}
