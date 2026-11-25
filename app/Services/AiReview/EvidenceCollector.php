<?php

namespace App\Services\AiReview;

use App\Models\FellowCurriculumProgress;
use Illuminate\Support\Facades\Storage;

/**
 * Gathers everything the reviewer needs about one submission: the activity
 * brief and rubric, what the fellow wrote, and the content behind their
 * links and files. Anything that cannot be read is listed under `unread`,
 * so the decision rules can send the submission to a human instead of
 * judging work that was never seen.
 */
class EvidenceCollector
{
    public const MAX_TEXT_CHARS = 15_000;
    public const MAX_LINKS = 3;
    public const MAX_REPOS = 2;
    public const MAX_FILE_BYTES = 8_000_000;

    protected const TEXT_EXTENSIONS = [
        'txt', 'md', 'markdown', 'csv', 'json', 'xml', 'yml', 'yaml', 'html', 'htm', 'css', 'scss', 'js', 'jsx', 'ts', 'tsx',
        'php', 'py', 'java', 'kt', 'c', 'h', 'cpp', 'cs', 'go', 'rb', 'rs', 'sql', 'sh', 'ps1', 'tf', 'hcl', 'r', 'ini', 'toml', 'log',
    ];

    protected const VIDEO_HOSTS = ['youtube.com', 'youtu.be', 'vimeo.com', 'loom.com', 'tiktok.com'];
    protected const BLOCKED_HOSTS = ['linkedin.com', 'facebook.com', 'instagram.com', 'x.com', 'twitter.com'];

    public function __construct(
        protected GitHubInspector $github,
        protected SafeUrlFetcher $fetcher,
    ) {
    }

    public function collect(FellowCurriculumProgress $progress): array
    {
        $progress->loadMissing(['curriculumActivity.milestone', 'curriculumActivity.track', 'fellow']);
        $activity = $progress->curriculumActivity;
        $fellow = $progress->fellow;
        $evidence = is_array($progress->evidence) ? $progress->evidence : [];

        $package = [
            'activity' => [
                'title' => $activity->title,
                'track' => $activity->track?->name,
                'milestone' => $activity->milestone?->title,
                'type' => $activity->type?->value ?? (string) $activity->type,
                'difficulty' => $activity->difficulty_level?->value ?? (string) $activity->difficulty_level,
                'points' => (int) $activity->points,
                'description' => (string) $activity->description,
                'instructions' => (string) $activity->instructions,
                'reviewer_guide' => (string) $activity->reviewer_guide,
                'evidence_requirements' => array_values((array) ($activity->evidence_requirements ?? [])),
                'rubric' => $this->rubric($activity->evaluation_rubric),
            ],
            'submission' => [
                'attempt' => (int) ($progress->attempt_number ?: 1),
                'started_at' => $progress->started_at?->toIso8601String(),
                'submitted_at' => $progress->submitted_at?->toIso8601String(),
                'minutes_from_start_to_submit' => $progress->started_at && $progress->submitted_at
                    ? (int) $progress->started_at->diffInMinutes($progress->submitted_at) : null,
                'late' => (bool) $progress->late_penalty_applied,
                'url' => $evidence['evidence_url'] ?? null,
                'text' => $evidence['evidence_text'] ?? null,
                'reflection' => $progress->submission_notes,
                'file_names' => array_map('basename', (array) ($evidence['evidence_files'] ?? [])),
                'ai_assistance' => $evidence['ai_assistance'] ?? null,
                // Backend projects: the automatic API contract check (IKS called the deployed API).
                'api_contract_check' => !empty($evidence['contract']['results']) ? [
                    'passed' => $evidence['contract']['passed'], 'total' => $evidence['contract']['total'],
                    'failed' => array_values(array_map(fn ($r) => $r['name'] . ': ' . $r['message'], array_filter($evidence['contract']['results'], fn ($r) => !$r['ok']))),
                ] : null,
                'show_tell_video' => !empty($evidence['video']['url']) ? ['url' => $evidence['video']['url'], 'title' => $evidence['video']['title'] ?? null] : null,
            ],
            'github' => [],
            'links' => [],
            'files' => [],
            'unread' => [],
        ];

        $this->collectLinks($package, $progress, $fellow?->github_url);
        $this->collectFiles($package, (array) ($evidence['evidence_files'] ?? []));

        return $package;
    }

    /**
     * Normalised rubric: [{criterion, description, weight}], weights summing to 100.
     * Activities without a rubric get a general one.
     */
    public function rubric(mixed $rubric): array
    {
        $items = [];
        foreach ((array) $rubric as $i => $row) {
            if (is_array($row)) {
                $items[] = [
                    'criterion' => (string) ($row['criterion'] ?? $row['name'] ?? 'Criterion ' . ($i + 1)),
                    'description' => (string) ($row['description'] ?? ''),
                    'weight' => (float) ($row['weight'] ?? 0),
                ];
            }
        }

        if (empty($items)) {
            $items = [
                ['criterion' => 'Requirements', 'description' => 'Did the fellow do what the activity asked, completely?', 'weight' => 60],
                ['criterion' => 'Quality & understanding', 'description' => 'Is the work correct, thoughtful and clearly their own understanding?', 'weight' => 40],
            ];
        }

        $total = array_sum(array_column($items, 'weight'));
        foreach ($items as &$item) {
            $item['weight'] = $total > 0 ? round($item['weight'] * 100 / $total, 2) : round(100 / count($items), 2);
        }

        return $items;
    }

