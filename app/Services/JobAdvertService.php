<?php

namespace App\Services;

use App\Models\JobAdvertCheck;
use App\Models\Track;
use App\Models\User;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiUnavailableException;
use Illuminate\Support\Str;

/**
 * "You vs a job advert": the AI reads any advert a fellow pastes and lists what it
 * asks for, mapped to the researched skill list. The match is computed here from the
 * fellow's proven skills (reviewed work), never by the AI.
 */
class JobAdvertService
{
    public const DAILY_LIMIT = 10;

    public function __construct(protected AiClient $ai, protected JobMarketService $market) {}

    /** @return array{ok: bool, error?: string, check?: JobAdvertCheck} */
    public function analyse(User $fellow, Track $track, string $advert): array
    {
        if (JobAdvertCheck::where('fellow_id', $fellow->id)->where('created_at', '>=', now()->startOfDay())->count() >= self::DAILY_LIMIT) {
            return ['ok' => false, 'error' => 'You have checked ' . self::DAILY_LIMIT . ' adverts today. Come back tomorrow!'];
        }
        $data = $this->market->data($track);
        $skills = collect($data['skills'] ?? [])->mapWithKeys(fn ($s) => [$s['key'] => $s['label']]);

        $system = implode("\n", [
            'You read a job advert and list what it asks candidates for. Be faithful to the advert: never add requirements it does not state.',
            'Map each requirement to ONE skill key from this list, or "other" if none fits: ' . $skills->map(fn ($l, $k) => "{$k} ({$l})")->implode(', ') . '.',
            'importance: "must" if required/essential, "nice" if preferred/bonus/desirable.',
            'Reply with JSON only: {"title": "job title", "company": "company or null", "seniority": "intern|entry|junior|mid|senior|unknown", "years": number or null,',
            '"requirements": [{"text": "short requirement in plain English", "skill": "key or other", "importance": "must|nice"}]}. At most 20 requirements.',
        ]);

        try {
            $result = $this->ai->chat('mentor', ['messages' => [['role' => 'system', 'content' => $system], ['role' => 'user', 'content' => "JOB ADVERT:\n\"\"\"\n" . Str::limit($advert, 9000) . "\n\"\"\""]],
                'temperature' => 0.1, 'response_format' => ['type' => 'json_object']], 900, ['timeout' => 60]);
        } catch (AiUnavailableException $e) {
            report($e);

            return ['ok' => false, 'error' => 'The advert reader is busy right now. Try again in a minute.'];
        }
        $raw = trim(preg_replace('/<think>.*?<\/think>/s', '', (string) $result['response']->json('choices.0.message.content')));
        $json = json_decode(preg_replace('/^```(json)?|```$/m', '', $raw), true);
        $reqs = collect(is_array($json['requirements'] ?? null) ? $json['requirements'] : [])
            ->filter(fn ($r) => is_array($r) && is_string($r['text'] ?? null))
            ->map(fn ($r) => ['text' => Str::limit($r['text'], 200), 'skill' => $skills->has($r['skill'] ?? '') ? $r['skill'] : 'other', 'importance' => ($r['importance'] ?? '') === 'nice' ? 'nice' : 'must'])
            ->take(20)->values();
        if ($reqs->isEmpty()) {
            return ['ok' => false, 'error' => 'I could not find requirements in that text. Paste the full advert, including the requirements section.'];
        }

        $proof = $this->market->proof($fellow, $track);
        $byKey = collect($data['skills'] ?? [])->keyBy('key');
        $rows = $reqs->map(function ($r) use ($proof, $byKey) {
            $skill = $byKey[$r['skill']] ?? null;
            $p = $proof[$r['skill']] ?? null;
            $state = !$skill || ($skill['kind'] ?? '') === 'info' ? 'unmeasured' : (!$p ? 'beyond' : ($p['proof'] >= 0.75 ? 'have' : ($p['proof'] > 0 ? 'growing' : 'missing')));

            return $r + ['label' => $skill['label'] ?? null, 'state' => $state, 'proof' => $p['proof'] ?? null, 'evidence' => $p['evidence'] ?? null,
                'next' => $p['next'] ?? [], 'beyond' => $state === 'beyond' ? ($skill['beyond'] ?? []) : []];
        });
        $scored = $rows->whereIn('state', ['have', 'growing', 'missing', 'beyond']);
        $weight = fn ($r) => $r['importance'] === 'must' ? 2 : 1;
        $total = $scored->sum($weight);
        $match = $total > 0 ? (int) round($scored->sum(fn ($r) => $weight($r) * (float) ($r['proof'] ?? 0)) / $total * 100) : 0;
        $plan = $rows->whereIn('state', ['missing', 'growing'])->sortByDesc(fn ($r) => $weight($r))->flatMap(fn ($r) => $r['next'])->unique('id')->take(3)->values()->all();

        $check = JobAdvertCheck::create([
            'fellow_id' => $fellow->id, 'track_id' => $track->id,
            'title' => Str::limit((string) ($json['title'] ?? 'Job advert'), 190), 'company' => isset($json['company']) && is_string($json['company']) ? Str::limit($json['company'], 190) : null,
            'match' => $match,
            'result' => ['seniority' => $json['seniority'] ?? 'unknown', 'years' => is_numeric($json['years'] ?? null) ? (float) $json['years'] : null,
                'requirements' => $rows->all(), 'plan' => $plan, 'model' => $result['model'] ?? null],
        ]);
        app(FellowActivityService::class)->record($fellow, 'help');

        return ['ok' => true, 'check' => $check];
    }
}
