<?php

namespace App\Services;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FoundationArea;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Connects Base Camp (foundations: typing, computer, maths, English, spreadsheets) to the
 * curriculum. Each lesson needs some basic comprehension: this works out which foundation
 * areas a lesson leans on (from its skill tags, its type and the words in its instructions),
 * so the lesson can say "before you start, refresh these" to a fellow whose foundations are
 * not ready yet, and Base Camp can say "needed in Chapter N".
 */
class FoundationLinkService
{
    /** Skill tag => [area => [lesson keys]]. Lesson keys are the Base Camp lessons that help most. */
    public const BY_SKILL = [
        'python' => ['computer' => ['computer-files-and-folders', 'computer-downloads-and-zip'], 'typing' => []],
        'pandas' => ['computer' => ['computer-files-and-folders', 'computer-downloads-and-zip'], 'spreadsheets' => ['spreadsheets-rows-columns-cells'], 'typing' => []],
        'statistics' => ['maths' => ['maths-averages-ratios', 'maths-percentages', 'maths-fractions-decimals-rounding']],
        'cleaning' => ['spreadsheets' => ['spreadsheets-rows-columns-cells', 'spreadsheets-sort-filter-freeze'], 'maths' => ['maths-fractions-decimals-rounding']],
        'visualization' => ['maths' => ['maths-percentages', 'maths-tables-negatives-order']],
        'sql' => ['spreadsheets' => ['spreadsheets-rows-columns-cells', 'spreadsheets-sort-filter-freeze'], 'typing' => []],
        'bi' => ['spreadsheets' => ['spreadsheets-formulas-sum-average', 'spreadsheets-min-max-count', 'spreadsheets-sort-filter-freeze'], 'maths' => ['maths-percentages']],
        'communication' => ['english' => ['english-clear-answers', 'english-professional-messages']],
        // Software Engineering
        'javascript' => ['computer' => ['computer-files-and-folders', 'computer-shortcuts-and-browser'], 'typing' => [], 'maths' => ['maths-percentages']],
        'html_css' => ['computer' => ['computer-files-and-folders', 'computer-shortcuts-and-browser'], 'typing' => []],
        'dom' => ['computer' => ['computer-shortcuts-and-browser'], 'typing' => []],
        'http_apis' => ['english' => ['english-reading-instructions', 'english-instruction-words'], 'computer' => ['computer-shortcuts-and-browser']],
        'git' => ['computer' => ['computer-files-and-folders', 'computer-downloads-and-zip'], 'english' => ['english-professional-messages']],
        'testing' => ['english' => ['english-instruction-words'], 'typing' => []],
        'backend' => ['computer' => ['computer-files-and-folders', 'computer-downloads-and-zip'], 'typing' => []],
        'frontend' => ['computer' => ['computer-files-and-folders'], 'typing' => []],
        'mobile' => ['computer' => ['computer-downloads-and-zip', 'computer-drive-and-safety'], 'typing' => []],
        'security' => ['computer' => ['computer-drive-and-safety']],
    ];

    /** Words in a lesson => [area, lesson key, why]. */
    public const BY_WORDS = [
        ['/\b(colab|google drive)\b/i', 'computer', 'computer-drive-and-safety', 'you open notebooks in Google Colab and Drive'],
        ['/\b(download|\.zip|unzip|upload)\b/i', 'computer', 'computer-downloads-and-zip', 'you download and upload files'],
        ['/\b(percent(age)?s?|%)/i', 'maths', 'maths-percentages', 'it works with percentages'],
        ['/\b(average|mean|median|ratio)s?\b/i', 'maths', 'maths-averages-ratios', 'it works with averages'],
        ['/\b(round(ed|ing)?|decimals?)\b/i', 'maths', 'maths-fractions-decimals-rounding', 'it rounds numbers and uses decimals'],
        ['/\b(google sheets|spreadsheet|excel|pivot)\b/i', 'spreadsheets', 'spreadsheets-formulas-sum-average', 'it uses a spreadsheet'],
        ['/\b(sort(ed|ing)?|filter(ed|ing)?)\b/i', 'spreadsheets', 'spreadsheets-sort-filter-freeze', 'you sort and filter data'],
    ];

    /** Why an area matters, by skill. */
    private const SKILL_WHY = [
        'python' => ['computer' => 'you save, find and open code files', 'typing' => 'you type code'],
        'pandas' => ['computer' => 'you load data files from folders', 'spreadsheets' => 'a DataFrame is a table of rows and columns', 'typing' => 'you type code'],
        'statistics' => ['maths' => 'statistics is built on averages, percentages and rounding'],
        'cleaning' => ['spreadsheets' => 'you fix rows and columns in a table', 'maths' => 'you round and check numbers'],
        'visualization' => ['maths' => 'charts show percentages and compare numbers'],
        'sql' => ['spreadsheets' => 'SQL tables are rows and columns you sort and filter', 'typing' => 'you type queries'],
        'bi' => ['spreadsheets' => 'dashboards use sums, averages, sorting and filters', 'maths' => 'dashboards show percentages'],
        'communication' => ['english' => 'you explain your work in clear written English'],
        'javascript' => ['computer' => 'you create, save and find code files', 'typing' => 'you type code all day', 'maths' => 'prices, discounts and totals use percentages'],
        'html_css' => ['computer' => 'a website is a folder of files', 'typing' => 'you type a lot of markup'],
        'dom' => ['computer' => 'you use the browser and its developer tools', 'typing' => 'you type code'],
        'http_apis' => ['english' => 'API documentation must be read carefully', 'computer' => 'you test addresses in the browser'],
        'git' => ['computer' => 'Git tracks the files and folders of your project', 'english' => 'commit messages and code reviews are short professional messages'],
        'testing' => ['english' => 'tests are written as precise instructions', 'typing' => 'you type code'],
        'backend' => ['computer' => 'you install tools and work with project folders', 'typing' => 'you type code'],
        'frontend' => ['computer' => 'a React app is a folder of files', 'typing' => 'you type code'],
        'mobile' => ['computer' => 'you download SDKs and install apps safely', 'typing' => 'you type code'],
        'security' => ['computer' => 'safe habits with passwords and accounts'],
    ];

