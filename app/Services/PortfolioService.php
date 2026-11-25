<?php

namespace App\Services;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\Presentation;
use App\Models\User;
use App\Support\TrackExperience;
use Illuminate\Support\Str;

/**
 * A fellow's public portfolio: only work reviewed and approved by the I-NNOVA
 * team (curriculum projects and Brand posts, presentations, certificates),
 * the skill map and practice activity.
 */
class PortfolioService
{
    public function __construct(
        protected SkillMapService $skills,
        protected SkillCertificateService $certificates,
        protected PracticeService $practice,
    ) {}

    public function build(User $fellow): array
    {
        $fellowTrack = $fellow->primaryTrack ?? $fellow->fellowTracks()->with('track')->first();
        $track = $fellowTrack?->track;
        $experience = TrackExperience::for($track);

        $completed = FellowCurriculumProgress::with('curriculumActivity.milestone')
            ->where('fellow_id', $fellow->id)
            ->where('status', CurriculumStatus::COMPLETED->value)
            ->whereHas('curriculumActivity', fn ($q) => $q->whereIn('type', ['project', 'case_study', 'blog_post']))
            ->latest('completed_at')
            ->get();

        $item = fn (FellowCurriculumProgress $p) => [
            'title' => $p->curriculumActivity->title,
            'chapter' => $p->curriculumActivity->milestone?->story['chapter'] ?? $p->curriculumActivity->milestone?->title,
            'summary' => Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(\Illuminate\Support\Str::markdown((string) $p->curriculumActivity->description, ['html_input' => 'strip'])), ENT_QUOTES | ENT_HTML5))), 220),
            'url' => $this->safeUrl($p->evidence_url),
            'date' => $p->completed_at,
            'score' => $p->score_awarded ? (int) $p->score_awarded : null,
        ];

        $isPost = fn ($p) => ($p->curriculumActivity->type?->value ?? (string) $p->curriculumActivity->type) === 'blog_post';

        return [
            'fellow' => $fellow,
            'fellowTrack' => $fellowTrack,
            'track' => $track,
            'experience' => $experience,
            'levelTitle' => $experience?->levelTitle($fellowTrack?->tier) ?? ucfirst((string) $fellowTrack?->tier),
            'skills' => $track ? $this->skills->forFellow($fellow, $track) : [],
            'certificates' => $this->certificates->forFellow($fellow),
            'projects' => $completed->reject($isPost)->take(12)->map($item)->values(),
            'videos' => $completed->filter(fn ($p) => $p->show_tell_video)->take(8)->map(fn ($p) => $item($p) + ['video' => $p->show_tell_video])->values(),
            'posts' => $completed->filter($isPost)->filter(fn ($p) => $this->safeUrl($p->evidence_url))->take(8)->map($item)->values(),
            'presentations' => Presentation::with('track')->where('fellow_id', $fellow->id)->where('status', Presentation::STATUS_APPROVED)->latest('presented_at')->get(),
            'practice' => $track ? $this->practice->stats($fellow, $track) : null,
            'briefs' => \App\Models\BriefSubmission::with('brief')->where('fellow_id', $fellow->id)->whereIn('status', ['shortlisted', 'winner'])->latest('reviewed_at')->get(),
            'links' => array_filter([
                'LinkedIn' => $this->safeUrl($fellow->linkedin_url),
                'GitHub' => $this->safeUrl($fellow->github_url),
            ]),
        ];
    }

    protected function safeUrl(?string $url): ?string
    {
        return $url && preg_match('#^https?://#i', $url) ? $url : null;
    }
}
