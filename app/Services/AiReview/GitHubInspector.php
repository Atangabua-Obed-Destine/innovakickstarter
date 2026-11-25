<?php

namespace App\Services\AiReview;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * Reads what a fellow submitted on GitHub: the code the reviewer needs, and
 * the history that shows whether the work is theirs and was done for this
 * activity (fork, commits during the activity, commit authors).
 */
class GitHubInspector
{
    /** Total characters of file content passed to the reviewer. */
    public const CONTENT_BUDGET = 60_000;
    public const MAX_FILE_CHARS = 15_000;
    public const MAX_FILES = 20;

    protected const SKIP_DIRS = ['node_modules', 'vendor', 'dist', 'build', '.git', '.next', '__pycache__', 'venv', '.venv', 'env', 'coverage', '.idea', '.vscode', 'target', 'bin', 'obj'];
    protected const SKIP_FILES = ['package-lock.json', 'yarn.lock', 'pnpm-lock.yaml', 'composer.lock', 'poetry.lock', 'Pipfile.lock', 'Gemfile.lock', 'cargo.lock'];
    protected const CODE_EXTENSIONS = [
        'html', 'htm', 'css', 'scss', 'js', 'jsx', 'ts', 'tsx', 'vue', 'svelte', 'php', 'py', 'ipynb', 'java', 'kt', 'c', 'h', 'cpp', 'cs',
        'go', 'rb', 'rs', 'swift', 'dart', 'sql', 'sh', 'bash', 'ps1', 'tf', 'hcl', 'yml', 'yaml', 'json', 'toml', 'ini', 'md', 'txt',
        'r', 'rmd', 'dockerfile', 'conf', 'env.example', 'xml', 'gradle', 'blade.php',
    ];

    /**
     * Find GitHub links in a piece of text.
     *
     * @return string[]
     */
    public static function findLinks(?string $text): array
    {
        if (!$text) {
            return [];
        }

        preg_match_all('#https?://(?:gist\.)?github\.com/[^\s<>"\')\]]+#i', $text, $m);

        return array_values(array_unique(array_map(fn ($u) => rtrim($u, '.,;:'), $m[0])));
    }

    /**
     * @return array{kind: string, owner: string, repo?: string, ref?: ?string, path?: ?string, sha?: string, gist_id?: string}|null
     */
    public static function parse(string $url): ?array
    {
        $parts = parse_url(trim($url));
        $host = strtolower($parts['host'] ?? '');
        $segments = array_values(array_filter(explode('/', $parts['path'] ?? ''), fn ($s) => $s !== ''));

        if ($host === 'gist.github.com' && count($segments) >= 1) {
            return ['kind' => 'gist', 'owner' => count($segments) > 1 ? $segments[0] : '', 'gist_id' => end($segments)];
        }

        if (!in_array($host, ['github.com', 'www.github.com'], true) || count($segments) < 2) {
            return null;
        }

        $owner = $segments[0];
        $repo = preg_replace('/\.git$/', '', $segments[1]);

        if (($segments[2] ?? null) === 'commit' && isset($segments[3])) {
            return ['kind' => 'commit', 'owner' => $owner, 'repo' => $repo, 'sha' => $segments[3]];
        }

        if (in_array($segments[2] ?? null, ['tree', 'blob'], true) && isset($segments[3])) {
            return [
                'kind' => 'repo', 'owner' => $owner, 'repo' => $repo,
                'ref' => $segments[3], 'path' => implode('/', array_slice($segments, 4)) ?: null,
            ];
        }

        return ['kind' => 'repo', 'owner' => $owner, 'repo' => $repo, 'ref' => null, 'path' => null];
    }

    /**
     * GitHub username from a profile URL such as https://github.com/jane.
     */
    public static function usernameFromProfile(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $parsed = static::parse(rtrim($url, '/') . '/x');

        return $parsed['owner'] ?? null;
    }

    /**
     * Inspect a GitHub link.
     *
     * @param  ?CarbonInterface  $activityStartedAt  When the fellow started the activity.
     * @param  ?string  $fellowUsername  The fellow's GitHub username, when known.
     */
    public function inspect(string $url, ?CarbonInterface $activityStartedAt, ?string $fellowUsername): array
    {
        $target = static::parse($url);

        if (!$target) {
            return ['url' => $url, 'ok' => false, 'error' => 'Not a GitHub repository, gist or commit link.'];
        }

        try {
            return match ($target['kind']) {
                'gist' => $this->inspectGist($url, $target, $activityStartedAt, $fellowUsername),
                default => $this->inspectRepo($url, $target, $activityStartedAt, $fellowUsername),
            };
        } catch (\Throwable $e) {
            return ['url' => $url, 'ok' => false, 'error' => 'GitHub could not be read: ' . mb_substr($e->getMessage(), 0, 160)];
        }
    }

