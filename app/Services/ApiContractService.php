<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Runs an activity's API contract against a fellow's deployed API: whatever the language
 * (Laravel, Django, Express, Spring...), the API must behave as the contract says.
 *
 * Contract: {"checks":[{"name","method","path","body"?,"headers"?,"save"?,
 *   "expect":{"status": 200|[200,201], "has":["data.0.id"], "equals":{"data.0.currency":"FCFA"}, "min_count":{"data":20}}}]}
 * Variables: {{rand}} (random per run) and values saved by earlier checks ("save": {"token": "token"}).
 *
 * Safety: only public https hosts are called (private, loopback, link-local and reserved
 * addresses are refused, and the checked address is pinned for the whole run), redirects are not followed,
 * a sleeping free-tier host gets one 70 s wake-up call, each check times out after 15 s, and at most 20 checks run.
 */
class ApiContractService
{
    public const MAX_CHECKS = 20;
    public const WAKE_TIMEOUT = 70;
    public const CHECK_TIMEOUT = 15;

    /** @return array{ok: bool, error?: string, base?: string} */
    public function validateBaseUrl(?string $url): array
    {
        $url = rtrim(trim((string) $url), '/');
        $parts = parse_url($url);
        if (!$parts || strtolower($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            return ['ok' => false, 'error' => 'Give the https:// address where your API is deployed (for example https://nkwen-api.onrender.com).'];
        }
        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            return ['ok' => false, 'error' => 'Give only the base address, without a password, ?query or #fragment.'];
        }
        if (!$this->hostIsPublic($parts['host'])) {
            return ['ok' => false, 'error' => 'That address is not reachable from the internet. Deploy your API (or use a public tunnel) and use its public https address.'];
        }

        return ['ok' => true, 'base' => $url];
    }

    public function hostIsPublic(string $host): bool
    {
        return $this->resolvePublic($host) !== null;
    }

