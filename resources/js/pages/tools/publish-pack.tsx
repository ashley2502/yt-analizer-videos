import { Head, useForm, usePage } from '@inertiajs/react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { copyText } from '@/hooks/use-clipboard';

type Pack = {
    engine: string;
    titles: string[];
    short_titles: string[];
    description: string;
    tags: string[];
    chapters: string[];
    pinned_comment: string;
    checklist: string[];
    title_max?: number;
};

type Beat = {
    id: number;
    title: string;
    artists: string | null;
    bpm: number | null;
    musical_key: string | null;
    mood: string | null;
};

export default function PublishPackTool() {
    const { aiEnabled, beats, pack, form: previous } = usePage().props as unknown as {
        aiEnabled: boolean;
        beats: Beat[];
        pack: Pack | null;
        form?: Record<string, string>;
    };

    const form = useForm({
        beat_id: previous?.beat_id ?? '',
        title: previous?.title ?? '',
        artists: previous?.artists ?? '',
        bpm: previous?.bpm ?? '',
        musical_key: previous?.musical_key ?? '',
        mood: previous?.mood ?? 'dark boom bap',
        notes: previous?.notes ?? '',
    });

    return (
        <>
            <Head title="Pack YouTube" />
            <div className="flex flex-col gap-6 p-4 lg:flex-row">
                <form
                    className="w-full max-w-md space-y-3 rounded-xl border p-4"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.transform((data) => ({
                            ...data,
                            bpm: data.bpm === '' ? null : Number(data.bpm),
                            beat_id: data.beat_id || null,
                        }));
                        form.post('/tools/publish-pack');
                    }}
                >
                    <h1 className="text-xl font-semibold">Pack YouTube</h1>
                    <p className="text-sm text-muted-foreground">
                        Títulos SEO, descripción, tags y checklist.{' '}
                        {aiEnabled ? 'Con IA' : 'Templates locales'}.
                    </p>

                    <Label>Beat</Label>
                    <select
                        className="h-9 w-full rounded-md border bg-background px-3 text-sm"
                        value={form.data.beat_id}
                        onChange={(e) => {
                            const id = e.target.value;
                            form.setData('beat_id', id);
                            const beat = beats.find((b) => String(b.id) === id);
                            if (beat) {
                                form.setData('title', beat.title);
                                form.setData('artists', beat.artists ?? '');
                                form.setData(
                                    'bpm',
                                    beat.bpm ? String(beat.bpm) : '',
                                );
                                form.setData(
                                    'musical_key',
                                    beat.musical_key ?? '',
                                );
                                form.setData('mood', beat.mood ?? '');
                            }
                        }}
                    >
                        <option value="">Manual</option>
                        {beats.map((b) => (
                            <option key={b.id} value={b.id}>
                                {b.title}
                            </option>
                        ))}
                    </select>

                    <Label>Título beat</Label>
                    <Input
                        value={form.data.title}
                        onChange={(e) => form.setData('title', e.target.value)}
                    />
                    <Label>Artistas</Label>
                    <Input
                        value={form.data.artists}
                        onChange={(e) =>
                            form.setData('artists', e.target.value)
                        }
                    />
                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <Label>BPM</Label>
                            <Input
                                value={form.data.bpm}
                                onChange={(e) =>
                                    form.setData('bpm', e.target.value)
                                }
                            />
                        </div>
                        <div>
                            <Label>Key</Label>
                            <Input
                                value={form.data.musical_key}
                                onChange={(e) =>
                                    form.setData('musical_key', e.target.value)
                                }
                            />
                        </div>
                    </div>
                    <Button type="submit" disabled={form.processing}>
                        Generar pack
                    </Button>
                </form>

                <div className="flex-1 space-y-4">
                    {!pack && (
                        <p className="text-sm text-muted-foreground">
                            Genera un pack para copiar/pegar en YouTube. Los
                            títulos se limitan a 60 caracteres (lo que se ve
                            bien en móvil).
                        </p>
                    )}
                    {pack && (
                        <>
                            <Section title={`Títulos · máx ${pack.title_max ?? 60} caracteres (${pack.engine})`}>
                                {pack.titles.map((t) => (
                                    <CopyRow
                                        key={t}
                                        text={t}
                                        maxChars={pack.title_max ?? 60}
                                    />
                                ))}
                            </Section>
                            <Section title="Títulos Shorts · máx 40">
                                {pack.short_titles.map((t) => (
                                    <CopyRow key={t} text={t} maxChars={40} />
                                ))}
                            </Section>
                            <Section title="Descripción">
                                <CopyRow text={pack.description} multiline />
                            </Section>
                            <Section title="Tags">
                                <CopyRow text={pack.tags.join(', ')} />
                            </Section>
                            <Section title="Capítulos">
                                <CopyRow text={pack.chapters.join('\n')} multiline />
                            </Section>
                            <Section title="Pinned comment">
                                <CopyRow text={pack.pinned_comment} />
                            </Section>
                            <Section title="Checklist">
                                <ul className="list-disc pl-5 text-sm">
                                    {pack.checklist.map((c) => (
                                        <li key={c}>{c}</li>
                                    ))}
                                </ul>
                            </Section>
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

function Section({
    title,
    children,
}: {
    title: string;
    children: React.ReactNode;
}) {
    return (
        <div className="rounded-xl border p-4">
            <h2 className="mb-3 font-medium">{title}</h2>
            <div className="space-y-2">{children}</div>
        </div>
    );
}

function CopyRow({
    text,
    multiline = false,
    maxChars,
}: {
    text: string;
    multiline?: boolean;
    maxChars?: number;
}) {
    const len = text.length;
    const over = maxChars !== undefined && len > maxChars;

    return (
        <div className="space-y-1">
            <div className="flex gap-2">
                <pre
                    className={`flex-1 rounded-md bg-muted/50 p-2 text-xs whitespace-pre-wrap ${multiline ? '' : ''}`}
                >
                    {text}
                </pre>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={async () => {
                        const ok = await copyText(text);
                        if (ok) {
                            toast.success('Valor copiado');
                        } else {
                            toast.error('No se pudo copiar');
                        }
                    }}
                >
                    Copiar
                </Button>
            </div>
            {maxChars !== undefined && (
                <p
                    className={`text-xs ${over ? 'text-amber-600' : 'text-muted-foreground'}`}
                >
                    {len}/{maxChars} caracteres
                    {over ? ' · se corta en móvil' : ''}
                </p>
            )}
        </div>
    );
}

PublishPackTool.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Pack YouTube', href: '/tools/publish-pack' },
    ],
};