    protected function inspectRepo(string $url, array $target, ?CarbonInterface $startedAt, ?string $fellowUsername): array
    {
        $full = "{$target['owner']}/{$target['repo']}";
        $repo = $this->api("/repos/{$full}");

        if ($repo->status() === 404) {
            return [
                'url' => $url, 'ok' => false, 'repo' => $full, 'not_found' => true,
                'error' => 'The repository does not exist or is private. It must be public so it can be reviewed.',
            ];
        }
        $repo->throw();
        $info = $repo->json();

        $ref = $target['ref'] ?? $info['default_branch'] ?? 'main';

        // History
        $commitsResponse = $this->api("/repos/{$full}/commits", ['sha' => $ref, 'per_page' => 100]);
        $commits = $commitsResponse->successful() ? $commitsResponse->json() : [];
        $history = $this->summariseCommits($commits, $startedAt, $fellowUsername, $info['owner']['login'] ?? $target['owner']);

        // Files
        $tree = $this->api("/repos/{$full}/git/trees/{$ref}", ['recursive' => 1]);
        $paths = [];
        if ($tree->successful()) {
            foreach ($tree->json('tree') ?? [] as $node) {
                if (($node['type'] ?? '') === 'blob') {
                    $paths[] = ['path' => $node['path'], 'size' => (int) ($node['size'] ?? 0)];
                }
            }
        }

        $selected = $this->selectFiles($paths, $target['path'] ?? null);
        $files = [];
        $budget = self::CONTENT_BUDGET;
        foreach ($selected as $file) {
            if ($budget <= 0) {
                break;
            }
            $content = $this->rawFile($full, $file['path'], $ref);
            if ($content === null) {
                continue;
            }
            $content = $this->readable($file['path'], $content);
            $chunk = mb_substr($content, 0, min(self::MAX_FILE_CHARS, $budget));
            $budget -= mb_strlen($chunk);
            $files[] = [
                'path' => $file['path'],
                'chars' => mb_strlen($content),
                'truncated' => mb_strlen($chunk) < mb_strlen($content),
                'content' => $chunk,
            ];
        }

        return [
            'url' => $url,
            'ok' => true,
            'kind' => 'repo',
            'repo' => $full,
            'owner' => $info['owner']['login'] ?? $target['owner'],
            'ref' => $ref,
            'focus_path' => $target['path'] ?? null,
            'description' => $info['description'] ?? null,
            'is_fork' => (bool) ($info['fork'] ?? false),
            'fork_parent' => $info['parent']['full_name'] ?? null,
            'created_at' => $info['created_at'] ?? null,
            'pushed_at' => $info['pushed_at'] ?? null,
            'language' => $info['language'] ?? null,
            'file_count' => count($paths),
            'file_list' => array_slice(array_column($paths, 'path'), 0, 300),
            'files' => $files,
            'history' => $history,
        ];
    }

    protected function inspectGist(string $url, array $target, ?CarbonInterface $startedAt, ?string $fellowUsername): array
    {
        $response = $this->api("/gists/{$target['gist_id']}");

        if ($response->status() === 404) {
            return ['url' => $url, 'ok' => false, 'not_found' => true, 'error' => 'The gist does not exist or is secret and unreachable.'];
        }
        $response->throw();
        $gist = $response->json();

        $files = [];
        $budget = self::CONTENT_BUDGET;
        foreach ($gist['files'] ?? [] as $name => $file) {
            if ($budget <= 0) {
                break;
            }
            $content = $file['content'] ?? '';
            $chunk = mb_substr($this->readable($name, $content), 0, min(self::MAX_FILE_CHARS, $budget));
            $budget -= mb_strlen($chunk);
            $files[] = ['path' => $name, 'chars' => mb_strlen($content), 'truncated' => (bool) ($file['truncated'] ?? false), 'content' => $chunk];
        }

        $owner = $gist['owner']['login'] ?? $target['owner'];
        $created = isset($gist['created_at']) ? Carbon::parse($gist['created_at']) : null;
        $updated = isset($gist['updated_at']) ? Carbon::parse($gist['updated_at']) : null;

        return [
            'url' => $url,
            'ok' => true,
            'kind' => 'gist',
            'owner' => $owner,
            'description' => $gist['description'] ?? null,
            'created_at' => $gist['created_at'] ?? null,
            'updated_at' => $gist['updated_at'] ?? null,
            'files' => $files,
            'history' => [
                'commits_total' => count($gist['history'] ?? []),
                'activity_started_at' => $startedAt?->toIso8601String(),
                'changed_during_activity' => $startedAt && $updated ? $updated->gte($startedAt) : null,
                'created_before_activity' => $startedAt && $created ? $created->lt($startedAt) : null,
                'owner_is_fellow' => $fellowUsername ? strcasecmp($owner, $fellowUsername) === 0 : null,
            ],
        ];
    }

