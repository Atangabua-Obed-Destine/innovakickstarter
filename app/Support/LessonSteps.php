<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Turns lesson instructions (markdown) into sections a fellow can work through:
 * the first numbered list in a section becomes checkable steps, and "- [ ]"
 * lists become a checklist. Returns null when the text has neither, so the
 * page falls back to plain markdown.
 *
 * Step keys ("steps:3", "self-check:2") are what fellows' ticks are saved under.
 */
class LessonSteps
{
    /**
     * @return array<int, array{key: string, title: ?string, kind: string, intro: HtmlString, items: array<int, array{key: string, n: int, html: HtmlString, plain: string}>, outro: HtmlString}>|null
     */
    public static function parse(?string $markdown): ?array
    {
        $lines = preg_split('/\R/', (string) $markdown);
        $raw = [];
        $current = ['title' => null, 'lines' => []];
        $inFence = false;
        foreach ($lines as $line) {
            if (preg_match('/^\s*```/', $line)) {
                $inFence = !$inFence;
            }
            if (!$inFence && preg_match('/^#{2,3}\s+(.+?)\s*#*\s*$/', $line, $m)) {
                $raw[] = $current;
                $current = ['title' => trim($m[1]), 'lines' => []];
                continue;
            }
            $current['lines'][] = $line;
        }
        $raw[] = $current;

        $sections = [];
        $usedKeys = [];
        foreach ($raw as $r) {
            if ($r['title'] === null && trim(implode("\n", $r['lines'])) === '') {
                continue;
            }
            $key = Str::slug($r['title'] ?? 'intro') ?: 'section';
            $base = $key;
            for ($i = 2; isset($usedKeys[$key]); $i++) {
                $key = "{$base}-{$i}";
            }
            $usedKeys[$key] = true;
            $sections[] = self::section($key, $r['title'], $r['lines']);
        }

        $interactive = array_filter($sections, fn ($s) => $s['kind'] !== 'text');

        return $interactive ? $sections : null;
    }

    /** All step keys, to keep saved ticks valid. */
    public static function keys(?array $sections): array
    {
        return collect($sections ?? [])->flatMap(fn ($s) => array_column($s['items'], 'key'))->values()->all();
    }

    protected static function section(string $key, ?string $title, array $lines): array
    {
        // "What to submit" is a list of deliverables, not things to tick off while working.
        $listable = !preg_match('/what to submit|submission/i', (string) $title);
        $intro = [];
        $items = [];
        $outro = [];
        $kind = 'text';
        $leads = []; // text between two lists of the same kind (e.g. "**Part B**"), shown above the next item
        $state = 'intro';
        $inFence = false;

        foreach ($lines as $line) {
            $fence = (bool) preg_match('/^\s*```/', $line);
            if (!$inFence && $listable) {
                $isStep = preg_match('/^(\d+)[.)]\s+(.*)$/', $line, $sm);
                $isCheck = preg_match('/^[-*]\s+\[[ xX]?\]\s+(.*)$/', $line, $cm);
                $matches = ($isStep && $kind !== 'checklist') || ($isCheck && $kind !== 'steps');
                // A later list of the same kind continues the steps; the text before it becomes its lead.
                if ($matches && $state === 'outro') {
                    $leads[count($items)] = implode("\n", $outro);
                    $outro = [];
                }
                if ($matches) {
                    $kind = $isStep ? 'steps' : 'checklist';
                    $items[] = [$isStep ? $sm[2] : $cm[1]];
                    $state = 'items';
                    continue;
                }
                if ($state === 'items') {
                    if (trim($line) === '') {
                        continue; // blank lines between items
                    }
                    if (preg_match('/^(\s{2,}|\t)/', $line)) {
                        $items[count($items) - 1][] = $line; // indented: part of the item
                        continue;
                    }
                    $state = 'outro';
                }
            }
            if ($fence) {
                $inFence = !$inFence;
            }
            if ($state === 'items' && $inFence) {
                $items[count($items) - 1][] = $line;
                continue;
            }
            $state === 'intro' ? $intro[] = $line : $outro[] = $line;
        }

        $prefix = $kind === 'checklist' ? 'check' : 'step';
        $built = [];
        foreach ($items as $i => $itemLines) {
            $md = self::dedent($itemLines);
            $built[] = ['key' => "{$key}:" . ($i + 1), 'n' => $i + 1, 'html' => LessonMarkdown::render($md), 'plain' => LessonMarkdown::plain($md, 400), 'kind' => $prefix,
                'lead' => isset($leads[$i]) && trim($leads[$i]) !== '' ? LessonMarkdown::render($leads[$i]) : null];
        }

        return [
            'key' => $key, 'title' => $title, 'kind' => $kind,
            'intro' => LessonMarkdown::render(implode("\n", $intro)),
            'items' => $built,
            'outro' => LessonMarkdown::render(implode("\n", $outro)),
        ];
    }

    protected static function dedent(array $lines): string
    {
        $first = array_shift($lines);
        $rest = array_map(fn ($l) => preg_replace('/^(\s{2,4}|\t)/', '', $l), $lines);

        return trim(implode("\n", array_merge([$first], $rest)));
    }
}