    /** The host's address if every address it resolves to is public, else null. */
    public function resolvePublic(string $host): ?string
    {
        $host = strtolower(trim($host, '[]'));
        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return null;
        }
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : array_merge(
            array_column(@dns_get_record($host, DNS_A) ?: [], 'ip'),
            array_column(@dns_get_record($host, DNS_AAAA) ?: [], 'ipv6'),
        );
        if (!$ips) {
            return null;
        }
        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return null;
            }
        }

        return $ips[0];
    }

    /**
     * @return array{ok: bool, error?: string, passed?: int, total?: int, results?: array, checked_at?: string, base?: string}
     */
    public function run(array $contract, ?string $baseUrl): array
    {
        $v = $this->validateBaseUrl($baseUrl);
        if (!$v['ok']) {
            return $v;
        }
        $base = $v['base'];
        $host = (string) parse_url($base, PHP_URL_HOST);
        $port = (int) (parse_url($base, PHP_URL_PORT) ?: 443);
        $ip = $this->resolvePublic($host);
        if ($ip === null) {
            return ['ok' => false, 'error' => 'That address is not reachable from the internet.'];
        }
        // Pin the checked address for every request of this run (no DNS rebinding to internal hosts).
        $pin = defined('CURLOPT_RESOLVE') && !filter_var($host, FILTER_VALIDATE_IP)
            ? ['curl' => [CURLOPT_RESOLVE => ["{$host}:{$port}:" . (str_contains($ip, ':') ? "[{$ip}]" : $ip)]]] : [];
        $checks = array_slice(array_values(array_filter((array) ($contract['checks'] ?? []), 'is_array')), 0, self::MAX_CHECKS);
        if (!$checks) {
            return ['ok' => false, 'error' => 'This activity has no API checks.'];
        }
        // Free hosts (e.g. Render) put idle apps to sleep and take about a minute to wake:
        // wake the API once with a long timeout, then run the checks with a normal one.
        @set_time_limit(self::WAKE_TIMEOUT + self::MAX_CHECKS * self::CHECK_TIMEOUT + 30);
        try {
            Http::withOptions(['allow_redirects' => false] + $pin)->timeout(self::WAKE_TIMEOUT)
                ->withHeaders(['User-Agent' => 'IKS-Contract-Check/1.0'])->get($base . '/');
        } catch (\Throwable $e) {
            // A failed wake-up is reported by the checks themselves.
        }
        $vars = ['rand' => strtolower(Str::random(8))];
        $results = [];
        foreach ($checks as $i => $c) {
            $name = (string) ($c['name'] ?? 'Check ' . ($i + 1));
            $method = strtoupper((string) ($c['method'] ?? 'GET'));
            $path = $this->fill((string) ($c['path'] ?? '/'), $vars);
            if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true) || !str_starts_with($path, '/')) {
                $results[] = ['name' => $name, 'ok' => false, 'message' => 'Bad check in the contract.'];
                continue;
            }
            $headers = array_map(fn ($h) => $this->fill((string) $h, $vars), (array) ($c['headers'] ?? []));
            $body = isset($c['body']) ? $this->fillDeep($c['body'], $vars) : null;
            $label = "{$method} {$path}";
            try {
                $req = Http::withOptions(['allow_redirects' => false] + $pin)->timeout(self::CHECK_TIMEOUT)->acceptJson()->asJson()
                    ->withHeaders(array_merge(['User-Agent' => 'IKS-Contract-Check/1.0'], $headers));
                $res = $req->send($method, $base . $path, $body !== null ? ['json' => $body] : []);
            } catch (\Throwable $e) {
                $results[] = ['name' => $name, 'request' => $label, 'ok' => false, 'message' => 'No answer from your API (timeout or connection refused).'];
                continue;
            }
            $json = $res->json();
            $problems = $this->compare($c['expect'] ?? [], $res->status(), $json);
            if (!$problems) {
                foreach ((array) ($c['save'] ?? []) as $var => $jsonPath) {
                    $value = is_array($json) ? Arr::get($json, (string) $jsonPath) : null;
                    if (is_scalar($value)) {
                        $vars[(string) $var] = (string) $value;
                    }
                }
            }
            $results[] = ['name' => $name, 'request' => $label, 'status' => $res->status(), 'ok' => !$problems, 'message' => $problems ? implode(' ', $problems) : 'OK'];
        }
        $passed = count(array_filter($results, fn ($r) => $r['ok']));

        return ['ok' => true, 'base' => $base, 'passed' => $passed, 'total' => count($checks), 'results' => $results, 'checked_at' => now()->toIso8601String()];
    }

    private function compare(array $expect, int $status, $json): array
    {
        $problems = [];
        $want = $expect['status'] ?? null;
        if ($want !== null && !in_array($status, array_map('intval', (array) $want), true)) {
            $problems[] = 'Expected status ' . implode(' or ', (array) $want) . ", got {$status}.";
        }
        if (($expect['has'] ?? []) || ($expect['equals'] ?? []) || ($expect['min_count'] ?? [])) {
            if (!is_array($json)) {
                return [...$problems, 'The response is not JSON.'];
            }
            foreach ((array) ($expect['has'] ?? []) as $p) {
                if (!Arr::has($json, (string) $p)) {
                    $problems[] = "Missing \"{$p}\" in the JSON.";
                }
            }
            foreach ((array) ($expect['equals'] ?? []) as $p => $value) {
                if (Arr::get($json, (string) $p) != $value) {
                    $problems[] = "\"{$p}\" should be " . json_encode($value) . '.';
                }
            }
            foreach ((array) ($expect['min_count'] ?? []) as $p => $n) {
                $list = Arr::get($json, (string) $p);
                if (!is_array($list) || count($list) < (int) $n) {
                    $problems[] = "\"{$p}\" should list at least {$n} items.";
                }
            }
        }

        return $problems;
    }

    private function fill(string $s, array $vars): string
    {
        return preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', fn ($m) => $vars[$m[1]] ?? $m[0], $s);
    }

    private function fillDeep($value, array $vars)
    {
        if (is_string($value)) {
            return $this->fill($value, $vars);
        }
        if (is_array($value)) {
            return array_map(fn ($v) => $this->fillDeep($v, $vars), $value);
        }

        return $value;
    }
}
