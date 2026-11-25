<?php

namespace App\Support;

use App\Enums\Tier;
use App\Models\Track;
use Illuminate\Support\HtmlString;

/**
 * A track's identity: theme colours, emblem, job-style level titles, story
 * setting (company, team, manager) and skill list, from tracks.experience.
 * Tracks without one get neutral defaults, so every page can use it.
 *
 * experience JSON:
 * {
 *   "emblem": "📊", "role": "Data Analyst", "team": "Analytics Team", "company": "Nkwen Traders Ltd.",
 *   "tagline": "...", "greeting": "Ready to turn data into decisions?",
 *   "theme": {"50": "#f0fdfa", ..., "900": "#134e4a"},
 *   "levels": {"rookie": "Data Rookie", "intern": "Junior Analyst", "professional": "Data Analyst", "elite": "Lead Data Scientist"},
 *   "manager": {"name": "...", "title": "...", "initials": "NE"},
 *   "skills": [{"key": "python", "label": "Python", "description": "..."}]
 * }
 */
class TrackExperience
{
    protected const SHADES = ['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'];

    public function __construct(public readonly Track $track, protected array $config) {}

    public static function for(?Track $track): ?self
    {
        return $track ? new self($track, is_array($track->experience) ? $track->experience : []) : null;
    }

    public function hasStory(): bool
    {
        return !empty($this->config['company']) && !empty($this->config['manager']['name']);
    }

    public function emblem(): string
    {
        return $this->config['emblem'] ?? '🎯';
    }

    public function role(): string
    {
        return $this->config['role'] ?? $this->track->name . ' Fellow';
    }

    public function team(): ?string
    {
        return $this->config['team'] ?? null;
    }

    public function company(): ?string
    {
        return $this->config['company'] ?? null;
    }

    public function tagline(): ?string
    {
        return $this->config['tagline'] ?? null;
    }

    public function greeting(): string
    {
        return $this->config['greeting'] ?? 'Keep building your Career Capital.';
    }

    public function manager(): ?array
    {
        $m = $this->config['manager'] ?? null;

        return is_array($m) && !empty($m['name']) ? $m + ['title' => '', 'initials' => mb_substr($m['name'], 0, 1)] : null;
    }

    /** The track's title for a tier, e.g. "Junior Analyst" for Intern. */
    public function levelTitle(Tier|string|null $tier): string
    {
        $tier = $tier instanceof Tier ? $tier : (Tier::tryFrom((string) $tier) ?? Tier::ROOKIE);

        return $this->config['levels'][$tier->value] ?? $tier->label();
    }

    /** @return array<int, array{key: string, label: string, description: string}> */
    public function skills(): array
    {
        return array_values(array_filter($this->config['skills'] ?? [], fn ($s) => !empty($s['key']) && !empty($s['label'])));
    }

    /** CSS that re-themes the whole interface (Tailwind reads its primary colour from these variables). */
    public function themeStyle(): HtmlString
    {
        $theme = $this->config['theme'] ?? [];
        $vars = [];
        foreach (self::SHADES as $shade) {
            $color = $theme[$shade] ?? null;
            if (is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                $vars[] = "--color-primary-{$shade}:{$color}";
            }
        }

        if (!$vars) {
            return new HtmlString('');
        }

        // Component classes in the stylesheet use fixed purple values: point them at the theme too.
        $mix = fn (string $shade, int $pct) => "color-mix(in srgb,var(--color-primary-{$shade}) {$pct}%,transparent)";
        $components = implode('', [
            '.btn-primary{background-color:var(--color-primary-600)}',
            '.btn-primary:hover{background-color:var(--color-primary-700)}',
            '.btn:focus{box-shadow:0 0 0 3px ' . $mix('600', 30) . '}',
            '.badge-primary{color:var(--color-primary-400);background-color:' . $mix('600', 20) . ';border-color:' . $mix('600', 30) . '}',
            '.nav-link.active{color:var(--color-primary-400);background-color:' . $mix('600', 20) . ';border-left-color:var(--color-primary-600)}',
            '.form-input:focus{border-color:var(--color-primary-600);box-shadow:0 0 0 3px ' . $mix('600', 20) . '}',
            '.card-hover:hover{border-color:' . $mix('600', 50) . '}',
            '.card-glow:hover{box-shadow:0 0 30px ' . $mix('600', 20) . '}',
            '.avatar{background:linear-gradient(135deg,var(--color-primary-600),var(--color-primary-800))}',
        ]);

        return new HtmlString('<style>:root{' . implode(';', $vars) . '}' . $components . '</style>');
    }

    public function accent(): string
    {
        $c = $this->config['theme']['500'] ?? null;

        return is_string($c) && preg_match('/^#[0-9a-fA-F]{6}$/', $c) ? $c : '#8b5cf6';
    }
}
