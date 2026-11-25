<?php

namespace App\Services\AiReview;

use Illuminate\Support\Facades\Http;

/**
 * Fetches a URL that a fellow submitted.
 *
 * The URL is untrusted, so the server must never be tricked into calling its
 * own network (localhost, the cloud metadata endpoint, private ranges).
 * Every hop of a redirect is checked the same way.
 */
class SafeUrlFetcher
{
    public const MAX_BYTES = 2_000_000;
    public const MAX_REDIRECTS = 3;

    /**
     * @return array{ok: bool, status: ?int, url: string, content_type: ?string, title: ?string, text: ?string, error: ?string}
     */
    public function fetch(string $url): array
    {
        $current = $url;

        for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
            $target = $this->inspect($current);
            if ($target['reason']) {
                return $this->result(false, null, $current, error: $target['reason']);
            }

            // Pin the connection to the address that was checked (no DNS rebinding).
            $ip = str_contains($target['ip'], ':') ? '[' . $target['ip'] . ']' : $target['ip'];
            $curl = defined('CURLOPT_RESOLVE') ? [CURLOPT_RESOLVE => ["{$target['host']}:{$target['port']}:{$ip}"]] : [];

            try {
                $response = Http::withHeaders(['User-Agent' => 'IKS-Reviewer/1.0 (+https://innovakickstarter.com)'])
                    ->withOptions(['allow_redirects' => false, 'curl' => $curl])
                    ->timeout(15)
                    ->connectTimeout(8)
                    ->get($current);
            } catch (\Throwable $e) {
                return $this->result(false, null, $current, error: 'Could not reach the page: ' . $this->shortError($e));
            }

            if ($response->redirect()) {
                $location = $response->header('Location');
                if (!$location) {
                    return $this->result(false, $response->status(), $current, error: 'Redirect without a location.');
                }
                $current = $this->resolve($current, $location);
                continue;
            }

            if (!$response->successful()) {
                return $this->result(false, $response->status(), $current, error: "The page answered with HTTP {$response->status()}.");
            }

            $body = substr($response->body(), 0, self::MAX_BYTES);
            $type = strtolower((string) $response->header('Content-Type'));

            if (str_contains($type, 'html')) {
                [$title, $text] = $this->htmlToText($body);
                return $this->result(true, $response->status(), $current, $type, $title, $text);
            }

            if (str_starts_with($type, 'text/') || str_contains($type, 'json') || str_contains($type, 'xml')) {
                return $this->result(true, $response->status(), $current, $type, null, $body);
            }

            return $this->result(true, $response->status(), $current, $type, error: "Content type {$type} is not read in this version.");
        }

        return $this->result(false, null, $current, error: 'Too many redirects.');
    }

    /**
     * Why a URL may not be fetched, or null when it is safe.
     */
    public function unsafeReason(string $url): ?string
    {
        return $this->inspect($url)['reason'];
    }

    /**
     * @return array{reason: ?string, host: string, port: int, ip: string}
     */
    protected function inspect(string $url): array
    {
        $parts = parse_url($url) ?: [];
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = trim($parts['host'] ?? '', '[]');
        $port = (int) ($parts['port'] ?? ($scheme === 'http' ? 80 : 443));
        $deny = fn (string $reason) => ['reason' => $reason, 'host' => $host, 'port' => $port, 'ip' => ''];

        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            return $deny('Only http and https links can be checked.');
        }

        if (!in_array($port, [80, 443, 8080, 8443], true)) {
            return $deny('Links on unusual ports are not fetched.');
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : $this->resolveHost($host);

        if (empty($ips)) {
            return $deny('The domain does not resolve.');
        }

        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $deny('Links to private or internal addresses are not fetched.');
            }
        }

        return ['reason' => null, 'host' => $host, 'port' => $port, 'ip' => $ips[0]];
    }

    /**
     * @return string[]
     */
    protected function resolveHost(string $host): array
    {
        $ips = @gethostbynamel($host) ?: [];

        if (function_exists('dns_get_record')) {
            foreach (@dns_get_record($host, DNS_AAAA) ?: [] as $record) {
                if (!empty($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
        }

        return array_values(array_unique($ips));
    }

    /**
     * @return array{0: ?string, 1: string}
     */
    public function htmlToText(string $html): array
    {
        $title = null;
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5));
        }

        $html = preg_replace('/<(script|style|noscript|svg|head)\b[^>]*>.*?<\/\1>/is', ' ', $html) ?? $html;
        $html = preg_replace('/<(br|\/p|\/div|\/li|\/h[1-6]|\/tr)\s*\/?>/i', "\n", $html) ?? $html;
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n\s*\n+/", "\n\n", $text) ?? $text;

        return [$title, trim($text)];
    }

    protected function resolve(string $base, string $location): string
    {
        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }

        $parts = parse_url($base);
        $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '') . (isset($parts['port']) ? ':' . $parts['port'] : '');

        return str_starts_with($location, '/') ? $origin . $location : rtrim($origin . dirname($parts['path'] ?? '/'), '/') . '/' . $location;
    }

    protected function shortError(\Throwable $e): string
    {
        return mb_substr(preg_replace('/\s+/', ' ', $e->getMessage()), 0, 160);
    }

    protected function result(
        bool $ok,
        ?int $status,
        string $url,
        ?string $contentType = null,
        ?string $title = null,
        ?string $text = null,
        ?string $error = null,
    ): array {
        return [
            'ok' => $ok,
            'status' => $status,
            'url' => $url,
            'content_type' => $contentType,
            'title' => $title,
            'text' => $text,
            'error' => $error,
        ];
    }
}