    protected function collectLinks(array &$package, FellowCurriculumProgress $progress, ?string $fellowGithub): array
    {
        $urls = [];
        if ($package['submission']['url']) {
            $urls[] = $package['submission']['url'];
        }
        foreach ([$package['submission']['text'], $package['submission']['reflection']] as $text) {
            preg_match_all('#https?://[^\s<>"\')\]]+#i', (string) $text, $m);
            foreach ($m[0] as $u) {
                $urls[] = rtrim($u, '.,;:');
            }
        }
        $urls = array_values(array_unique($urls));

        $username = GitHubInspector::usernameFromProfile($fellowGithub);
        $repos = 0;
        $links = 0;

        foreach ($urls as $url) {
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            $host = preg_replace('/^www\./', '', $host);

            if (in_array($host, ['github.com', 'gist.github.com'], true)) {
                if ($repos++ >= self::MAX_REPOS) {
                    continue;
                }
                $result = $this->github->inspect($url, $progress->started_at, $username);
                $package['github'][] = $result;
                if (!($result['ok'] ?? false)) {
                    $package['unread'][] = ['item' => $url, 'reason' => $result['error'] ?? 'GitHub could not be read.'];
                }
                continue;
            }

            if ($this->hostIn($host, self::VIDEO_HOSTS)) {
                $package['unread'][] = ['item' => $url, 'reason' => 'Videos are not watched by the AI reviewer.'];
                continue;
            }

            if ($this->hostIn($host, self::BLOCKED_HOSTS)) {
                $package['unread'][] = ['item' => $url, 'reason' => 'This site blocks automatic reading (social network).'];
                continue;
            }

            if ($links++ >= self::MAX_LINKS) {
                continue;
            }

            $fetchUrl = $this->googleDocExport($url) ?? $url;
            $page = $this->fetcher->fetch($fetchUrl);
            $page['url'] = $url;
            if ($page['text'] !== null) {
                $page['text'] = mb_substr($page['text'], 0, self::MAX_TEXT_CHARS);
            }
            $package['links'][] = $page;

            if (!$page['ok'] || $page['text'] === null || trim($page['text']) === '') {
                $package['unread'][] = ['item' => $url, 'reason' => $page['error'] ?? 'The page has no readable text.'];
            }
        }

        return $package;
    }

    /**
     * A shared Google Doc can be read as plain text through its export link.
     */
    protected function googleDocExport(string $url): ?string
    {
        if (preg_match('#https://docs\.google\.com/document/d/([A-Za-z0-9_-]+)#', $url, $m)) {
            return "https://docs.google.com/document/d/{$m[1]}/export?format=txt";
        }

        return null;
    }

    protected function collectFiles(array &$package, array $paths): void
    {
        $disk = Storage::disk(config('filesystems.default'));

        foreach (array_slice($paths, 0, 5) as $path) {
            $name = basename($path);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            try {
                if (!$disk->exists($path)) {
                    $package['unread'][] = ['item' => $name, 'reason' => 'File not found in storage.'];
                    continue;
                }
                if ($disk->size($path) > self::MAX_FILE_BYTES) {
                    $package['unread'][] = ['item' => $name, 'reason' => 'File too large to read.'];
                    continue;
                }

                $text = match (true) {
                    $ext === 'ipynb' => NotebookReader::toText($disk->get($path)),
                    $ext === 'pdf' => $this->pdfText($disk->get($path)),
                    in_array($ext, ['docx', 'pptx'], true) => $this->officeText($disk->get($path), $ext),
                    in_array($ext, self::TEXT_EXTENSIONS, true) => $disk->get($path),
                    default => null,
                };
            } catch (\Throwable $e) {
                $text = null;
                $package['unread'][] = ['item' => $name, 'reason' => 'File could not be read: ' . mb_substr($e->getMessage(), 0, 120)];
                continue;
            }

            if ($text === null) {
                $kind = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true) ? 'Images' : (in_array($ext, ['mp4', 'mov', 'webm', 'avi'], true) ? 'Videos' : "'.{$ext}' files");
                $package['unread'][] = ['item' => $name, 'reason' => "{$kind} are not read by the AI reviewer yet."];
                continue;
            }

            $text = trim(preg_replace("/[ \t]+/", ' ', $text) ?? $text);
            if ($text === '') {
                $package['unread'][] = ['item' => $name, 'reason' => 'No text could be extracted (scanned document?).'];
                continue;
            }

            $package['files'][] = [
                'name' => $name,
                'chars' => mb_strlen($text),
                'truncated' => mb_strlen($text) > self::MAX_TEXT_CHARS,
                'content' => mb_substr($text, 0, self::MAX_TEXT_CHARS),
            ];
        }
    }

    protected function pdfText(string $bytes): string
    {
        $parser = new \Smalot\PdfParser\Parser();

        return $parser->parseContent($bytes)->getText();
    }

    /**
     * Text of a Word document or PowerPoint deck (both are zipped XML).
     */
    protected function officeText(string $bytes, string $ext): ?string
    {
        if (!class_exists(\ZipArchive::class)) {
            return null;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'iks');
        file_put_contents($tmp, $bytes);
        $zip = new \ZipArchive();

        try {
            if ($zip->open($tmp) !== true) {
                return null;
            }

            $parts = [];
            if ($ext === 'docx') {
                $parts[] = $zip->getFromName('word/document.xml') ?: '';
            } else {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (preg_match('#^ppt/slides/slide(\d+)\.xml$#', $name, $m)) {
                        $parts[(int) $m[1]] = "[slide {$m[1]}]\n" . $zip->getFromIndex($i);
                    }
                }
                ksort($parts);
            }
            $zip->close();

            $xml = implode("\n", $parts);
            $xml = preg_replace('#</w:p>|</a:p>#', "\n", $xml) ?? $xml;

            return html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1);
        } finally {
            @unlink($tmp);
        }
    }

    protected function hostIn(string $host, array $domains): bool
    {
        foreach ($domains as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return true;
            }
        }

        return false;
    }
}
