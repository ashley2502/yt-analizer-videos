import { Head, useForm, usePage } from '@inertiajs/react';
import { Check, Copy, ExternalLink } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { copyText } from '@/hooks/use-clipboard';

type CopyField = { field: string; value: string };

type SampleHunt = {
    keywords: string[];
    drum_break_keywords?: string[];
    youtube_searches: Array<{ label: string; query?: string; url: string }>;
    drum_break_searches?: Array<{ label: string; query?: string; url: string }>;
    samplette: {
        url: string;
        how: string;
        filters: Record<string, string>;
        copy_fields?: CopyField[];
        copy_all?: string;
        drums_copy_fields?: CopyField[];
        drums_copy_all?: string;
    };
    youtube_copy_all?: string;
    youtube_drums_copy_all?: string;
    youtube_channels: Array<{ name: string; why: string; url: string }>;
    disclaimer: string;
};

type Brief = {
    engine: string;
    bpm: number;
    musical_key: string;
    structure: string;
    hook_idea: string;
    references: string[];
    title_ideas: string[];
    session_plan: string[];
    sample_hunt?: SampleHunt;
};

type Beat = {
    id: number;
    title: string;
    artists: string | null;
    mood: string | null;
};

export default function BriefTool() {
    const { aiEnabled, beats, brief, form: previous } = usePage().props as unknown as {
        aiEnabled: boolean;
        beats: Beat[];
        brief: Brief | null;
        form?: Record<string, string>;
    };

    const form = useForm({
        beat_id: previous?.beat_id ?? '',
        artists: previous?.artists ?? 'Westside Gunn x Griselda',
        mood: previous?.mood ?? 'dark, dusty, grimy',
        notes: previous?.notes ?? '',
    });

    return (
        <>
            <Head title="Brief de beat" />
            <div className="flex flex-col gap-6 p-4 lg:flex-row">
                <form
                    className="w-full max-w-md space-y-3 rounded-xl border p-4"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.transform((data) => ({
                            ...data,
                            beat_id: data.beat_id || null,
                        }));
                        form.post('/tools/brief');
                    }}
                >
                    <h1 className="text-xl font-semibold">Brief de producción</h1>
                    <p className="text-sm text-muted-foreground">
                        Incluye filtros listos para copiar a Samplette y queries
                        para YouTube. {aiEnabled ? 'IA ON.' : 'Templates locales.'}
                    </p>

                    <Label>Vincular beat</Label>
                    <select
                        className="h-9 w-full rounded-md border bg-background px-3 text-sm"
                        value={form.data.beat_id}
                        onChange={(e) => {
                            const id = e.target.value;
                            form.setData('beat_id', id);
                            const beat = beats.find((b) => String(b.id) === id);
                            if (beat) {
                                form.setData('artists', beat.artists ?? '');
                                form.setData('mood', beat.mood ?? '');
                            }
                        }}
                    >
                        <option value="">Sin vincular</option>
                        {beats.map((b) => (
                            <option key={b.id} value={b.id}>
                                {b.title}
                            </option>
                        ))}
                    </select>

                    <Label>Artistas / estilo</Label>
                    <Input
                        value={form.data.artists}
                        onChange={(e) =>
                            form.setData('artists', e.target.value)
                        }
                    />
                    <Label>Mood</Label>
                    <Input
                        value={form.data.mood}
                        onChange={(e) => form.setData('mood', e.target.value)}
                    />
                    <Label>Notas (ayuda al sample hunt)</Label>
                    <Input
                        value={form.data.notes}
                        onChange={(e) => form.setData('notes', e.target.value)}
                        placeholder="piano sucio, soundtrack italiano, guitarra jazz..."
                    />
                    <Button type="submit" disabled={form.processing}>
                        Generar brief
                    </Button>
                </form>

                <div className="flex-1 space-y-4">
                    {!brief && (
                        <p className="text-sm text-muted-foreground">
                            El brief te da BPM, key, estructura y packs
                            copiables para Samplette / YouTube.
                        </p>
                    )}
                    {brief && (
                        <>
                            <div className="space-y-4 rounded-xl border p-4">
                                <p className="text-sm text-muted-foreground">
                                    Engine: {brief.engine}
                                </p>
                                <p className="text-2xl font-semibold">
                                    {brief.bpm} BPM · {brief.musical_key}
                                </p>
                                <p>
                                    <span className="font-medium">
                                        Estructura:
                                    </span>{' '}
                                    {brief.structure}
                                </p>
                                <p>
                                    <span className="font-medium">
                                        Hook idea:
                                    </span>{' '}
                                    {brief.hook_idea}
                                </p>
                                <List
                                    title="Referencias"
                                    items={brief.references}
                                />
                                <List
                                    title="Nombres tentativos"
                                    items={brief.title_ideas}
                                />
                                <List
                                    title="Plan de sesión"
                                    items={brief.session_plan}
                                />
                            </div>

                            {brief.sample_hunt && (
                                <SampleHuntCard hunt={brief.sample_hunt} />
                            )}
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

function SampleHuntCard({ hunt }: { hunt: SampleHunt }) {
    return (
        <div className="space-y-4 rounded-xl border p-4">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 className="text-lg font-semibold">Sample hunt</h2>
                    <p className="text-sm text-muted-foreground">
                        {hunt.samplette.how}
                    </p>
                </div>
                <Button asChild variant="outline" size="sm">
                    <a
                        href={hunt.samplette.url}
                        target="_blank"
                        rel="noreferrer"
                    >
                        Abrir Samplette
                        <ExternalLink className="size-3.5" />
                    </a>
                </Button>
            </div>

            <CopyPack
                title="Samplette · melodía / vibes"
                fields={hunt.samplette.copy_fields ?? []}
                copyAll={hunt.samplette.copy_all}
                hint="Copia cada valor y pégalo en Filters (S) de Samplette."
            />

            <CopyPack
                title="Samplette · drum breaks"
                fields={hunt.samplette.drums_copy_fields ?? []}
                copyAll={hunt.samplette.drums_copy_all}
                hint="Misma idea: Funk/Soul + tempo. Escucha intros/outros."
            />

            <SearchCopyList
                title="YouTube · melodía / vibes"
                items={hunt.youtube_searches}
                copyAll={hunt.youtube_copy_all}
            />

            <SearchCopyList
                title="YouTube · drum breaks"
                items={hunt.drum_break_searches ?? []}
                copyAll={hunt.youtube_drums_copy_all}
            />

            <div>
                <p className="mb-2 font-medium">Canales / lugares útiles</p>
                <ul className="space-y-2">
                    {hunt.youtube_channels.map((channel) => (
                        <li key={channel.url + channel.name}>
                            <a
                                href={channel.url}
                                target="_blank"
                                rel="noreferrer"
                                className="block rounded-lg border p-3 transition hover:bg-muted/40"
                            >
                                <p className="flex items-center gap-2 text-sm font-medium">
                                    {channel.name}
                                    <ExternalLink className="size-3.5 text-muted-foreground" />
                                </p>
                                {channel.why && (
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {channel.why}
                                    </p>
                                )}
                            </a>
                        </li>
                    ))}
                </ul>
            </div>

            <p className="text-xs text-amber-700 dark:text-amber-400">
                {hunt.disclaimer}
            </p>
        </div>
    );
}

function CopyPack({
    title,
    fields,
    copyAll,
    hint,
}: {
    title: string;
    fields: CopyField[];
    copyAll?: string;
    hint: string;
}) {
    if (fields.length === 0) {
        return null;
    }

    return (
        <div className="rounded-lg border bg-muted/30 p-3">
            <div className="mb-2 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p className="font-medium">{title}</p>
                    <p className="text-xs text-muted-foreground">{hint}</p>
                </div>
                {copyAll && <CopyButton text={copyAll} label="Copiar todo" />}
            </div>
            <ul className="space-y-2">
                {fields.map((field) => (
                    <li
                        key={field.field}
                        className="flex items-center gap-2 rounded-md border bg-background px-3 py-2"
                    >
                        <div className="min-w-0 flex-1">
                            <p className="text-[11px] tracking-wide text-muted-foreground uppercase">
                                {field.field}
                            </p>
                            <p className="truncate text-sm font-medium">
                                {field.value}
                            </p>
                        </div>
                        <CopyButton text={field.value} />
                    </li>
                ))}
            </ul>
        </div>
    );
}

function SearchCopyList({
    title,
    items,
    copyAll,
}: {
    title: string;
    items: Array<{ label: string; query?: string; url: string }>;
    copyAll?: string;
}) {
    if (items.length === 0) {
        return null;
    }

    return (
        <div>
            <div className="mb-2 flex flex-wrap items-center justify-between gap-2">
                <p className="font-medium">{title}</p>
                {copyAll && <CopyButton text={copyAll} label="Copiar queries" />}
            </div>
            <ul className="space-y-2">
                {items.map((search) => {
                    const query = search.query ?? search.label;
                    return (
                        <li
                            key={search.url}
                            className="flex items-center gap-2 rounded-lg border p-3"
                        >
                            <a
                                href={search.url}
                                target="_blank"
                                rel="noreferrer"
                                className="flex min-w-0 flex-1 items-start gap-2 text-sm transition hover:underline"
                            >
                                <ExternalLink className="mt-0.5 size-3.5 shrink-0 text-muted-foreground" />
                                <span className="min-w-0 break-words">
                                    {search.label}
                                </span>
                            </a>
                            <CopyButton text={query} />
                        </li>
                    );
                })}
            </ul>
        </div>
    );
}

function CopyButton({ text, label }: { text: string; label?: string }) {
    const [copied, setCopied] = useState(false);

    return (
        <Button
            type="button"
            variant="outline"
            size="sm"
            className="shrink-0"
            onClick={async (event) => {
                event.preventDefault();
                event.stopPropagation();

                const ok = await copyText(text);

                if (!ok) {
                    toast.error('No se pudo copiar');

                    return;
                }

                setCopied(true);
                toast.success('Valor copiado');
                window.setTimeout(() => setCopied(false), 1500);
            }}
        >
            {copied ? (
                <Check className="size-3.5" />
            ) : (
                <Copy className="size-3.5" />
            )}
            {copied ? 'Valor copiado' : (label ?? 'Copiar')}
        </Button>
    );
}

function List({ title, items }: { title: string; items: string[] }) {
    return (
        <div>
            <p className="mb-1 font-medium">{title}</p>
            <ul className="list-disc pl-5 text-sm text-muted-foreground">
                {items.map((item) => (
                    <li key={item}>{item}</li>
                ))}
            </ul>
        </div>
    );
}

BriefTool.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Brief', href: '/tools/brief' },
    ],
};