    public function __construct(protected FoundationService $foundations) {}

    /**
     * Which foundation areas an activity leans on.
     *
     * @return array<string, array{lessons: string[], why: string[]}>
     */
    public function needs(TrackCurriculumActivity $activity): array
    {
        $out = [];
        $add = function (string $area, array $lessons, ?string $why) use (&$out) {
            $out[$area] ??= ['lessons' => [], 'why' => []];
            $out[$area]['lessons'] = array_values(array_unique([...$out[$area]['lessons'], ...$lessons]));
            if ($why && !in_array($why, $out[$area]['why'], true)) {
                $out[$area]['why'][] = $why;
            }
        };

        foreach ((array) ($activity->skills ?? []) as $skill) {
            foreach (self::BY_SKILL[$skill] ?? [] as $area => $lessons) {
                $add($area, $lessons, self::SKILL_WHY[$skill][$area] ?? null);
            }
        }
        $type = $activity->type?->value ?? (string) $activity->type;
        if (in_array($type, ['project', 'case_study', 'blog_post'], true)) {
            $add('english', ['english-clear-answers'], 'you explain your results in writing');
        }
        if ($activity->requires_video) {
            $add('english', ['english-clear-answers'], 'you explain your work on video');
        }
        $text = strip_tags((string) $activity->description . "\n" . (string) $activity->instructions);
        foreach (self::BY_WORDS as [$pattern, $area, $lesson, $why]) {
            if (preg_match($pattern, $text)) {
                $add($area, [$lesson], $why);
            }
        }
        if (mb_strlen($text) > 2500) {
            $add('english', ['english-reading-instructions', 'english-instruction-words'], 'the instructions are long and precise');
        }

        return $out;
    }

    /**
     * Foundation areas this lesson needs that the fellow has not made ready yet,
     * with the Base Camp lessons to refresh. Empty when Base Camp is off or all are ready.
     */
    public function forFellow(User $fellow, TrackCurriculumActivity $activity): array
    {
        $needs = $this->needs($activity);
        if (!$needs || FoundationArea::where('is_active', true)->doesntExist()) {
            return [];
        }
        $status = collect($this->foundations->status($fellow))->keyBy('key');
        $order = $status->keys()->flip();

        return collect($needs)
            ->filter(fn ($n, $key) => $status->has($key) && !$status[$key]['ready'])
            ->map(function ($n, $key) use ($status) {
                $s = $status[$key];
                $titles = collect($s['lessons'])->keyBy('key');
                $lessons = $key === 'typing'
                    ? [['title' => 'Typing trainer: 10 minutes a day', 'minutes' => 10, 'done' => false, 'url' => route('typing.index')]]
                    : collect($n['lessons'])->filter(fn ($l) => $titles->has($l))->map(fn ($l) => [
                        'title' => $titles[$l]['title'], 'minutes' => $titles[$l]['minutes'],
                        'done' => in_array($l, $s['lessons_done'], true),
                        'url' => route('base-camp.lesson', [$key, $l]),
                    ])->values()->all();

                return [
                    'key' => $key, 'title' => $s['title'], 'icon' => $s['icon'],
                    'checked' => $s['checked'], 'best' => $s['best'], 'target' => $s['target'],
                    'why' => $n['why'], 'lessons' => $lessons,
                ];
            })
            ->sortBy(fn ($a) => $order[$a['key']] ?? 99)
            ->values()->all();
    }

    /**
     * For Base Camp: area key => the next lesson (not yet completed) in the fellow's track that needs it.
     *
     * @return array<string, array{chapter: int, title: string, id: string}>
     */
    public function upcoming(User $fellow, Track $track): array
    {
        $map = Cache::remember("foundation-needs:{$track->id}:" . TrackCurriculumActivity::where('track_id', $track->id)->max('updated_at'), now()->addDay(), function () use ($track) {
            $chapters = $track->milestones()->where('is_active', true)->orderBy('sequence_order')->pluck('id')->flip();

            return TrackCurriculumActivity::where('track_id', $track->id)->where('is_active', true)->where('type', '!=', 'mock_interview')->orderBy('sequence_order')->get()
                ->map(fn ($a) => ['id' => $a->id, 'title' => $a->title, 'chapter' => (int) ($chapters[$a->milestone_id] ?? -1) + 1, 'areas' => array_keys($this->needs($a))])
                ->filter(fn ($a) => $a['chapter'] > 0)->values()->all();
        });

        $done = FellowCurriculumProgress::where('fellow_id', $fellow->id)->where('status', CurriculumStatus::COMPLETED->value)->pluck('curriculum_activity_id')->flip();
        $out = [];
        foreach ($map as $a) {
            if (isset($done[$a['id']])) {
                continue;
            }
            foreach ($a['areas'] as $area) {
                $out[$area] ??= ['chapter' => $a['chapter'], 'title' => $a['title'], 'id' => $a['id']];
            }
        }

        return $out;
    }
}
