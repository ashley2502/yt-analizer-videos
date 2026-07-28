<?php

namespace App\Services\Analysis;

use App\Services\Ai\OpenAiService;

class BriefService
{
    public function __construct(private OpenAiService $ai) {}

    /**
     * @param  array{artists?: string, mood?: string, notes?: string|null}  $input
     * @return array<string, mixed>
     */
    public function generate(array $input): array
    {
        if ($this->ai->enabled()) {
            $result = $this->ai->chatJson([
                [
                    'role' => 'system',
                    'content' => 'You are a boom-bap / Griselda-style beat coach. JSON only. Do not encourage copying melodies note-for-note. Suggest sample-hunting queries and filters, not illegal download sites.',
                ],
                [
                    'role' => 'user',
                    'content' => 'Crea un brief de producción para Type Beat. Input: '.json_encode($input, JSON_UNESCAPED_UNICODE).
                        ' Nicho: '.config('ai.channel.niche').
                        ' JSON: {"bpm":85,"musical_key":"A minor","structure":"...","hook_idea":"...","references":["mood refs"],"title_ideas":["..."],"session_plan":["25min tasks"],"sample_hunt":{"keywords":["5-8 YouTube search queries for melodic samples"],"drum_break_keywords":["4-6 YouTube queries for drum breaks"],"samplette_filters":{"Genre":"Jazz","Style":"Soul-Jazz","Region":"Italy","Year":"1965-1979","Tempo":"70-90","Key":"A minor"},"samplette_drums_filters":{"Genre":"Funk","Style":"Soul","Region":"USA","Year":"1968-1976","Tempo":"70-90","Key":""},"youtube_channels":[{"name":"channel","why":"why"}]}}'.
                        ' Importante: samplette_filters y samplette_drums_filters deben ser valores CORTOS listos para pegar en cada campo de Samplette (un valor por campo, no listas largas).',
                ],
            ]);

            return ['engine' => 'openai', ...$this->normalize($result, $input)];
        }

        return ['engine' => 'template', ...$this->withTemplate($input)];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function withTemplate(array $input): array
    {
        $artists = $input['artists'] ?: 'Griselda / Mobb Deep';
        $mood = $input['mood'] ?: 'dark, dusty, grimy';
        $bpms = [72, 78, 84, 88, 92];
        $keys = ['A minor', 'D minor', 'F minor', 'C minor', 'G minor'];
        $bpm = $bpms[array_rand($bpms)];
        $key = $keys[array_rand($keys)];

        return [
            'bpm' => $bpm,
            'musical_key' => $key,
            'structure' => '8 intro · 16 hook · 16 verse · 8 hook · 16 verse · 8 outro',
            'hook_idea' => "Sample vibe {$mood}: chops cortos, vinyl crackle, 808 con glide suave, hats sucios.",
            'references' => [
                "Mood de {$artists} (no copies melodías)",
                'Drums tipo Alchemist: dry, punchy, room corto',
                'Melodía menor, intervalos cercanos, atmosphere pad bajo',
            ],
            'title_ideas' => [
                'Smoke',
                'Marble',
                'Night Court',
                'Cold Room',
            ],
            'session_plan' => [
                '10 min: buscar 1 sample/loop en mood oscuro',
                '25 min: drums + 808 pocket',
                '25 min: arrangement mínimo escuchable',
                '10 min: export sketch y nombrar el beat',
            ],
            'sample_hunt' => $this->buildSampleHunt($input, $bpm, $key),
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
        $bpm = (int) ($result['bpm'] ?? $fallback['bpm']);
        $key = (string) ($result['musical_key'] ?? $fallback['musical_key']);

        return [
            'bpm' => $bpm,
            'musical_key' => $key,
            'structure' => (string) ($result['structure'] ?? $fallback['structure']),
            'hook_idea' => (string) ($result['hook_idea'] ?? $fallback['hook_idea']),
            'references' => array_values($result['references'] ?? $fallback['references']),
            'title_ideas' => array_values($result['title_ideas'] ?? $fallback['title_ideas']),
            'session_plan' => array_values($result['session_plan'] ?? $fallback['session_plan']),
            'sample_hunt' => $this->normalizeSampleHunt(
                is_array($result['sample_hunt'] ?? null) ? $result['sample_hunt'] : [],
                $input,
                $bpm,
                $key,
                is_array($fallback['sample_hunt'] ?? null) ? $fallback['sample_hunt'] : [],
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function buildSampleHunt(array $input, int $bpm, string $key): array
    {
        $artists = trim((string) ($input['artists'] ?? 'Griselda'));
        $mood = trim((string) ($input['mood'] ?? 'dark dusty grimy'));
        $notes = trim((string) ($input['notes'] ?? ''));
        $primary = $this->primaryArtist($artists);
        $tempoLow = max(50, $bpm - 15);
        $tempoHigh = $bpm + 10;

        $keywords = array_values(array_unique(array_filter([
            "1970s italian soundtrack piano {$mood}",
            'dusty soul vinyl loop minor key',
            'obscure jazz guitar 1968 vinyl',
            "dark {$primary} vibe instrumental sample",
            'grimy boom bap sample pack free',
            'japanese city pop vinyl crackle',
            'library music thriller 1970s',
            $notes !== '' ? "{$notes} vinyl sample" : null,
            "{$mood} piano sample royalty free",
        ])));

        $drumBreakKeywords = array_values(array_unique(array_filter([
            "drum break {$bpm} bpm vinyl",
            'funky drummer style break boom bap',
            'obscure 1960s drum break dusty',
            'soul drum break dry roomy',
            "{$bpm} bpm drum break sample pack free",
            'classic hip hop drum breaks compilation',
            'amen break alternative obscure',
            'grimy boom bap drums one shot break',
            $notes !== '' && str_contains(strtolower($notes), 'drum')
                ? "{$notes} drum break"
                : null,
        ])));

        $channels = [
            [
                'name' => 'Tracklib (cleared samples)',
                'why' => 'Samples con licencia clara si quieres publicar sin drama legal.',
                'url' => 'https://www.youtube.com/@Tracklib',
            ],
            [
                'name' => 'Pell (crate digs / vinyl finds)',
                'why' => 'Buenas piezas oscuras y digs tipo boom bap underground.',
                'url' => 'https://www.youtube.com/@Pell',
            ],
            [
                'name' => 'Drum breaks / breakbeats compilations',
                'why' => 'Búsquedas de breaks clásicos y oscuros para chops de kit.',
                'url' => 'https://www.youtube.com/results?search_query=drum+breaks+compilation+vinyl+boom+bap',
            ],
            [
                'name' => 'The BreakBeat Bible / break digs',
                'why' => 'Referencias de breaks históricos usados en hip-hop.',
                'url' => 'https://www.youtube.com/results?search_query=hip+hop+drum+breaks+origins',
            ],
            [
                'name' => 'Royalty Free drum breaks',
                'why' => 'Breaks libres para sketches rápidos sin copyright drama.',
                'url' => 'https://www.youtube.com/results?search_query=royalty+free+drum+breaks+boom+bap',
            ],
            [
                'name' => 'SuperDuperMusic (library / rare)',
                'why' => 'Library music y rarezas útiles para chops melódicos.',
                'url' => 'https://www.youtube.com/results?search_query=library+music+1970s+vinyl',
            ],
        ];

        return $this->enrichSampleHunt([
            'keywords' => $keywords,
            'drum_break_keywords' => $drumBreakKeywords,
            'samplette_filters' => [
                'Genre' => 'Jazz',
                'Style' => 'Soul-Jazz',
                'Region' => 'Italy',
                'Year' => '1965-1979',
                'Tempo' => "{$tempoLow}-{$tempoHigh}",
                'Key' => $key,
            ],
            'samplette_drums_filters' => [
                'Genre' => 'Funk',
                'Style' => 'Soul',
                'Region' => 'USA',
                'Year' => '1968-1976',
                'Tempo' => "{$tempoLow}-{$tempoHigh}",
                'Key' => '',
            ],
            'youtube_channels' => $channels,
        ], $keywords, $drumBreakKeywords);
    }

    /**
     * @param  array<string, mixed>  $sampleHunt
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $fallback
     * @return array<string, mixed>
     */
    private function normalizeSampleHunt(
        array $sampleHunt,
        array $input,
        int $bpm,
        string $key,
        array $fallback,
    ): array {
        $keywords = array_values($sampleHunt['keywords'] ?? $fallback['keywords'] ?? []);
        if ($keywords === []) {
            return $this->buildSampleHunt($input, $bpm, $key);
        }

        $drumBreakKeywords = array_values(
            $sampleHunt['drum_break_keywords'] ?? $fallback['drum_break_keywords'] ?? [],
        );

        $filters = $this->normalizeFilterMap(
            is_array($sampleHunt['samplette_filters'] ?? null) ? $sampleHunt['samplette_filters'] : [],
            is_array($fallback['samplette_filters'] ?? null) ? $fallback['samplette_filters'] : [],
        );

        $drumFilters = $this->normalizeFilterMap(
            is_array($sampleHunt['samplette_drums_filters'] ?? null) ? $sampleHunt['samplette_drums_filters'] : [],
            is_array($fallback['samplette_drums_filters'] ?? null) ? $fallback['samplette_drums_filters'] : [],
        );

        $channels = array_values($sampleHunt['youtube_channels'] ?? $fallback['youtube_channels'] ?? []);

        return $this->enrichSampleHunt([
            'keywords' => $keywords,
            'drum_break_keywords' => $drumBreakKeywords,
            'samplette_filters' => $filters,
            'samplette_drums_filters' => $drumFilters,
            'youtube_channels' => $channels,
        ], $keywords, $drumBreakKeywords);
    }

    /**
     * @param  array<string, mixed>  $preferred
     * @param  array<string, mixed>  $fallback
     * @return array<string, string>
     */
    private function normalizeFilterMap(array $preferred, array $fallback): array
    {
        $merged = array_merge($fallback, $preferred);
        $map = [];

        $aliases = [
            'genre' => 'Genre',
            'style' => 'Style',
            'region' => 'Region',
            'years' => 'Year',
            'year' => 'Year',
            'tempo' => 'Tempo',
            'key' => 'Key',
            'key_hint' => 'Key',
            'Genre' => 'Genre',
            'Style' => 'Style',
            'Region' => 'Region',
            'Year' => 'Year',
            'Tempo' => 'Tempo',
            'Key' => 'Key',
        ];

        foreach ($merged as $key => $value) {
            if (! is_scalar($value) || (string) $value === '') {
                continue;
            }
            $label = $aliases[(string) $key] ?? null;
            if ($label === null) {
                continue;
            }
            $map[$label] = trim((string) $value);
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $hunt
     * @param  list<string>  $keywords
     * @param  list<string>  $drumBreakKeywords
     * @return array<string, mixed>
     */
    private function enrichSampleHunt(array $hunt, array $keywords, array $drumBreakKeywords = []): array
    {
        $youtubeSearches = [];
        foreach (array_slice($keywords, 0, 6) as $keyword) {
            $youtubeSearches[] = [
                'label' => $keyword,
                'query' => $keyword,
                'url' => 'https://www.youtube.com/results?search_query='.rawurlencode($keyword),
            ];
        }

        if ($drumBreakKeywords === []) {
            $drumBreakKeywords = array_values($hunt['drum_break_keywords'] ?? []);
        }

        $drumBreakSearches = [];
        foreach (array_slice($drumBreakKeywords, 0, 6) as $keyword) {
            $drumBreakSearches[] = [
                'label' => $keyword,
                'query' => $keyword,
                'url' => 'https://www.youtube.com/results?search_query='.rawurlencode($keyword),
            ];
        }

        $channels = [];
        foreach (array_values($hunt['youtube_channels'] ?? []) as $channel) {
            if (! is_array($channel)) {
                continue;
            }
            $name = (string) ($channel['name'] ?? 'Canal');
            $url = (string) ($channel['url'] ?? '');
            if ($url === '') {
                $url = 'https://www.youtube.com/results?search_query='.rawurlencode($name.' samples vinyl');
            }
            $channels[] = [
                'name' => $name,
                'why' => (string) ($channel['why'] ?? ''),
                'url' => $url,
            ];
        }

        $melodyFilters = is_array($hunt['samplette_filters'] ?? null)
            ? $hunt['samplette_filters']
            : [];
        $drumsFilters = is_array($hunt['samplette_drums_filters'] ?? null)
            ? $hunt['samplette_drums_filters']
            : [];

        return [
            'keywords' => array_values($keywords),
            'drum_break_keywords' => array_values($drumBreakKeywords),
            'youtube_searches' => $youtubeSearches,
            'drum_break_searches' => $drumBreakSearches,
            'samplette' => [
                'url' => 'https://samplette.io/',
                'how' => 'Abre Samplette → tecla S (Filters) → pega cada valor en su campo. Samplette no acepta URL con filtros; por eso van listos para copiar.',
                'filters' => $melodyFilters,
                'copy_fields' => $this->filterCopyFields($melodyFilters),
                'copy_all' => $this->filterPasteBlock($melodyFilters, 'Samplette · melodía / vibes'),
                'drums_copy_fields' => $this->filterCopyFields($drumsFilters),
                'drums_copy_all' => $this->filterPasteBlock($drumsFilters, 'Samplette · drum breaks'),
            ],
            'youtube_copy_all' => $this->queriesPasteBlock(
                array_column($youtubeSearches, 'query'),
                'YouTube · melodía / vibes',
            ),
            'youtube_drums_copy_all' => $this->queriesPasteBlock(
                array_column($drumBreakSearches, 'query'),
                'YouTube · drum breaks',
            ),
            'youtube_channels' => $channels,
            'disclaimer' => 'Limpia samples y breaks si vas a monetizar / vender leases. Preferible Tracklib, packs con licencia o kits royalty-free.',
        ];
    }

    /**
     * @param  array<string, string>  $filters
     * @return list<array{field: string, value: string}>
     */
    private function filterCopyFields(array $filters): array
    {
        $fields = [];
        foreach ($filters as $field => $value) {
            if ($value === '') {
                continue;
            }
            $fields[] = [
                'field' => $field,
                'value' => $value,
            ];
        }

        return $fields;
    }

    /**
     * @param  array<string, string>  $filters
     */
    private function filterPasteBlock(array $filters, string $title): string
    {
        $lines = [$title, str_repeat('-', mb_strlen($title))];
        foreach ($filters as $field => $value) {
            if ($value === '') {
                continue;
            }
            $lines[] = "{$field}: {$value}";
        }

        return implode("\n", $lines);
    }

    /**
     * @param  list<string>  $queries
     */
    private function queriesPasteBlock(array $queries, string $title): string
    {
        $lines = [$title, str_repeat('-', mb_strlen($title))];
        foreach ($queries as $i => $query) {
            $lines[] = ($i + 1).'. '.$query;
        }

        return implode("\n", $lines);
    }

    private function primaryArtist(string $artists): string
    {
        $parts = preg_split('/\s*[xX×,&\/]\s*/u', $artists) ?: [$artists];

        return trim((string) ($parts[0] ?? $artists)) ?: $artists;
    }
}
