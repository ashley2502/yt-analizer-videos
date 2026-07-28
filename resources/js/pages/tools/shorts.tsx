import { Head, useForm, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Plan = {
    engine: string;
    windows: Array<{
        label: string;
        start_sec: number;
        end_sec: number;
        why: string;
        first_frame_tip: string;
    }>;
    overlays: string[];
    workflow: string[];
    caption: string;
};

type Beat = {
    id: number;
    title: string;
    artists: string | null;
    bpm: number | null;
};

export default function ShortsTool() {
    const { aiEnabled, beats, plan, form: previous } = usePage().props as unknown as {
        aiEnabled: boolean;
        beats: Beat[];
        plan: Plan | null;
        form?: Record<string, string | number>;
    };

    const form = useForm({
        beat_id: previous?.beat_id ? String(previous.beat_id) : '',
        title: (previous?.title as string) ?? '',
        artists: (previous?.artists as string) ?? '',
        bpm: previous?.bpm ? String(previous.bpm) : '',
        drop_second: previous?.drop_second ? String(previous.drop_second) : '12',
        duration_seconds: previous?.duration_seconds
            ? String(previous.duration_seconds)
            : '180',
        notes: (previous?.notes as string) ?? '',
    });

    return (
        <>
            <Head title="Asistente Shorts" />
            <div className="flex flex-col gap-6 p-4 lg:flex-row">
                <form
                    className="w-full max-w-md space-y-3 rounded-xl border p-4"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.transform((data) => ({
                            ...data,
                            beat_id: data.beat_id || null,
                            bpm: data.bpm === '' ? null : Number(data.bpm),
                            drop_second: Number(data.drop_second || 12),
                            duration_seconds: Number(
                                data.duration_seconds || 180,
                            ),
                        }));
                        form.post('/tools/shorts');
                    }}
                >
                    <h1 className="text-xl font-semibold">
                        Shorts sin cámara
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Te dice qué segundos cortar y cómo editar en CapCut.
                        {aiEnabled ? ' IA ON.' : ' Templates locales.'}
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

                    <Label>Título</Label>
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
                    <Label>Segundo del drop / hook</Label>
                    <Input
                        value={form.data.drop_second}
                        onChange={(e) =>
                            form.setData('drop_second', e.target.value)
                        }
                    />
                    <Button type="submit" disabled={form.processing}>
                        Generar plan Shorts
                    </Button>
                </form>

                <div className="flex-1 space-y-4">
                    {!plan && (
                        <p className="text-sm text-muted-foreground">
                            Indica en qué segundo entra el hook y te armamos 3
                            ventanas.
                        </p>
                    )}
                    {plan && (
                        <>
                            <div className="rounded-xl border p-4">
                                <h2 className="mb-3 font-medium">
                                    Ventanas ({plan.engine})
                                </h2>
                                <ul className="space-y-3">
                                    {plan.windows.map((w) => (
                                        <li
                                            key={w.label}
                                            className="rounded-lg border p-3 text-sm"
                                        >
                                            <p className="font-medium">
                                                {w.label}: {w.start_sec}s →{' '}
                                                {w.end_sec}s
                                            </p>
                                            <p className="text-muted-foreground">
                                                {w.why}
                                            </p>
                                            <p className="mt-1">
                                                Tip primer frame:{' '}
                                                {w.first_frame_tip}
                                            </p>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                            <div className="rounded-xl border p-4">
                                <h2 className="mb-2 font-medium">Overlays</h2>
                                <ul className="list-disc pl-5 text-sm">
                                    {plan.overlays.map((o) => (
                                        <li key={o}>{o}</li>
                                    ))}
                                </ul>
                            </div>
                            <div className="rounded-xl border p-4">
                                <h2 className="mb-2 font-medium">
                                    Workflow CapCut
                                </h2>
                                <ol className="list-decimal space-y-1 pl-5 text-sm">
                                    {plan.workflow.map((step) => (
                                        <li key={step}>{step}</li>
                                    ))}
                                </ol>
                            </div>
                            <div className="rounded-xl border p-4">
                                <h2 className="mb-2 font-medium">Caption</h2>
                                <pre className="whitespace-pre-wrap text-sm">
                                    {plan.caption}
                                </pre>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

ShortsTool.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Shorts', href: '/tools/shorts' },
    ],
};
