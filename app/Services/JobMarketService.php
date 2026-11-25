<?php

namespace App\Services;

use App\Enums\CurriculumStatus;
use App\Models\BriefSubmission;
use App\Models\DetectiveAnswer;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\InterviewSession;
use App\Models\Notification;
use App\Models\PracticeAttempt;
use App\Models\Presentation;
use App\Models\SkillCertificate;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackOrientation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Job-market readiness, built on real job adverts (database/data/job-market/{track}.json,
 * researched and cited). For each skill employers ask for, a fellow's proof comes only
 * from reviewed work (completed activities, certificates), never from self-rating.
 * Readiness for a role = how much of what its adverts ask for (weighted by how often
 * they ask) the fellow has proven. Skills the program doesn't teach are listed
 * separately with free resources, so the score never pretends to cover them.
 */
class JobMarketService
{
    public const OFFER_READINESS = 80;
    public const OFFER_STAGES = 4;

    public function data(?Track $track): ?array
    {
        if (!$track) {
            return null;
        }
        $file = database_path("data/job-market/{$track->slug}.json");
        if (!is_file($file)) {
            return null;
        }

        return Cache::remember('job-market:' . $track->slug . ':' . filemtime($file), 3600, fn () => json_decode((string) file_get_contents($file), true));
    }

    public function enrolment(User $fellow, Track $track): ?FellowTrack
    {
        return FellowTrack::where('fellow_id', $fellow->id)->where('track_id', $track->id)->first();
    }

    /** The role the fellow is aiming for (defaults to the first, entry-level role). */
    public function role(User $fellow, Track $track): ?array
    {
        $roles = collect($this->data($track)['roles'] ?? []);
        $chosen = $this->enrolment($fellow, $track)?->career_role;

        return $roles->firstWhere('key', $chosen) ?? $roles->first();
    }

