<?php

namespace App\Services\AiReview;

/**
 * Turns a Jupyter notebook (.ipynb JSON) into readable text: markdown and
 * code cells, plus a short excerpt of text outputs.
 */
class NotebookReader
{
    public static function toText(string $json): string
    {
        $notebook = json_decode($json, true);

        if (!is_array($notebook) || !isset($notebook['cells'])) {
            return $json;
        }

        $out = [];
        foreach ($notebook['cells'] as $i => $cell) {
            $source = is_array($cell['source'] ?? null) ? implode('', $cell['source']) : (string) ($cell['source'] ?? '');
            $type = $cell['cell_type'] ?? 'code';
            $out[] = "# [cell " . ($i + 1) . " · {$type}]\n" . $source;

            if ($type === 'code') {
                $text = '';
                foreach ($cell['outputs'] ?? [] as $output) {
                    $chunk = $output['text'] ?? ($output['data']['text/plain'] ?? null);
                    if ($chunk !== null) {
                        $text .= is_array($chunk) ? implode('', $chunk) : (string) $chunk;
                    } elseif (isset($output['data']['image/png'])) {
                        $text .= "[chart/image output]\n";
                    }
                }
                if ($text !== '') {
                    $out[] = "# [output]\n" . mb_substr($text, 0, 600);
                }
            }
        }

        return implode("\n\n", $out);
    }
}
