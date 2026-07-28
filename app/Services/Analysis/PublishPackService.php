<?php

namespace App\Services\Analysis;

use App\Services\Ai\OpenAiService;

class PublishPackService
{
    public function __construct(private OpenAiService $ai) {}

    /**
     * @param  array{title?: string, artists?: string, bpm?: int|null, musical_key?: string|null, mood?: string|null, notes?: string|null}  $beat
     * @return array{engine: string, titles: list<string>, short_titles: list<string>, description: string, tags: list<string>, chapters: list<string>, pinned_comment: string, checklist: list<string>}
     */
    public function generate(array $beat): array
    {
        if ($this->ai->enabled()) {
            return ['engine' => 'openai', ...$this->withAi($beat)];
        }

        return ['engine' => 'template', ...$this->withTemplate($beat)];
    }

    /**
     * @param  array<string, mixed>  $beat
     * @return array<string, mixed>
     */
    private function withAi(array $beat): array
    {
        $channel = config('ai.channel');
        $payload = $this->json($beat);

        $result = $this->ai->chatJson([
            [
                'role' => 'system',
                'content' => 'You help Type Beat producers grow on YouTube. Reply JSON only, in a mix of English SEO titles and Spanish-friendly extras when useful.',
            ],
            [
                'role' => 'user',
                'content' => <<<PROMPT
Canal: {$channel['name']} {$channel['handle']}
Nicho: {$channel['niche']}
Contacto lease: {$channel['contact_email']}
Beat: {$payload}

Genera pack de publicación JSON:
{
  "titles": ["5 títulos long-form con [FREE] y Type Beat — CADA UNO MÁXIMO 60 caracteres (móvil corta el resto)"],
  "short_titles": ["3 títulos cortos para Shorts — máx 40 caracteres"],
  "description": "descripción lista para pegar con BPM, Key, uso FREE, crédito, email, hashtags limpios",
  "tags": ["8-15 tags"],
  "chapters": ["0:00 Intro", "..."],
  "pinned_comment": "comentario pineado con CTA",
  "checklist": ["thumbnail", "end screen", "..."]
}

Reglas de títulos:
- Máximo 60 caracteres por título (incluye espacios y [FREE]).
- Pon artista + Type Beat + nombre del beat al inicio; evita relleno al final.
- Si no cabe, acorta artistas (ej. "W. Gunn" o solo el principal) o quita el año.
PROMPT,
            ],
        ]);

        return $this->normalize($result, $beat);
    }

    /**
     * @param  array<string, mixed>  $beat
     * @return array<string, mixed>
     */
    private function withTemplate(array $beat): array
    {
        $title = $beat['title'] ?? 'Untitled';
        $artists = $beat['artists'] ?: 'Dark Boom Bap';
        $bpm = $beat['bpm'] ?? '??';
        $key = $beat['musical_key'] ?? '??';
        $channel = config('ai.channel');

        $primary = $this->shortArtist($artists);

        $titles = array_values(array_filter([
            $this->fitTitle("[FREE] {$primary} Type Beat - {$title}"),
            $this->fitTitle("{$primary} Type Beat \"{$title}\" FREE"),
            $this->fitTitle("[FREE] {$title} | {$primary} Type Beat"),
            $this->fitTitle("{$primary} Type Beat - {$title} (FREE)"),
            $this->fitTitle("[FREE] Dark Boom Bap - {$title}"),
        ]));

        $shortTitles = array_values(array_filter([
            $this->fitTitle("FREE {$primary} Type Beat", 40),
            $this->fitTitle("{$primary} Type Beat \"{$title}\"", 40),
            $this->fitTitle("Dark Boom Bap Type Beat FREE", 40),
        ]));

        $description = <<<DESC
[FREE] {$artists} Type Beat - "{$title}" | Dark Boom Bap Instrumental

BPM: {$bpm} | Key: {$key} | Prod. {$channel['name']}

Dark grimy boom bap for serious bars.
Free for non-profit use only. Credit: Prod. by {$channel['name']}
Lease / exclusive → {$channel['contact_email']}

0:00 Intro
0:12 Hook
0:40 Verse

Subscribe: youtube.com/{$channel['handle']}

#TypeBeat #BoomBap #FreeTypeBeat #HollowGrimBeats
DESC;

        return [
            'titles' => $titles,
            'short_titles' => $shortTitles,
            'description' => trim($description),
            'tags' => [
                strtolower($artists).' type beat',
                'free type beat',
                'dark boom bap',
                'griselda type beat',
                'underground rap instrumental',
                'hollowgrim beats',
            ],
            'chapters' => ['0:00 Intro', '0:12 Hook', '0:40 Verse'],
            'pinned_comment' => "🔥 Full beat on the channel. Lease → {$channel['contact_email']} | Prod. {$channel['name']}",
            'checklist' => [
                'Thumbnail full-bleed + texto artista/FREE',
                'Título ≤ 60 caracteres (móvil corta el resto)',
                'BPM + Key en descripción',
                'End screen + cards',
                'Comentario pineado',
                '1-3 Shorts del mismo beat',
            ],
            'title_max' => 60,
        ];
    }

    /**
     * Prefer the first artist name so titles stay under the mobile limit.
     */
    private function shortArtist(string $artists): string
    {
        $parts = preg_split('/\s*[xX×,&]\s*/u', $artists) ?: [$artists];
        $first = trim((string) ($parts[0] ?? $artists));

        return $first !== '' ? $first : $artists;
    }

    private function fitTitle(string $title, int $max = 60): string
    {
        $title = trim(preg_replace('/\s+/', ' ', $title) ?? $title);

        if (mb_strlen($title) <= $max) {
            return $title;
        }

        return rtrim(mb_substr($title, 0, $max - 1)).'…';
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $beat
     * @return array<string, mixed>
     */
    private function normalize(array $result, array $beat): array
    {
        $fallback = $this->withTemplate($beat);

        return [
            'titles' => $this->clampTitles(array_values($result['titles'] ?? $fallback['titles']), 60),
            'short_titles' => $this->clampTitles(array_values($result['short_titles'] ?? $fallback['short_titles']), 40),
            'description' => (string) ($result['description'] ?? $fallback['description']),
            'tags' => array_values($result['tags'] ?? $fallback['tags']),
            'chapters' => array_values($result['chapters'] ?? $fallback['chapters']),
            'pinned_comment' => (string) ($result['pinned_comment'] ?? $fallback['pinned_comment']),
            'checklist' => array_values($result['checklist'] ?? $fallback['checklist']),
            'title_max' => 60,
        ];
    }

    /**
     * @param  list<string>  $titles
     * @return list<string>
     */
    private function clampTitles(array $titles, int $max): array
    {
        return array_values(array_map(
            fn (string $title) => $this->fitTitle($title, $max),
            $titles,
        ));
    }

    private function json(mixed $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE) ?: '{}';
    }
}
