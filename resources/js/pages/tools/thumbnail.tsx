import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type BeatOption = { id: number; title: string; artists: string | null };

type Analysis = {
    id: number;
    score: number | null;
    engine: string;
    thumbnail_path: string | null;
    result: {
        summary?: string;
        checks?: Array<{ id: string; pass: boolean; message: string }>;
        recommendations?: string[];
        ai?: Record<string, unknown> | null;
        meta?: Record<string, unknown>;
    };
};

type PageProps = {
    aiEnabled: boolean;
    beats: BeatOption[];
    recent: Analysis[];
    selected: Analysis | null;
};

export default function ThumbnailTool() {
    const { aiEnabled, beats, recent, selected } = usePage()
        .props as unknown as PageProps;

    const form = useForm<{
        thumbnail: File | null;
        beat_id: string;
        title: string;
        artists: string;
    }>({
        thumbnail: null,
        beat_id: '',
        title: '',
        artists: '',
    });

    return (
        <>
            <Head title="Auditor thumbnail" />
            <div className="flex flex-col gap-6 p-4 lg:flex-row">
                <form
                    className="w-full max-w-md space-y-3 rounded-xl border p-4"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post('/tools/thumbnail', {
                            forceFormData: true,
                        });
                    }}
                >
                    <h1 className="text-xl font-semibold">Auditor thumbnail</h1>
                    <p className="text-sm text-muted-foreground">
                        Heurística local (letterbox, resolución) + visión IA si
                        hay API key. Ahora:{' '}
                        {aiEnabled ? 'IA activa' : 'solo local'}.
                    </p>

                    <div className="space-y-2">
                        <Label>Imagen</Label>
                        <Input
                            type="file"
                            accept="image/*"
                            onChange={(e) =>
                                form.setData(
                                    'thumbnail',
                                    e.target.files?.[0] ?? null,
                                )
                            }
                        />
                    </div>

                    <div className="space-y-2">
                        <Label>Beat (opcional)</Label>
                        <select
                            className="h-9 w-full rounded-md border bg-background px-3 text-sm"
                            value={form.data.beat_id}
                            onChange={(e) => {
                                const id = e.target.value;
                                form.setData('beat_id', id);
                                const beat = beats.find(
                                    (b) => String(b.id) === id,
                                );
                                if (beat) {
                                    form.setData('title', beat.title);
                                    form.setData(
                                        'artists',
                                        beat.artists ?? '',
                                    );
                                }
                            }}
                        >
                            <option value="">—</option>
                            {beats.map((b) => (
                                <option key={b.id} value={b.id}>
                                    {b.title}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="space-y-2">
                        <Label>Título contexto</Label>
                        <Input
                            value={form.data.title}
                            onChange={(e) =>
                                form.setData('title', e.target.value)
                            }
                        />
                    </div>
                    <div className="space-y-2">
                        <Label>Artistas</Label>
                        <Input
                            value={form.data.artists}
                            onChange={(e) =>
                                form.setData('artists', e.target.value)
                            }
                        />
                    </div>

                    <Button type="submit" disabled={form.processing}>
                        Analizar
                    </Button>
                </form>

                <div className="flex-1 space-y-4">
                    {selected ? (
                        <div className="rounded-xl border p-4">
                            <div className="flex flex-wrap items-end justify-between gap-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">
                                        Score · {selected.engine}
                                    </p>
                                    <p className="text-4xl font-semibold">
                                        {selected.score ?? '—'}/100
                                    </p>
                                </div>
                                {selected.thumbnail_path && (
                                    <img
                                        src={`/storage/${selected.thumbnail_path}`}
                                        alt="Thumbnail"
                                        className="max-h-40 rounded-lg border object-cover"
                                    />
                                )}
                            </div>
                            <p className="mt-3 text-sm">
                                {selected.result.summary}
                            </p>
                            <ul className="mt-4 space-y-2 text-sm">
                                {(selected.result.checks ?? []).map((c) => (
                                    <li
                                        key={c.id}
                                        className="rounded-lg border px-3 py-2"
                                    >
                                        <span
                                            className={
                                                c.pass
                                                    ? 'text-emerald-600'
                                                    : 'text-amber-600'
                                            }
                                        >
                                            {c.pass ? 'OK' : 'FIX'}
                                        </span>{' '}
                                        {c.message}
                                    </li>
                                ))}
                            </ul>
                            <div className="mt-4">
                                <p className="mb-2 font-medium">
                                    Recomendaciones
                                </p>
                                <ul className="list-disc space-y-1 pl-5 text-sm text-muted-foreground">
                                    {(selected.result.recommendations ?? []).map(
                                        (r) => (
                                            <li key={r}>{r}</li>
                                        ),
                                    )}
                                </ul>
                            </div>
                        </div>
                    ) : (
                        <p className="text-sm text-muted-foreground">
                            Sube una thumbnail para ver el score.
                        </p>
                    )}

                    <div className="rounded-xl border p-4">
                        <p className="mb-2 font-medium">Recientes</p>
                        <ul className="space-y-1 text-sm">
                            {recent.map((a) => (
                                <li key={a.id}>
                                    <button
                                        type="button"
                                        className="text-left hover:underline"
                                        onClick={() =>
                                            router.get(
                                                `/tools/thumbnail?analysis=${a.id}`,
                                            )
                                        }
                                    >
                                        #{a.id} · {a.score}/100 · {a.engine}
                                    </button>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
        </>
    );
}

ThumbnailTool.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Thumbnail', href: '/tools/thumbnail' },
    ],
};
