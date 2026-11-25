<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Show & Tell videos: fellows publish a video on their own YouTube channel and share the link.
 * Nothing is uploaded to the portal. We read the link, ask YouTube's public oEmbed endpoint
 * whether the video exists and can be embedded, and keep its title and channel.
 *
 * Limitation (stated to reviewers): oEmbed answers the same for Public and Unlisted videos,
 * so "Public" is confirmed by the fellow and checked by the reviewer. Private videos and
 * videos that do not exist are refused.
 */
class YouTubeVideoService
{
    /** Pull the 11-character video id out of any usual YouTube link, or null. */
    public function videoId(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        $parts = parse_url($url);
        $host = strtolower(preg_replace('/^(www\.|m\.|music\.)/i', '', $parts['host'] ?? ''));
        $path = $parts['path'] ?? '';
        $id = null;

        if ($host === 'youtu.be') {
            $id = explode('/', ltrim($path, '/'))[0] ?? null;
        } elseif ($host === 'youtube.com' || $host === 'youtube-nocookie.com') {
            if (rtrim($path, '/') === '/watch') {
                parse_str($parts['query'] ?? '', $q);
                $id = is_string($q['v'] ?? null) ? $q['v'] : null;
            } elseif (preg_match('#^/(shorts|live|embed|v)/([^/?]+)#', $path, $m)) {
                $id = $m[2];
            }
        }

        return $id !== null && preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : null;
    }

    public function watchUrl(string $id): string
    {
        return "https://www.youtube.com/watch?v={$id}";
    }

    public function embedUrl(string $id): string
    {
        return "https://www.youtube-nocookie.com/embed/{$id}?rel=0";
    }

    /**
     * Check a link with YouTube.
     *
     * @return array{ok: bool, id?: string, url?: string, title?: string|null, channel?: string|null, channel_url?: string|null, verified?: bool, error?: string}
     */
    public function check(?string $url): array
    {
        $id = $this->videoId($url);
        if (!$id) {
            return ['ok' => false, 'error' => 'That is not a YouTube video link. Copy the link from the Share button under your video (it looks like https://youtu.be/... or https://www.youtube.com/watch?v=...).'];
        }

        $info = Cache::remember("yt-oembed:{$id}", now()->addMinutes(10), function () use ($id) {
            try {
                $r = Http::timeout(8)->acceptJson()->get('https://www.youtube.com/oembed', ['url' => $this->watchUrl($id), 'format' => 'json']);
            } catch (\Throwable $e) {
                return ['status' => 0];
            }

            return ['status' => $r->status(), 'json' => $r->successful() ? $r->json() : null];
        });

        $base = ['id' => $id, 'url' => $this->watchUrl($id)];
        if ($info['status'] === 200 && is_array($info['json'] ?? null)) {
            return $base + [
                'ok' => true,
                'verified' => true,
                'title' => mb_substr((string) ($info['json']['title'] ?? ''), 0, 200) ?: null,
                'channel' => mb_substr((string) ($info['json']['author_name'] ?? ''), 0, 120) ?: null,
                'channel_url' => $this->safeChannelUrl($info['json']['author_url'] ?? null),
            ];
        }
        if (in_array($info['status'], [401, 403], true)) {
            Cache::forget("yt-oembed:{$id}");

            return ['ok' => false, 'error' => 'YouTube says this video is Private or cannot be embedded. In YouTube Studio set Visibility to Public and keep "Allow embedding" on, then try again.'];
        }
        if (in_array($info['status'], [400, 404], true)) {
            Cache::forget("yt-oembed:{$id}");

            return ['ok' => false, 'error' => 'YouTube cannot find this video. Check the link, and wait until YouTube has finished processing your upload.'];
        }

        // YouTube unreachable from our side: do not punish the fellow, the reviewer will open the link.
        Cache::forget("yt-oembed:{$id}");

        return $base + ['ok' => true, 'verified' => false, 'title' => null, 'channel' => null, 'channel_url' => null];
    }

    private function safeChannelUrl($url): ?string
    {
        return is_string($url) && preg_match('#^https://(www\.)?youtube\.com/#', $url) ? $url : null;
    }
}
