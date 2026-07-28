<?php

namespace App\Services\Analysis;

use App\Services\Ai\OpenAiService;

class ThumbnailAnalysisService
{
    public function __construct(
        private OpenAiService $ai,
        private ThumbnailHeuristicAnalyzer $heuristic,
    ) {}

    /**
     * @return array{engine: string, score: int, summary: string, checks: array<int, mixed>, ai: array<string, mixed>|null, meta: array<string, mixed>}
     */
    public function analyze(string $absolutePath, ?string $contextTitle = null, ?string $artists = null): array
    {
        $local = $this->heuristic->analyze($absolutePath);
        $aiResult = null;
        $engine = 'heuristic';
        $score = $local['score'];
        $summary = 'Análisis local (sin API key). Añade OPENAI_API_KEY para visión IA completa.';

        if ($this->ai->enabled()) {
            $engine = 'openai+heuristic';
            $aiResult = $this->analyzeWithAi($absolutePath, $contextTitle, $artists, $local);
            $score = (int) ($aiResult['score'] ?? $score);
            $summary = (string) ($aiResult['summary'] ?? $summary);
        }

        return [
            'engine' => $engine,
            'score' => $score,
            'summary' => $summary,
            'checks' => $local['checks'],
            'ai' => $aiResult,
            'meta' => $local['meta'],
            'recommendations' => $aiResult['recommendations'] ?? $this->defaultRecommendations($local),
        ];
    }

    /**
     * @param  array<string, mixed>  $local
     * @return array<string, mixed>
     */
    private function analyzeWithAi(string $path, ?string $title, ?string $artists, array $local): array
    {
        $mime = mime_content_type($path) ?: 'image/jpeg';
        $base64 = base64_encode((string) file_get_contents($path));
        $channel = config('ai.channel');

        $prompt = <<<PROMPT
Eres un coach de CTR para canales Type Beat de YouTube (nicho: {$channel['niche']}).
Canal: {$channel['name']} ({$channel['handle']}).
Título tentativo: {$title}
Artistas: {$artists}
Datos locales: {$this->json($local['meta'])}

Evalúa la thumbnail para CTR en móvil. Responde SOLO JSON con:
{
  "score": 0-100,
  "summary": "1-2 frases en español",
  "has_text": true/false,
  "text_readable_on_mobile": true/false,
  "detected_text": ["..."],
  "has_letterboxing": true/false,
  "niche_fit": 0-10,
  "mood_match": "string",
  "recommendations": ["accionable 1", "accionable 2", "accionable 3"],
  "suggested_text_overlay": ["WESTSIDE GUNN x GRISELDA", "FREE", "BEAT NAME"]
}
PROMPT;

        return $this->ai->visionJson([
            [
                'role' => 'system',
                'content' => 'You are a YouTube Type Beat thumbnail analyst. Reply with valid JSON only.',
            ],
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => $prompt],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => "data:{$mime};base64,{$base64}",
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $local
     * @return list<string>
     */
    private function defaultRecommendations(array $local): array
    {
        $recs = [
            'Añade texto grande: ARTISTA TYPE BEAT + FREE + nombre del beat.',
            'Imagen full-bleed 1280x720 sin barras negras.',
            'Incluye marca HollowGrim pequeña y alto contraste.',
        ];

        if (($local['meta']['letterbox']['detected'] ?? false) === true) {
            array_unshift($recs, 'Quita el letterbox: recorta o genera un fondo 16:9 real.');
        }

        return $recs;
    }

    private function json(mixed $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE) ?: '{}';
    }
}
