<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * A very small, safe markdown renderer for interview content and AI text:
 * fenced code blocks (```lang ... ```) become escaped <pre><code>, **bold** and
 * `inline code` are kept, line breaks are preserved. Everything else is escaped:
 * no raw HTML from the AI or the content files ever reaches the page.
 */
class SafeMarkdown
{
    public static function render(?string $text): HtmlString
    {
        $text = str_replace("\r\n", "\n", (string) $text);
        $parts = preg_split('/```([A-Za-z0-9_+\-]*)[ \t]*\n?(.*?)(?:```|$)/s', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $html = '';
        for ($i = 0; $i < count($parts); $i += 3) {
            $html .= self::inline($parts[$i]);
            if (isset($parts[$i + 2])) {
                $lang = $parts[$i + 1] !== '' ? ' data-lang="' . e($parts[$i + 1]) . '"' : '';
                $html .= '<pre class="iks-code"' . $lang . ' style="white-space: pre-wrap; overflow-x: auto; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 13px; background: #0f172a; border-radius: 8px; padding: 10px; margin: 6px 0; color: #cbd5e1;"><code>'
                    . e(rtrim($parts[$i + 2], "\n")) . '</code></pre>';
            }
        }

        return new HtmlString($html);
    }

    /** The code inside the fences (for an editor), or the text itself when it has none. */
    public static function code(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return $text;
        }
        if (preg_match_all('/```[A-Za-z0-9_+\-]*[ \t]*\n?(.*?)```/s', $text, $m) && $m[1]) {
            return implode("\n\n", array_map(fn ($c) => rtrim($c, "\n"), $m[1]));
        }

        return $text;
    }

    protected static function inline(string $text): string
    {
        if ($text === '') {
            return '';
        }
        $html = e($text);
        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
        $html = preg_replace('/`([^`\n]+)`/', '<code style="background:#1e293b;border-radius:4px;padding:0 4px;">$1</code>', $html);

        return nl2br(trim($html, "\n"), false);
    }
}
