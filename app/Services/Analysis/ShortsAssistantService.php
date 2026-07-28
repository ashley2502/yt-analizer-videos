<?php

namespace App\Services\Analysis;

use App\Services\Ai\OpenAiService;

class ShortsAssistantService
{
    public function __construct(private OpenAiService $ai) {}

    /**
     * @param  array{title?: string, artists?: string, bpm?: int|null, duration_seconds?: int|null, drop_second?: int|null, notes?: string|null}  $input
     * @return array{engine: string, windows: list<array<string, mixed>>, overlays: list<string>, workflow: list<string>, caption: string}
     */
    public function generate(array $input): array
    {
        if ($this->ai->enabled()) {
            return ['engine' => 'openai', ...$this->withAi($input)];
        }

        return ['engine' => 'template', ...$this->withTemplate($input)];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function withAi(array $input): array
    {
        $channel = config('ai.channel');
        $payload = json_encode($input, JSON_UNESCAPED_UNICODE);

        $result = $this->ai->chatJson([
            [
                'role' => 'system',
                'content' => 'You help Type Beat producers make YouTube Shorts WITHOUT filming themselves. JSON only, Spanish instructions.',
            ],
            [
                'role' => 'user',
                'content' => <<<PROMPT
Canal: {$channel['handle']} nicho {$channel['niche']}
Beat data: {$payload}

El productor solo sube type beats con visualizer (no face-cam).
Devuelve JSON:
{
  "windows": [
    {"label": "Hook", "start_sec": 12, "end_sec": 32, "why": "...", "first_frame_tip": "empieza en el hit"}
  ],
  "overlays": ["texto 1", "texto 2"],
  "workflow": ["paso CapCut 1", "..."],
  "caption": "caption para el Short"
}
Dame 3 ventanas de 15-25s.
PROMPT,
            ],
        ]);

        return $this->normalize($result, $input);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function withTemplate(array $input): array
    {
        $artists = $input['artists'] ?: 'Dark Boom Bap';
        $title = $input['title'] ?? 'Beat';
        $drop = (int) ($input['drop_second'] ?? 12);
        $channel = config('ai.channel.handle');

        return [
            'windows' => [
                [
                    'label' => 'Hook impact',
                    'start_sec' => $drop,
                    'end_sec' => $drop + 20,
                    'why' => 'Entra el sample/808; mejor retención en los primeros 2s.',
                    'first_frame_tip' => 'Corta justo en el hit del snare/808.',
                ],
                [
                    'label' => 'Verse pocket',
                    'start_sec' => $drop + 28,
                    'end_sec' => $drop + 48,
                    'why' => 'Espacio para imaginar barras; buen loop visual.',
                    'first_frame_tip' => 'Empieza en un crash o fill.',
                ],
                [
                    'label' => 'Dark moment',
                    'start_sec' => max(0, $drop - 4),
                    'end_sec' => $drop + 16,
                    'why' => 'Build → drop funciona como spoiler del beat largo.',
                    'first_frame_tip' => '1s de tensión y luego el drop.',
                ],
            ],
            'overlays' => [
                strtoupper($artists).' TYPE BEAT',
                'FREE',
                "\"{$title}\"",
                $channel,
            ],
            'workflow' => [
                'Abre CapCut / VN → proyecto vertical 9:16.',
                'Importa el mp3 del beat + tu imagen del visualizer.',
                'Aplica zoom lento (Ken Burns) + leve shake.',
                'Corta 15-25s empezando en el drop.',
                'Texto grande arriba: artista TYPE BEAT + FREE.',
                'Último segundo: @HollowGrimBeats + “Full beat on channel”.',
                'Exporta y súbelo como Short (no video horizontal).',
            ],
            'caption' => "FREE {$artists} Type Beat \"{$title}\" 🔥 Full beat on the channel #typebeat #boombap #shorts",
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function normalize(array $result, array $input): array
    {
        $fallback = $this->withTemplate($input);

        return [
            'windows' => array_values($result['windows'] ?? $fallback['windows']),
            'overlays' => array_values($result['overlays'] ?? $fallback['overlays']),
            'workflow' => array_values($result['workflow'] ?? $fallback['workflow']),
            'caption' => (string) ($result['caption'] ?? $fallback['caption']),
        ];
    }
}