    /** Required activities that teach a market skill (by track skill tags, activity types or title words). */
    protected function activitiesFor(array $map, Collection $activities): Collection
    {
        return $activities->filter(function ($a) use ($map) {
            $skills = (array) $a->skills;
            if (array_intersect($skills, $map['skills'] ?? [])) {
                return true;
            }
            if (in_array($a->type?->value, $map['types'] ?? [], true)) {
                return true;
            }
            foreach ($map['title_contains'] ?? [] as $word) {
                if (stripos($a->title, $word) !== false) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Proof per market skill: share of the activities that teach it that are completed
     * (a skill certificate counts as full proof). Skills the program doesn't teach have no proof.
     *
     * @return array<string, array{proof: float, done: int, total: int, certificate: bool, evidence: string, next: array}>
     */
    public function proof(User $fellow, Track $track): array
    {
        $data = $this->data($track) ?? [];
        $activities = TrackCurriculumActivity::where('track_id', $track->id)->where('is_active', true)->where('is_required', true)
            ->with('milestone:id,sequence_order')->get(['id', 'title', 'type', 'skills', 'milestone_id', 'sequence_order']);
        $progress = FellowCurriculumProgress::where('fellow_id', $fellow->id)->whereIn('curriculum_activity_id', $activities->pluck('id'))->get()->keyBy('curriculum_activity_id');
        $completed = $progress->filter(fn ($p) => $p->status === CurriculumStatus::COMPLETED)->keys()->flip();
        $certs = SkillCertificate::where('fellow_id', $fellow->id)->where('track_id', $track->id)->whereNull('revoked_at')->pluck('skill_key')->flip();
        $out = [];

        foreach ($data['skills'] ?? [] as $skill) {
            $map = $skill['curriculum'] ?? null;
            if (!$map) {
                continue;
            }
            if (!empty($map['github'])) {
                $github = $progress->filter(fn ($p) => $p->status === CurriculumStatus::COMPLETED && str_contains((string) ($p->evidence['evidence_url'] ?? ''), 'github.com'))->count();
                $out[$skill['key']] = ['proof' => min(1, $github / 3), 'done' => min(3, $github), 'total' => 3, 'certificate' => false,
                    'evidence' => "{$github} approved " . ($github === 1 ? 'submission' : 'submissions') . ' on GitHub (3 make it proven)', 'next' => []];
                continue;
            }
            $teach = $this->activitiesFor($map, $activities)->sortBy(fn ($a) => [$a->milestone?->sequence_order, $a->sequence_order]);
            $done = $teach->filter(fn ($a) => $completed->has($a->id));
            $hasCert = collect($map['skills'] ?? [])->contains(fn ($k) => $certs->has($k));
            $proof = $hasCert ? 1.0 : ($teach->isEmpty() ? 0.0 : $done->count() / $teach->count());
            $out[$skill['key']] = [
                'proof' => round($proof, 3), 'done' => $done->count(), 'total' => $teach->count(), 'certificate' => $hasCert,
                'evidence' => $hasCert ? 'Certificate earned' : "{$done->count()} of {$teach->count()} activities approved",
                'next' => $teach->reject(fn ($a) => $completed->has($a->id))->take(3)->map(fn ($a) => ['id' => $a->id, 'title' => $a->title, 'chapter' => $a->milestone?->sequence_order])->values()->all(),
            ];
        }

        return $out;
    }

    /** Readiness for a role: proven share of what its adverts ask for, among skills the program teaches. */
    public function readiness(User $fellow, Track $track, ?array $role = null, ?array $proof = null): array
    {
        $data = $this->data($track) ?? [];
        $role ??= $this->role($fellow, $track);
        $proof ??= $this->proof($fellow, $track);
        $freqs = $role['skills'] ?? null;
        $taught = [];
        $beyond = [];
        foreach ($data['skills'] ?? [] as $skill) {
            // A role's own frequencies win; a skill its adverts never mention counts 0 for that role.
            $f = (float) (is_array($freqs) ? ($freqs[$skill['key']] ?? 0) : ($skill['frequency'] ?? 0));
            if (($skill['kind'] ?? 'skill') === 'info' || $f <= 0) {
                continue;
            }
            if (isset($proof[$skill['key']])) {
                $taught[] = $skill + ['role_frequency' => $f, 'p' => $proof[$skill['key']]];
            } else {
                $beyond[] = $skill + ['role_frequency' => $f];
            }
        }
        $total = array_sum(array_column($taught, 'role_frequency'));
        $have = array_sum(array_map(fn ($s) => $s['role_frequency'] * $s['p']['proof'], $taught));
        usort($taught, fn ($a, $b) => $b['role_frequency'] <=> $a['role_frequency']);
        usort($beyond, fn ($a, $b) => $b['role_frequency'] <=> $a['role_frequency']);
        $gap = collect($taught)->sortByDesc(fn ($s) => $s['role_frequency'] * (1 - $s['p']['proof']))->first();

        return [
            'role' => $role,
            'percent' => $total > 0 ? (int) round($have / $total * 100) : 0,
            'taught' => $taught,
            'beyond' => $beyond,
            'gap' => $gap && $gap['p']['proof'] < 1 ? $gap : null,
            'proven' => count(array_filter($taught, fn ($s) => $s['p']['proof'] >= 0.999)),
        ];
    }

    /** The typical hiring loop, each stage with the fellow's evidence. */
    public function interviewLoop(User $fellow, Track $track, ?int $readiness = null): array
    {
        $data = $this->data($track) ?? [];
        $activityIds = TrackCurriculumActivity::where('track_id', $track->id)->pluck('id');
        $progress = FellowCurriculumProgress::where('fellow_id', $fellow->id)->whereIn('curriculum_activity_id', $activityIds)->with('curriculumActivity:id,title,type,interview_config')->get();
        $done = $progress->filter(fn ($p) => $p->status === CurriculumStatus::COMPLETED);
        $mock = fn (string $type, $rows) => $rows->filter(fn ($p) => $p->curriculumActivity?->type?->value === 'mock_interview' && (($p->curriculumActivity->interview_config['type'] ?? null) === $type));
        $sessions = InterviewSession::where('fellow_id', $fellow->id)->where('status', 'completed')->get(['type', 'score', 'overall_score']);
        $sessionPassed = fn (string $type) => $sessions->filter(fn ($s) => ($s->type?->value ?? $s->type) === $type && (float) ($s->overall_score ?? $s->score) >= 70)->count();
        $practiceSolved = PracticeAttempt::where('fellow_id', $fellow->id)->where('correct', true)->distinct('challenge_id')->count('challenge_id');
        $casesSolved = DetectiveAnswer::where('fellow_id', $fellow->id)->where('correct', true)->distinct('case_id')->count('case_id');
        $projects = $done->filter(fn ($p) => $p->curriculumActivity?->type?->value === 'project')->count();
        $caseStudies = $done->filter(fn ($p) => $p->curriculumActivity?->type?->value === 'case_study')->count();
        $briefs = BriefSubmission::where('fellow_id', $fellow->id)->whereIn('status', ['shortlisted', 'winner'])->count();
        $presentations = Presentation::where('fellow_id', $fellow->id)->get(['status']);
        $profileReady = filled($fellow->headline) && filled($fellow->bio);
        $oriented = TrackOrientation::where('fellow_id', $fellow->id)->where('track_id', $track->id)->whereNotNull('completed_at')->exists();
        $readiness ??= $this->readiness($fellow, $track)['percent'];

        $status = function (bool $passed, bool $practising, string $evidence, string $how) {
            return ['status' => $passed ? 'passed' : ($practising ? 'practising' : 'not_yet'), 'evidence' => $evidence, 'how' => $how];
        };
        $evidence = [
            'screen' => $status($profileReady && $oriented, $oriented || $profileReady,
                ($profileReady ? 'Profile headline and bio written' : 'Profile headline/bio missing') . ' · ' . ($oriented ? 'orientation done' : 'orientation not done'),
                'Write a strong headline and bio on your profile: it is your 30-second pitch.'),
            'online_test' => $status($practiceSolved >= 10, $practiceSolved > 0, "{$practiceSolved} Practice Lab challenges solved (10 = ready)", 'Solve Practice Lab challenges: they work like online tests.'),
            'technical' => $status($mock('technical_coding', $done)->count() >= 3 || $sessionPassed('technical_coding') >= 3, $mock('technical_coding', $progress)->isNotEmpty() || $sessionPassed('technical_coding') > 0,
                $mock('technical_coding', $done)->count() . ' technical mock interviews passed (3 = ready)', 'Pass the technical mock interview at the end of each chapter.'),
            'take_home' => $status($projects >= 3, $projects > 0, "{$projects} projects approved (3 = ready)", 'Complete chapter projects: they are take-home assignments.'),
            'case' => $status($caseStudies + $casesSolved + $briefs >= 2, $caseStudies + $casesSolved + $briefs > 0,
                "{$caseStudies} case studies approved, {$casesSolved} Detective cases solved, {$briefs} employer briefs shortlisted", 'Solve Data Detective cases and employer briefs.'),
            'presentation' => $status($presentations->where('status', Presentation::STATUS_APPROVED)->isNotEmpty(), $presentations->isNotEmpty(),
                $presentations->where('status', Presentation::STATUS_APPROVED)->count() . ' presentations approved', 'Present your work at Presentation Day.'),
            'behavioural' => $status($mock('behavioral', $done)->isNotEmpty() || $sessionPassed('behavioral') > 0, $mock('behavioral', $progress)->isNotEmpty(),
                ($mock('behavioral', $done)->count() + $sessionPassed('behavioral')) . ' behavioural interviews passed', 'Practise behavioural answers with the STAR method in mock interviews.'),
            'final' => $status($readiness >= self::OFFER_READINESS, $readiness >= 50, "Job-ready {$readiness}% ({$this->offerReadinessLabel()} = ready)", 'Close your biggest skill gaps for your target role.'),
        ];

        return array_map(fn ($stage) => $stage + ($evidence[$stage['key']] ?? ['status' => 'info', 'evidence' => '', 'how' => $stage['how_to_prepare'] ?? '']),
            $data['interview_loop']['typical'] ?? []);
    }

    protected function offerReadinessLabel(): string
    {
        return self::OFFER_READINESS . '%';
    }

    /**
     * Unlock the mock offer when the fellow is ready for their target role and has passed
     * most of the hiring loop. Returns true the first time (notification + feed post).
     */
    public function checkOffer(User $fellow, Track $track): bool
    {
        $enrolment = $this->enrolment($fellow, $track);
        if (!$enrolment || $enrolment->offer_unlocked_at || !$this->data($track)) {
            return false;
        }
        $ready = $this->readiness($fellow, $track);
        $passed = collect($this->interviewLoop($fellow, $track, $ready['percent']))->where('status', 'passed')->count();
        if ($ready['percent'] < self::OFFER_READINESS || $passed < self::OFFER_STAGES) {
            return false;
        }
        $enrolment->update(['offer_unlocked_at' => now(), 'offer_role' => $ready['role']['key'] ?? null]);
        Notification::send($fellow, Notification::TYPE_ACHIEVEMENT, '📨 You unlocked a mock job offer!',
            "You are ready for most of what {$ready['role']['title']} adverts ask for, and you passed the key interview stages. Open your mock offer, then apply for real roles.",
            ['action_url' => route('career.offer'), 'action_label' => 'Open my offer']);
        $first = explode(' ', trim($fellow->name))[0];
        app(TrackFeedService::class)->record($fellow, $track->id, 'level_up', "{$first} unlocked a mock {$ready['role']['title']} offer 📨", null, null, "offer:{$track->id}");

        return true;
    }

    /** "Why this matters" for a lesson: the market skill it builds, how often adverts ask for it, a real advert line and a real-world example. */
    public function whyItMatters(Track $track, TrackCurriculumActivity $activity): ?array
    {
        $data = $this->data($track);
        if (!$data) {
            return null;
        }
        $candidates = collect($data['skills'] ?? [])->filter(function ($s) use ($activity) {
            $map = $s['curriculum'] ?? null;

            return $map && empty($map['github']) && $this->activitiesFor($map, collect([$activity]))->isNotEmpty();
        })->sortByDesc('frequency')->values();
        $skill = $candidates->first();
        if (!$skill) {
            return null;
        }
        $pick = fn (array $list) => $list ? $list[abs(crc32((string) $activity->id)) % count($list)] : null;

        return [
            'skill' => $skill,
            'also' => $candidates->slice(1, 2)->pluck('label')->all(),
            'quote' => $pick($skill['quotes'] ?? []),
            'example' => $pick($data['real_world'][$skill['key']] ?? []),
            'postings' => $data['summary']['postings'] ?? null,
        ];
    }
}