    /**
     * Who made the commits, and when, relative to the activity.
     */
    protected function summariseCommits(array $commits, ?CarbonInterface $startedAt, ?string $fellowUsername, string $repoOwner): array
    {
        $authors = [];
        $during = 0;
        $duringByOthers = 0;
        $dates = [];
        $identity = strtolower($fellowUsername ?: $repoOwner);

        foreach ($commits as $commit) {
            $login = strtolower($commit['author']['login'] ?? $commit['commit']['author']['name'] ?? 'unknown');
            $date = isset($commit['commit']['author']['date']) ? Carbon::parse($commit['commit']['author']['date']) : null;
            $authors[$login] = ($authors[$login] ?? 0) + 1;
            if ($date) {
                $dates[] = $date;
            }
            if ($startedAt && $date && $date->gte($startedAt->copy()->subHours(6))) {
                $during++;
                if ($login !== $identity) {
                    $duringByOthers++;
                }
            }
        }

        usort($dates, fn ($a, $b) => $a <=> $b);
        arsort($authors);

        return [
            'commits_total' => count($commits),
            'commits_capped' => count($commits) >= 100,
            'first_commit_at' => isset($dates[0]) ? $dates[0]->toIso8601String() : null,
            'last_commit_at' => $dates ? end($dates)->toIso8601String() : null,
            'activity_started_at' => $startedAt?->toIso8601String(),
            'commits_during_activity' => $startedAt ? $during : null,
            'commits_during_activity_by_others' => $startedAt ? $duringByOthers : null,
            'authors' => $authors,
            'fellow_username' => $fellowUsername,
            'owner_is_fellow' => $fellowUsername ? strcasecmp($repoOwner, $fellowUsername) === 0 : null,
        ];
    }

    /**
     * Pick the files a reviewer should read: the linked path first, then the
     * README, then source files, skipping dependencies and build output.
     */
    protected function selectFiles(array $paths, ?string $focusPath): array
    {
        $candidates = array_filter($paths, function ($file) {
            $path = $file['path'];
            foreach (explode('/', $path) as $segment) {
                if (in_array($segment, self::SKIP_DIRS, true)) {
                    return false;
                }
            }
            $name = strtolower(basename($path));
            if (in_array($name, array_map('strtolower', self::SKIP_FILES), true) || str_contains($name, '.min.')) {
                return false;
            }
            if ($file['size'] > 400_000) {
                return false;
            }
            $ext = str_ends_with($name, '.blade.php') ? 'blade.php' : pathinfo($name, PATHINFO_EXTENSION);

            return in_array($ext, self::CODE_EXTENSIONS, true) || $name === 'dockerfile' || $name === 'makefile';
        });

        usort($candidates, function ($a, $b) use ($focusPath) {
            return $this->priority($a['path'], $focusPath) <=> $this->priority($b['path'], $focusPath)
                ?: $a['size'] <=> $b['size'];
        });

        return array_slice(array_values($candidates), 0, self::MAX_FILES);
    }

    protected function priority(string $path, ?string $focusPath): int
    {
        if ($focusPath && ($path === $focusPath || str_starts_with($path, rtrim($focusPath, '/') . '/'))) {
            return 0;
        }
        if (preg_match('#^readme(\.|$)#i', basename($path)) && !str_contains($path, '/')) {
            return 1;
        }
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (in_array($ext, ['json', 'yml', 'yaml', 'toml', 'ini', 'xml', 'txt', 'md'], true)) {
            return 4;
        }

        return substr_count($path, '/') + 2;
    }

    /**
     * Jupyter notebooks are JSON; keep the markdown and code cells only.
     */
    protected function readable(string $path, string $content): string
    {
        if (str_ends_with(strtolower($path), '.ipynb')) {
            return NotebookReader::toText($content);
        }

        return $content;
    }

    protected function rawFile(string $full, string $path, string $ref): ?string
    {
        $encoded = implode('/', array_map('rawurlencode', explode('/', $path)));
        $response = $this->client('application/vnd.github.raw')->get("https://api.github.com/repos/{$full}/contents/{$encoded}", ['ref' => $ref]);

        return $response->successful() ? $response->body() : null;
    }

    protected function api(string $path, array $query = []): \Illuminate\Http\Client\Response
    {
        return $this->client()->get('https://api.github.com' . $path, $query);
    }

    protected function client(string $accept = 'application/vnd.github+json'): \Illuminate\Http\Client\PendingRequest
    {
        $request = Http::withHeaders([
            'Accept' => $accept,
            'User-Agent' => 'IKS-Reviewer',
            'X-GitHub-Api-Version' => '2022-11-28',
        ])->timeout(20);

        if ($token = config('services.github.token')) {
            $request = $request->withToken($token);
        }

        return $request;
    }
}
