import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ArrowRight, Music2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Beat = {
    id: number;
    title: string;
    artists: string | null;
    bpm: number | null;
    musical_key: string | null;
    status: string;
};

const STATUS_LABEL: Record<string, string> = {
    idea: 'Idea',
    sketch: 'Sketch',
    mix: 'Mix',
    ready: 'Listo',
    uploaded: 'Subido',
};

export default function BeatsIndex() {
    const { beats } = usePage().props as unknown as { beats: Beat[] };
    const form = useForm({
        title: '',
        artists: '',
        bpm: '',
        musical_key: '',
        mood: 'dark boom bap',
        notes: '',
    });

    return (
        <>
            <Head title="Beats" />
            <div className="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4">
                <div className="space-y-2">
                    <h1 className="text-2xl font-semibold">Mis beats</h1>
                    <p className="max-w-2xl text-sm text-muted-foreground">
                        Un <strong>beat</strong> es un proyecto musical (ej.
                        “Pays”). Aquí lo registras y lo mueves de{' '}
                        <em>idea → sketch → mix → listo → subido</em>. El{' '}
                        <Link href="/schedule" className="underline">
                            Schedule
                        </Link>{' '}
                        es aparte: son las tareas de 25 min de la semana para no
                        perder el hábito.
                    </p>
                </div>

                <div className="flex flex-col gap-6 lg:flex-row">
                    <div className="flex-1 space-y-3">
                        {beats.length === 0 ? (
                            <div className="rounded-xl border border-dashed p-8 text-center">
                                <Music2 className="mx-auto mb-3 size-8 text-muted-foreground" />
                                <p className="font-medium">Aún no hay beats</p>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Crea uno a la derecha con el nombre del Type
                                    Beat. Luego genera el brief y abre el DAW.
                                </p>
                            </div>
                        ) : (
                            <ul className="space-y-2">
                                {beats.map((beat) => (
                                    <li key={beat.id}>
                                        <Link
                                            href={`/beats/${beat.id}`}
                                            className="flex items-center justify-between gap-3 rounded-xl border p-4 transition hover:bg-muted/40"
                                        >
                                            <div className="min-w-0">
                                                <p className="font-medium">
                                                    {beat.title}
                                                </p>
                                                <p className="truncate text-sm text-muted-foreground">
                                                    {beat.artists ||
                                                        'Sin artistas Type Beat'}
                                                    {beat.bpm
                                                        ? ` · ${beat.bpm} BPM`
                                                        : ''}
                                                    {beat.musical_key
                                                        ? ` · ${beat.musical_key}`
                                                        : ''}
                                                </p>
                                            </div>
                                            <span className="shrink-0 rounded-full border px-2.5 py-1 text-xs">
                                                {STATUS_LABEL[beat.status] ??
                                                    beat.status}
                                            </span>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>

                    <form
                        className="w-full max-w-md space-y-3 rounded-xl border bg-card p-4"
                        onSubmit={(e) => {
                            e.preventDefault();
                            form.transform((data) => ({
                                ...data,
                                bpm: data.bpm ? Number(data.bpm) : null,
                            }));
                            form.post('/beats');
                        }}
                    >
                        <div>
                            <h2 className="font-medium">Nuevo beat</h2>
                            <p className="text-xs text-muted-foreground">
                                Solo necesitas un nombre. BPM y key pueden
                                venir después del brief.
                            </p>
                        </div>
                        <Field label="Nombre del beat">
                            <Input
                                value={form.data.title}
                                onChange={(e) =>
                                    form.setData('title', e.target.value)
                                }
                                placeholder="Pays"
                                required
                            />
                        </Field>
                        <Field label="Type Beat de… (opcional)">
                            <Input
                                value={form.data.artists}
                                onChange={(e) =>
                                    form.setData('artists', e.target.value)
                                }
                                placeholder="Westside Gunn x Griselda"
                            />
                        </Field>
                        <div className="grid grid-cols-2 gap-3">
                            <Field label="BPM (opcional)">
                                <Input
                                    value={form.data.bpm}
                                    onChange={(e) =>
                                        form.setData('bpm', e.target.value)
                                    }
                                    placeholder="68"
                                />
                            </Field>
                            <Field label="Key (opcional)">
                                <Input
                                    value={form.data.musical_key}
                                    onChange={(e) =>
                                        form.setData(
                                            'musical_key',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="A minor"
                                />
                            </Field>
                        </div>
                        <Button type="submit" disabled={form.processing}>
                            Crear y abrir
                            <ArrowRight />
                        </Button>
                    </form>
                </div>
            </div>
        </>
    );
}

function Field({
    label,
    children,
}: {
    label: string;
    children: React.ReactNode;
}) {
    return (
        <div className="space-y-2">
            <Label>{label}</Label>
            {children}
        </div>
    );
}

BeatsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Beats', href: '/beats' },
    ],
};
