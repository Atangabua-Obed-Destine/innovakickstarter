<?php

namespace App\Services\Interview;

use App\Services\Ai\AiProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

/**
 * Speech to text with Groq Whisper (OpenAI-compatible audio transcriptions).
 * The audio is sent once and never stored. Throws on any failure so the
 * browser can fall back to its own speech recognition.
 */
class WhisperTranscriber
{
    public const URL = 'https://api.groq.com/openai/v1/audio/transcriptions';
    public const MODEL = 'whisper-large-v3-turbo';
    public const MAX_BYTES = 25 * 1024 * 1024;
    public const MAX_SECONDS = 300;

    public function available(): bool
    {
        return AiProvider::key('groq') !== null;
    }

    /**
     * @return array{text: string, duration_seconds: ?float}
     */
    public function transcribe(UploadedFile $audio, string $prompt = ''): array
    {
        $key = AiProvider::key('groq');
        if ($key === null) {
            throw new \RuntimeException('No Groq key is set.');
        }

        $extension = strtolower($audio->getClientOriginalExtension() ?: $audio->extension() ?: 'webm');
        $extension = in_array($extension, ['webm', 'ogg', 'mp4', 'm4a', 'wav', 'mp3', 'mpeg', 'mpga', 'flac'], true) ? $extension : 'webm';

        $response = Http::withToken($key)
            ->acceptJson()
            ->timeout(60)
            ->attach('file', fopen($audio->getRealPath(), 'r'), 'answer.' . $extension)
            ->post(self::URL, array_filter([
                'model' => self::MODEL,
                'language' => 'en',
                'response_format' => 'verbose_json',
                'temperature' => '0',
                'prompt' => mb_substr($prompt, 0, 600) ?: null,
            ], fn ($v) => $v !== null));

        if (!$response->successful()) {
            throw new \RuntimeException('Whisper answered HTTP ' . $response->status() . '.');
        }
        $text = trim((string) $response->json('text'));
        $duration = $response->json('duration');

        return ['text' => $text, 'duration_seconds' => is_numeric($duration) ? round((float) $duration, 1) : null];
    }

    /**
     * A short prompt that helps Whisper spell the track's terms and keep filler
     * words (so the filler count is honest).
     */
    public function promptFor(?array $context): string
    {
        $terms = array_slice(array_merge(
            (array) ($context['chapter']['topics'] ?? []),
            (array) ($context['chapter']['skills'] ?? []),
        ), 0, 12);

        return 'Umm, so, uh, this is a job interview answer about ' . ($context['track']['name'] ?? 'software') .
            ($terms ? ': ' . implode(', ', array_map(fn ($t) => mb_substr((string) $t, 0, 60), $terms)) : '') . '.';
    }
}
