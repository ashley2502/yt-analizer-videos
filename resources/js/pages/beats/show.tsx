import { Head, Link, useForm, usePage } from '@inertiajs/react';
import {
    Clapperboard,
    ImageIcon,
    Sparkles,
    Wand2,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Beat = {
    id: number;
    title: string;
    artists: string | null;
    bpm: number | null;
    musical_key: string | null;
    mood: string | null;
    status: string;
    notes: string | null;
    brief: {
        bpm?: number;
        musical_key?: string;
        structure?: string;
        hook_idea?: string;
        session_plan?: string[];
        title_ideas?: string[];
        sample_hunt?: {
            youtube_searches?: Array<{ label: string; url: string }>;
            samplette?: { url: string; how?: string; filters?: Record<string, string> };
            youtube_channels?: Array<{ name: string; why: string; url: string }>;
            disclaimer?: string;
        };
    } | null;
    publish_pack: {
        titles?: string[];
        description?: string;
        tags?: string[];
        checklist?: string[];
    } | null;
    shorts_plan: {
        caption?: string;
        windows?: Array<{ label: string; start_sec: number; end_sec: number }>;
        workflow?: string[];
    } | null;
    analyses: Array<{
        id: number;
        type: string;
        score: number | null;
        engine: string;
    }>;
};

const STEPS = [
    {
        id: 'idea',
        label: 'Idea',
        tip: 'Nombre + artistas. Genera un brief antes del DAW.',
    },
    {
        id: 'sketch',
        label: 'Sketch',
        tip: 'Loop / drums / idea de 60–90s en el DAW.',
    },
    {
        id: 'mix',
        label: 'Mix',
        tip: 'Arreglo completo y mezcla usable.',
    },
    {
        id: 'ready',
        label: 'Listo',
        tip: 'Pack YouTube + thumbnail + Shorts.',
    },
    {
        id: 'uploaded',
        label: 'Subido',
        tip: 'Ya está en el canal.',
    },
] as const;

function nextAction(beat: Beat): { href: string; label: string; why: string } {
    const id = beat.id;
    if (!beat.brief) {
        return {
            href: `/tools/brief?beat_id=${id}`,
            label: 'Generar brief',
            why: 'Te da BPM, key, estructura y plan de 25 min para abrir el DAW.',
        };
    }
    if (beat.status === 'idea' || beat.status === 'sketch') {
        return {
            href: `/tools/brief?beat_id=${id}`,
            label: 'Ver / regenerar brief',
            why: 'Sigue produciendo. Cuando el beat suene completo, pásalo a Mix.',
        };
    }
    if (beat.status === 'mix' || !beat.publish_pack) {
        return {
            href: `/tools/publish-pack?beat_id=${id}`,
            label: 'Generar pack YouTube',
            why: 'Títulos, descripción, tags y checklist para subir.',
        };
    }
    if (!beat.shorts_plan) {
        return {
            href: `/tools/shorts?beat_id=${id}`,
            label: 'Plan de Shorts',
            why: 'Cortes sin cámara para alimentar el algoritmo.',
        };
    }
    return {
        href: `/tools/thumbnail?beat_id=${id}`,
        label: 'Auditar thumbnail',
        why: 'Revisa CTR visual antes de publicar.',
    };
}

export default function BeatShow() {
    const { beat, aiEnabled } = usePage().props as unknown as {
        beat: Beat;
        aiEnabled: boolean;
    };

    const form = useForm({
        title: beat.title,
        artists: beat.artists ?? '',
        bpm: beat.bpm ?? '',
        musical_key: beat.musical_key ?? '',
        mood: beat.mood ?? '',
        status: beat.status,
        notes: beat.notes ?? '',
    });

    const action = nextAction(beat);
    const stepIndex = STEPS.findIndex((s) => s.id === beat.status);

    return (
        <>
            <Head title={beat.title} />
            <div className="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4">
                <div>
                    <p className="text-sm text-muted-foreground">
                        Beat · {aiEnabled ? 'IA ON' : 'modo local'}
                    </p>
                    <h1 className="text-2xl font-semibold">{beat.title}</h1>
                    <p className="text-sm text-muted-foreground">
                        {beat.artists || 'Sin artistas Type Beat'}
                        {beat.bpm ? ` · ${beat.bpm} BPM` : ''}
                        {beat.musical_key ? ` · ${beat.musical_key}` : ''}
                    </p>
                </div>

                <div className="rounded-xl border p-4">
                    <p className="mb-3 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                        Pipeline del beat
                    </p>
                    <ol className="grid gap-2 sm:grid-cols-5">
                        {STEPS.map((step, i) => {
                            const active = step.id === beat.status;
                            const done = stepIndex > i;
                            return (
                                <li
                                    key={step.id}
                                    className={`rounded-lg border p-3 text-sm ${
                                        active
                                            ? 'border-primary bg-primary/5'
                                            : done
                                              ? 'bg-muted/40'
                                              : 'opacity-60'
                                    }`}
                                >
                                    <p className="font-medium">{step.label}</p>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {step.tip}
                                    </p>
                                </li>
                            );
                        })}
                    </ol>
                </div>

                <div className="rounded-xl border bg-card p-4">
                    <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                        Qué sigue
                    </p>
                    <p className="mt-1 text-lg font-medium">{action.label}</p>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {action.why}
                    </p>
                    <Button asChild className="mt-3">
                        <Link href={action.href}>{action.label}</Link>
                    </Button>
                </div>

                <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    <ToolLink
                        href={`/tools/brief?beat_id=${beat.id}`}
                        icon={Sparkles}
                        title="Brief"
                        done={Boolean(beat.brief)}
                    />
                    <ToolLink
                        href={`/tools/publish-pack?beat_id=${beat.id}`}
                        icon={Wand2}
                        title="Pack YT"
                        done={Boolean(beat.publish_pack)}
                    />
                    <ToolLink
                        href={`/tools/shorts?beat_id=${beat.id}`}
                        icon={Clapperboard}
                        title="Shorts"
                        done={Boolean(beat.shorts_plan)}
                    />
                    <ToolLink
                        href={`/tools/thumbnail?beat_id=${beat.id}`}
                        icon={ImageIcon}
                        title="Thumbnail"
                        done={beat.analyses.some((a) => a.type === 'thumbnail')}
                    />
                </div>

                <div className="flex flex-col gap-6 lg:flex-row">
                    <form
                        className="w-full max-w-md space-y-3 rounded-xl border p-4"
                        onSubmit={(e) => {
                            e.preventDefault();
                            form.transform((data) => ({
                                ...data,
                                bpm: data.bpm === '' ? null : Number(data.bpm),
                            }));
                            form.put(`/beats/${beat.id}`);
                        }}
                    >
                        <h2 className="font-medium">Datos del beat</h2>
                        <Label>Nombre</Label>
                        <Input
                            value={form.data.title}
                            onChange={(e) =>
                                form.setData('title', e.target.value)
                            }
                        />
                        <Label>Artistas Type Beat</Label>
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
                                        form.setData(
                                            'musical_key',
                                            e.target.value,
                                        )
                                    }
                                />
                            </div>
                        </div>
                        <Label>Estado</Label>
                        <select
                            className="h-9 w-full rounded-md border bg-background px-3 text-sm"
                            value={form.data.status}
                            onChange={(e) =>
                                form.setData('status', e.target.value)
                            }
                        >
                            {STEPS.map((s) => (
                                <option key={s.id} value={s.id}>
                                    {s.label}
                                </option>
                            ))}
                        </select>
                        <p className="text-xs text-muted-foreground">
                            Cuando termines una etapa en el DAW, súbela aquí
                            (ej. sketch → mix).
                        </p>
                        <Button type="submit" disabled={form.processing}>
                            Guardar cambios
                        </Button>
                    </form>

                    <div className="flex-1 space-y-4">
                        {beat.brief && (
                            <section className="rounded-xl border p-4">
                                <h2 className="font-medium">Brief guardado</h2>
                                <dl className="mt-3 space-y-2 text-sm">
                                    <Row
                                        k="BPM / Key"
                                        v={`${beat.brief.bpm ?? '—'} · ${beat.brief.musical_key ?? '—'}`}
                                    />
                                    <Row
                                        k="Estructura"
                                        v={beat.brief.structure}
                                    />
                                    <Row k="Hook" v={beat.brief.hook_idea} />
                                </dl>
                                {beat.brief.session_plan && (
                                    <ol className="mt-3 list-decimal space-y-1 pl-4 text-sm text-muted-foreground">
                                        {beat.brief.session_plan.map((step) => (
                                            <li key={step}>{step}</li>
                                        ))}
                                    </ol>
                                )}
                                {beat.brief.sample_hunt?.youtube_searches && (
                                    <div className="mt-4 border-t pt-3">
                                        <p className="mb-2 text-sm font-medium">
                                            Sample hunt
                                        </p>
                                        <div className="flex flex-wrap gap-2">
                                            {beat.brief.sample_hunt.samplette
                                                ?.url && (
                                                <a
                                                    href={
                                                        beat.brief.sample_hunt
                                                            .samplette.url
                                                    }
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="rounded-full border px-3 py-1 text-xs hover:bg-muted/40"
                                                >
                                                    Samplette
                                                </a>
                                            )}
                                            {beat.brief.sample_hunt.youtube_searches
                                                .slice(0, 4)
                                                .map((s) => (
                                                    <a
                                                        key={s.url}
                                                        href={s.url}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="rounded-full border px-3 py-1 text-xs hover:bg-muted/40"
                                                    >
                                                        YT: {s.label.slice(0, 28)}
                                                        …
                                                    </a>
                                                ))}
                                        </div>
                                    </div>
                                )}
                            </section>
                        )}

                        {beat.publish_pack && (
                            <section className="rounded-xl border p-4">
                                <h2 className="font-medium">Pack YouTube</h2>
                                <ul className="mt-3 space-y-1 text-sm">
                                    {(beat.publish_pack.titles ?? [])
                                        .slice(0, 3)
                                        .map((t) => (
                                            <li key={t}>• {t}</li>
                                        ))}
                                </ul>
                                {beat.publish_pack.description && (
                                    <p className="mt-3 whitespace-pre-wrap text-xs text-muted-foreground">
                                        {beat.publish_pack.description.slice(
                                            0,
                                            280,
                                        )}
                                        …
                                    </p>
                                )}
                            </section>
                        )}

                        {beat.shorts_plan && (
                            <section className="rounded-xl border p-4">
                                <h2 className="font-medium">Plan Shorts</h2>
                                <p className="mt-2 text-sm">
                                    {beat.shorts_plan.caption}
                                </p>
                                <ul className="mt-3 space-y-1 text-sm text-muted-foreground">
                                    {(beat.shorts_plan.windows ?? []).map(
                                        (w) => (
                                            <li key={w.label}>
                                                {w.label}: {w.start_sec}s–
                                                {w.end_sec}s
                                            </li>
                                        ),
                                    )}
                                </ul>
                            </section>
                        )}

                        {!beat.brief &&
                            !beat.publish_pack &&
                            !beat.shorts_plan && (
                                <p className="rounded-xl border border-dashed p-6 text-sm text-muted-foreground">
                                    Todavía no hay brief ni pack. Usa “Qué
                                    sigue” arriba: primero el brief, luego
                                    produces en el DAW.
                                </p>
                            )}
                    </div>
                </div>
            </div>
        </>
    );
}

function ToolLink({
    href,
    icon: Icon,
    title,
    done,
}: {
    href: string;
    icon: React.ComponentType<{ className?: string }>;
    title: string;
    done: boolean;
}) {
    return (
        <Link
            href={href}
            className="flex items-center gap-3 rounded-xl border p-3 text-sm transition hover:bg-muted/40"
        >
            <Icon className="size-4 text-muted-foreground" />
            <span className="font-medium">{title}</span>
            <span className="ml-auto text-xs text-muted-foreground">
                {done ? 'listo' : 'pendiente'}
            </span>
        </Link>
    );
}

function Row({ k, v }: { k: string; v?: string | null }) {
    if (!v) {
        return null;
    }
    return (
        <div className="flex gap-3">
            <dt className="w-24 shrink-0 text-muted-foreground">{k}</dt>
            <dd>{v}</dd>
        </div>
    );
}

BeatShow.layout = {
    breadcrumbs: [
        { title: 'Beats', href: '/beats' },
        { title: 'Detalle', href: '#' },
    ],
};
