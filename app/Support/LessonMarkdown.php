<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Lesson text (activity descriptions and instructions) as safe HTML: Markdown
 * is rendered, any HTML typed into the text is escaped, unsafe links are
 * dropped, and single line breaks are kept so older plain-text lessons still
 * read the same.
 */
class LessonMarkdown
{
    public static function render(?string $text): HtmlString
    {
        if (trim((string) $text) === '') {
            return new HtmlString('');
        }

        $html = Str::markdown((string) $text, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'renderer' => ['soft_break' => "<br>\n"],
        ]);

        // Links open in a new tab and never pass the session to other sites.
        $html = preg_replace('/<a href="/', '<a target="_blank" rel="noopener noreferrer nofollow" href="', $html);

        return new HtmlString($html);
    }

    /** Plain-text preview (for cards and lists). */
    public static function plain(?string $text, int $limit = 120): string
    {
        return Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(Str::markdown((string) $text, ['html_input' => 'strip'])), ENT_QUOTES | ENT_HTML5))), $limit);
    }
}
