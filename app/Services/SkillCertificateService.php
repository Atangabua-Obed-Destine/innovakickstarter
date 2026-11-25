<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\SkillCertificate;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Skill certificates: when every required activity tagged with a skill is
 * approved, the fellow earns a verifiable certificate for it (once).
 */
class SkillCertificateService
{
    public function __construct(protected SkillMapService $skills) {}

    /** @return Collection<SkillCertificate> newly issued certificates */
    public function checkAndAward(User $fellow, Track $track, bool $notify = true): Collection
    {
        $issued = collect();
        foreach ($this->skills->forFellow($fellow, $track) as $skill) {
            if ($skill['total'] === 0 || $skill['completed'] < $skill['total']) {
                continue;
            }
            $certificate = SkillCertificate::firstOrCreate(
                ['fellow_id' => $fellow->id, 'track_id' => $track->id, 'skill_key' => $skill['key']],
                ['skill_label' => $skill['label'], 'activities_completed' => $skill['completed'], 'points_earned' => $skill['earned'], 'issued_at' => now()]
            );
            if (!$certificate->wasRecentlyCreated) {
                continue;
            }
            $issued->push($certificate);
            app(TrackFeedService::class)->record($fellow, $track->id, 'certificate', explode(' ', trim($fellow->name))[0] . " earned the {$skill['label']} certificate",
                null, route('certificates.show', $certificate), "certificate:{$certificate->id}");
            if ($notify) {
                Notification::send($fellow, Notification::TYPE_ACHIEVEMENT, "🎓 Certificate earned: {$skill['label']}",
                    "You completed every {$skill['label']} activity in {$track->name}. Your certificate is verifiable and you can add it to LinkedIn.",
                    ['action_url' => route('certificates.show', $certificate), 'action_label' => 'View certificate']);
            }
        }

        return $issued;
    }

    public function forFellow(User $fellow): Collection
    {
        return SkillCertificate::with('track')->where('fellow_id', $fellow->id)->whereNull('revoked_at')->orderBy('issued_at')->get();
    }
}
