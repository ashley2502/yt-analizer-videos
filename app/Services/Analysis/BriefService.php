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
        $briefInput = [
            'artists' => trim((string) ($input['artists'] ?? '')),
            'mood' => trim((string) ($input['mood'] ?? '')),
            'notes' => trim((string) ($input['notes'] ?? '')),
        ];

        if ($this->ai->enabled()) {
            $result = $this->ai->chatJson([
                [
                    'role' => 'system',
                    'content' => 'You are a boom-bap / underground Type Beat coach. JSON only. Vary BPM, key, titles, structure, sample hunt and Samplette filters based on the user input — never reuse a fixed Italy/Jazz recipe. Do not encourage copying melodies note-for-note. Suggest sample-hunting queries and filters, not illegal download sites.',
                ],
                [
                    'role' => 'user',
                    'content' => <<<PROMPT
Crea un brief de producción Type Beat ÚNICO para este input (cámbialo si cambian artists/mood/notes):
{$this->json($briefInput)}

Nicho del canal: {$this->channelNiche()}

JSON schema (rellena con valores concretos derivados del input, NO copies ejemplos literales):
{
  "bpm": 72-96 integer matching mood,
  "musical_key": "e.g. D minor / F# minor / Bb minor",
  "structure": "bars: intro/hook/verse tailored to the vibe",
  "hook_idea": "1-2 sentences specific to artists + mood + notes",
  "references": ["3 production refs inspired by artists/mood, no melody theft"],
  "title_ideas": ["4 short beat name ideas reflecting THIS mood/notes"],
  "session_plan": ["4 timed tasks specific to this brief"],
  "sample_hunt": {
    "keywords": ["5-8 YouTube queries using mood/notes instruments/era/region"],
    "drum_break_keywords": ["4-6 YouTube drum-break queries matching BPM feel"],
    "samplette_filters": {"Genre":"one short value","Style":"one short value","Region":"country/region that fits","Year":"range","Tempo":"low-high","Key":"musical key"},
    "samplette_drums_filters": {"Genre":"...","Style":"...","Region":"...","Year":"...","Tempo":"...","Key":""},
    "youtube_channels": [{"name":"channel or dig type","why":"why useful for THIS mood"}]
  }
}

Reglas:
- Si notes menciona piano/guitar/soundtrack/italiano/japonés/etc., refleja eso en keywords y Samplette Region/Genre/Style.
- samplette_*_filters: valores CORTOS listos para pegar en Samplette (un valor por campo).
- title_ideas y hook_idea deben sonar distintos si el mood o las notes cambian.
PROMPT,
                ],
            ]);

            return ['engine' => 'openai', ...$this->normalize($result, $briefInput)];
        }

        return ['engine' => 'template', ...$this->withTemplate($briefInput)];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function withTemplate(array $input): array
    {
        $artists = $input['artists'] !== '' ? $input['artists'] : 'Griselda / Mobb Deep';
        $mood = $input['mood'] !== '' ? $input['mood'] : 'dark, dusty, grimy';
        $notes = $input['notes'] ?? '';
        $seed = $this->seed($artists, $mood, $notes);
        $profile = $this->sampleProfile($mood, $notes, $seed);

        $bpms = [72, 76, 80, 84, 88, 92];
        $keys = ['A minor', 'D minor', 'F minor', 'C minor', 'G minor', 'Bb minor', 'E minor'];
        $bpm = $bpms[$seed % count($bpms)];
        $key = $keys[($seed >> 3) % count($keys)];
        $tempoLow = max(50, $bpm - 15);
        $tempoHigh = $bpm + 10;

        $structures = [
            '8 intro · 16 hook · 16 verse · 8 hook · 16 verse · 8 outro',
            '4 cold open · 8 hook · 16 verse · 8 hook · 16 verse · 8 bridge · 8 hook',
            '16 loop bed · 8 hook drop · 16 verse · 8 switch-up · 16 verse · 8 fade',
            '8 dust intro · 12 hook · 20 verse · 8 hook · 16 verse · 4 tag',
        ];

        $titlePools = [
            'dark' => ['Smoke Room', 'Black Marble', 'Cold Chain', 'Night Docket'],
            'dusty' => ['Dust Frame', 'Yellow Label', 'Crate Ash', 'Needle Run'],
            'grimy' => ['Grime Pocket', 'Basement Iron', 'Wet Concrete', 'Low Ceiling'],
            'melodic' => ['Soft Knife', 'Minor Halo', 'Glass Choir', 'Pale Ribbon'],
            'default' => ['Hollow Court', 'Grey Stamp', 'Quiet Warrant', 'Iron Hymn'],
        ];
        $titleKey = match (true) {
            str_contains(strtolower($mood), 'melodic') || str_contains(strtolower($notes), 'piano') => 'melodic',
            str_contains(strtolower($mood), 'grimy') || str_contains(strtolower($mood), 'grime') => 'grimy',
            str_contains(strtolower($mood), 'dust') => 'dusty',
            str_contains(strtolower($mood), 'dark') || str_contains(strtolower($mood), 'noir') => 'dark',
            default => 'default',
        };
        $titles = $titlePools[$titleKey];
        // Rotate so same pool still shifts with seed / notes
        $offset = $seed % count($titles);
        $titleIdeas = array_values([
            ...array_slice($titles, $offset),
            ...array_slice($titles, 0, $offset),
        ]);
        if ($notes !== '') {
            $titleIdeas[0] = $this->titleFromNotes($notes, $titleIdeas[0]);
        }

        $instrumentHint = $this->instrumentHint($notes, $mood);

        return [
            'bpm' => $bpm,
            'musical_key' => $key,
            'structure' => $structures[$seed % count($structures)],
            'hook_idea' => "Vibe {$mood}".($notes !== '' ? " ({$notes})" : '').": {$instrumentHint}, vinyl texture, 808 con glide suave, hats sucios tipo {$this->primaryArtist($artists)}.",
            'references' => [
                "Mood de {$artists} (no copies melodías)",
                "Drums dry/punchy; melodía en {$key} con atmosphere baja",
                $profile['ref'],
            ],
            'title_ideas' => $titleIdeas,
            'session_plan' => [
                "10 min: buscar sample {$profile['hunt_focus']}",
                '25 min: drums + 808 pocket al BPM del brief',
                "25 min: arrangement mínimo + hook ({$instrumentHint})",
                '10 min: export sketch y elegir un title idea',
            ],
            'sample_hunt' => $this->buildSampleHunt($input, $bpm, $key, $profile, $tempoLow, $tempoHigh),
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
     * @param  array<string, mixed>  $profile
     * @return array<string, mixed>
     */
    private function buildSampleHunt(
        array $input,
        int $bpm,
        string $key,
        array $profile,
        int $tempoLow,
        int $tempoHigh,
    ): array {
        $artists = trim((string) ($input['artists'] ?? 'Griselda'));
        $mood = trim((string) ($input['mood'] ?? 'dark dusty grimy'));
        $notes = trim((string) ($input['notes'] ?? ''));
        $primary = $this->primaryArtist($artists);

        $keywords = array_values(array_unique(array_filter([
            $profile['yt_melody'],
            "{$mood} {$profile['instrument']} vinyl sample",
            "{$profile['era']} {$profile['region_label']} {$profile['instrument']} loop",
            "dusty {$profile['genre_label']} minor key sample",
            "dark {$primary} vibe instrumental sample",
            $notes !== '' ? "{$notes} vinyl sample" : null,
            'obscure library music thriller vinyl',
            "{$mood} boom bap sample royalty free",
        ])));

        $drumBreakKeywords = array_values(array_unique(array_filter([
            "drum break {$bpm} bpm vinyl",
            "{$profile['drums_style']} drum break boom bap",
            'obscure 1960s drum break dusty',
            'soul drum break dry roomy',
            "{$bpm} bpm drum break sample pack free",
            'classic hip hop drum breaks compilation',
            $notes !== '' && str_contains(strtolower($notes), 'drum')
                ? "{$notes} drum break"
                : 'grimy boom bap drums one shot break',
        ])));

        $channels = [
            [
                'name' => 'Tracklib (cleared samples)',
                'why' => 'Samples con licencia clara si quieres publicar sin drama legal.',
                'url' => 'https://www.youtube.com/@Tracklib',
            ],
            [
                'name' => $profile['channel_name'],
                'why' => $profile['channel_why'],
                'url' => $profile['channel_url'],
            ],
            [
                'name' => 'Drum breaks / breakbeats compilations',
                'why' => 'Breaks clásicos y oscuros para chops de kit al BPM del brief.',
                'url' => 'https://www.youtube.com/results?search_query='.rawurlencode("drum breaks {$bpm} bpm vinyl boom bap"),
            ],
            [
                'name' => 'Royalty Free drum breaks',
                'why' => 'Breaks libres para sketches rápidos sin copyright drama.',
                'url' => 'https://www.youtube.com/results?search_query=royalty+free+drum+breaks+boom+bap',
            ],
            [
                'name' => 'Library / rare digs',
                'why' => "Busca {$profile['hunt_focus']} en vinyl uploads.",
                'url' => 'https://www.youtube.com/results?search_query='.rawurlencode($profile['yt_melody']),
            ],
        ];

        return $this->enrichSampleHunt([
            'keywords' => $keywords,
            'drum_break_keywords' => $drumBreakKeywords,
            'samplette_filters' => [
                'Genre' => $profile['genre'],
                'Style' => $profile['style'],
                'Region' => $profile['region'],
                'Year' => $profile['year'],
                'Tempo' => "{$tempoLow}-{$tempoHigh}",
                'Key' => $key,
            ],
            'samplette_drums_filters' => [
                'Genre' => $profile['drums_genre'],
                'Style' => $profile['drums_style'],
                'Region' => $profile['drums_region'],
                'Year' => $profile['drums_year'],
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
            $seed = $this->seed(
                (string) ($input['artists'] ?? ''),
                (string) ($input['mood'] ?? ''),
                (string) ($input['notes'] ?? ''),
            );
            $profile = $this->sampleProfile(
                (string) ($input['mood'] ?? ''),
                (string) ($input['notes'] ?? ''),
                $seed,
            );
            $tempoLow = max(50, $bpm - 15);
            $tempoHigh = $bpm + 10;

            return $this->buildSampleHunt($input, $bpm, $key, $profile, $tempoLow, $tempoHigh);
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
     * @return array<string, string>
     */
    private function sampleProfile(string $mood, string $notes, int $seed): array
    {
        $blob = strtolower($mood.' '.$notes);

        $profiles = [
            'italian' => [
                'genre' => 'Soundtrack',
                'style' => 'Film Score',
                'region' => 'Italy',
                'year' => '1968-1978',
                'instrument' => 'piano',
                'genre_label' => 'italian soundtrack',
                'region_label' => 'italian',
                'era' => '1970s',
                'hunt_focus' => 'piano / soundtrack italiano',
                'yt_melody' => '1970s italian soundtrack piano dusty',
                'ref' => 'Chop de soundtrack italiano: notas cortas, reverb de sala',
                'drums_genre' => 'Funk',
                'drums_style' => 'Soul',
                'drums_region' => 'USA',
                'drums_year' => '1968-1976',
                'channel_name' => 'Italian library / poliziotteschi digs',
                'channel_why' => 'Piano y orquesta oscura tipo thriller 70s.',
                'channel_url' => 'https://www.youtube.com/results?search_query=italian+soundtrack+1970s+vinyl',
            ],
            'jazz' => [
                'genre' => 'Jazz',
                'style' => 'Soul-Jazz',
                'region' => 'USA',
                'year' => '1965-1975',
                'instrument' => 'jazz guitar',
                'genre_label' => 'soul jazz',
                'region_label' => 'american',
                'era' => '1960s',
                'hunt_focus' => 'guitarra / organ jazz sucio',
                'yt_melody' => 'obscure soul jazz guitar 1968 vinyl',
                'ref' => 'Soul-jazz: guitarra o organ, swing suave, room corto',
                'drums_genre' => 'Jazz',
                'drums_style' => 'Hard Bop',
                'drums_region' => 'USA',
                'drums_year' => '1960-1972',
                'channel_name' => 'Pell (crate digs / vinyl finds)',
                'channel_why' => 'Digs oscuros útiles para chops jazz / boom bap.',
                'channel_url' => 'https://www.youtube.com/@Pell',
            ],
            'japanese' => [
                'genre' => 'City Pop',
                'style' => 'Funk',
                'region' => 'Japan',
                'year' => '1978-1988',
                'instrument' => 'electric piano',
                'genre_label' => 'city pop',
                'region_label' => 'japanese',
                'era' => '1980s',
                'hunt_focus' => 'city pop / EP japonesa',
                'yt_melody' => 'japanese city pop vinyl crackle electric piano',
                'ref' => 'City pop: EP cálido, chorus suave, no copies el hook entero',
                'drums_genre' => 'Funk',
                'drums_style' => 'Disco',
                'drums_region' => 'Japan',
                'drums_year' => '1978-1986',
                'channel_name' => 'Japanese vinyl / city pop digs',
                'channel_why' => 'Bucles melódicos y drums limpios de era 80s JP.',
                'channel_url' => 'https://www.youtube.com/results?search_query=japanese+city+pop+vinyl+sample',
            ],
            'soul' => [
                'genre' => 'Soul',
                'style' => 'Deep Soul',
                'region' => 'USA',
                'year' => '1968-1976',
                'instrument' => 'strings',
                'genre_label' => 'deep soul',
                'region_label' => 'american',
                'era' => '1970s',
                'hunt_focus' => 'cuerdas / coro soul dusty',
                'yt_melody' => 'dusty soul vinyl loop minor key strings',
                'ref' => 'Deep soul: cuerdas o coro, filtro LP, crackle',
                'drums_genre' => 'Funk',
                'drums_style' => 'Soul',
                'drums_region' => 'USA',
                'drums_year' => '1968-1976',
                'channel_name' => 'Tracklib (cleared samples)',
                'channel_why' => 'Soul/funk cleared para leases más seguros.',
                'channel_url' => 'https://www.youtube.com/@Tracklib',
            ],
            'library' => [
                'genre' => 'Library',
                'style' => 'Thriller',
                'region' => 'UK',
                'year' => '1970-1982',
                'instrument' => ' Rhodes',
                'genre_label' => 'library music',
                'region_label' => 'british',
                'era' => '1970s',
                'hunt_focus' => 'library thriller / suspense cue',
                'yt_melody' => 'library music thriller 1970s vinyl',
                'ref' => 'Library UK: suspense cues, Rhodes, perc metálica',
                'drums_genre' => 'Funk',
                'drums_style' => 'Breaks',
                'drums_region' => 'UK',
                'drums_year' => '1970-1980',
                'channel_name' => 'SuperDuperMusic (library / rare)',
                'channel_why' => 'Library music y rarezas para chops melódicos.',
                'channel_url' => 'https://www.youtube.com/results?search_query=library+music+1970s+vinyl',
            ],
            'french' => [
                'genre' => 'Soundtrack',
                'style' => 'Chanson',
                'region' => 'France',
                'year' => '1965-1975',
                'instrument' => 'accordion / nylon guitar',
                'genre_label' => 'french soundtrack',
                'region_label' => 'french',
                'era' => '1960s',
                'hunt_focus' => 'soundtrack francés / chanson oscura',
                'yt_melody' => '1960s french soundtrack minor key vinyl',
                'ref' => 'Chanson/noir francés: guitarra nylon o accordion filtrado',
                'drums_genre' => 'Jazz',
                'drums_style' => 'Swing',
                'drums_region' => 'France',
                'drums_year' => '1960-1972',
                'channel_name' => 'French library / OST digs',
                'channel_why' => 'OSTs francesas con mood noir para chops.',
                'channel_url' => 'https://www.youtube.com/results?search_query=french+soundtrack+1960s+vinyl',
            ],
        ];

        $key = match (true) {
            str_contains($blob, 'ital') || str_contains($blob, 'soundtrack') && str_contains($blob, 'piano') => 'italian',
            str_contains($blob, 'japan') || str_contains($blob, 'city pop') || str_contains($blob, 'anime') => 'japanese',
            str_contains($blob, 'french') || str_contains($blob, 'francia') || str_contains($blob, 'paris') => 'french',
            str_contains($blob, 'library') || str_contains($blob, 'thriller') || str_contains($blob, 'suspense') => 'library',
            str_contains($blob, 'jazz') || str_contains($blob, 'guitar') || str_contains($blob, 'organ') => 'jazz',
            str_contains($blob, 'soul') || str_contains($blob, 'string') || str_contains($blob, 'choir') || str_contains($blob, 'coro') => 'soul',
            str_contains($blob, 'piano') => 'italian',
            default => ['library', 'jazz', 'soul', 'italian', 'french', 'japanese'][$seed % 6],
        };

        $profile = $profiles[$key];
        $profile['instrument'] = trim($profile['instrument']);

        return $profile;
    }

    private function instrumentHint(string $notes, string $mood): string
    {
        $blob = strtolower($notes.' '.$mood);

        return match (true) {
            str_contains($blob, 'guitar') => 'chops de guitarra cortos',
            str_contains($blob, 'piano') || str_contains($blob, 'rhodes') => 'chops de piano/Rhodes',
            str_contains($blob, 'string') || str_contains($blob, 'cuerda') => 'stabs de cuerdas filtradas',
            str_contains($blob, 'organ') => 'organ stabs sucios',
            str_contains($blob, 'flute') || str_contains($blob, 'flauta') => 'flute phrases cortas',
            default => 'chops cortos de melodía',
        };
    }

    private function titleFromNotes(string $notes, string $fallback): string
    {
        $word = preg_split('/[\s,\/]+/u', trim($notes))[0] ?? '';
        $word = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ]/u', '', $word) ?? '';
        if ($word === '' || mb_strlen($word) < 3) {
            return $fallback;
        }

        return ucfirst(mb_strtolower($word)).' Room';
    }

    private function seed(string $artists, string $mood, string $notes): int
    {
        return abs(crc32(mb_strtolower(trim("{$artists}|{$mood}|{$notes}"))));
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

    private function channelNiche(): string
    {
        return (string) config('ai.channel.niche');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function json(array $data): string
    {
        return (string) json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
