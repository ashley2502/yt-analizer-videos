import { Head, Link, useForm, usePage } from '@inertiajs/react';
import {
    CheckCircle2,
    Clapperboard,
    ImageIcon,
    ListTodo,
    Music2,
    Sparkles,
    Wand2,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

type PageProps = {
    aiEnabled: boolean;
    channel: {
        name: string;
        handle: string;
        weekly_beat_goal: number;
        weekly_shorts_goal: number;
    };
    week: {
        session_streak: number;
        beat_goal: number;
        shorts_goal: number;
        week_start: string;
    };
    stats: { done: number; total: number; produce_done: number; shorts_done: number };
    beatsCount: number;
    nextTask: { id: number; title: string; minutes: number } | null;
    recentAnalyses: Array<{
        id: number;
        type: string;
        score: number | null;
        engine: string;
        created_at: string;
    }>;
};

export default function Dashboard() {
    const {
        aiEnabled,
        channel,
        week,
        stats,
        beatsCount,
        nextTask,
        recentAnalyses,
    } = usePage().props as unknown as PageProps;

    const toggle = useForm({});

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex flex-col gap-6 p-4">
                <div className="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            {channel.name} · {channel.handle}
                        </p>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Semana de producción
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Meta: {week.beat_goal} beats + {week.shorts_goal}{' '}
                            Shorts · Streak {week.session_streak} días
                        </p>
                    </div>
                    <div className="rounded-full border px-3 py-1 text-xs">
                        IA:{' '}
                        {aiEnabled ? (
                            <span className="text-emerald-600">activa (OpenAI)</span>
                        ) : (
                            <span className="text-amber-600">
                                modo local (pon OPENAI_API_KEY)
                            </span>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-4">
                    <Stat
                        label="Tareas"
                        value={`${stats.done}/${stats.total}`}
                    />
                    <Stat label="Beats" value={String(beatsCount)} />
                    <Stat
                        label="Produce hechas"
                        value={String(stats.produce_done)}
                    />
                    <Stat
                        label="Shorts tasks"
                        value={String(stats.shorts_done)}
                    />
                </div>

                {nextTask && (
                    <div className="rounded-xl border bg-card p-4">
                        <p className="text-xs uppercase tracking-wide text-muted-foreground">
                            Siguiente tarea
                        </p>
                        <div className="mt-2 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p className="font-medium">{nextTask.title}</p>
                                <p className="text-sm text-muted-foreground">
                                    {nextTask.minutes} min
                                </p>
                            </div>
                            <Button
                                onClick={() =>
                                    toggle.patch(`/tasks/${nextTask.id}/toggle`)
                                }
                                disabled={toggle.processing}
                            >
                                <CheckCircle2 />
                                Marcar hecha
                            </Button>
                        </div>
                    </div>
                )}

                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <ToolCard
                        href="/schedule"
                        icon={ListTodo}
                        title="Schedule"
                        desc="Lista de tareas y rachas de la semana"
                    />
                    <ToolCard
                        href="/tools/thumbnail"
                        icon={ImageIcon}
                        title="Auditor thumbnail"
                        desc="Score CTR + letterbox + tips IA"
                    />
                    <ToolCard
                        href="/tools/publish-pack"
                        icon={Wand2}
                        title="Pack YouTube"
                        desc="Títulos, descripción, tags, checklist"
                    />
                    <ToolCard
                        href="/tools/shorts"
                        icon={Clapperboard}
                        title="Asistente Shorts"
                        desc="Cortes sin cámara + overlays"
                    />
                    <ToolCard
                        href="/tools/brief"
                        icon={Sparkles}
                        title="Brief de beat"
                        desc="BPM, key, estructura, plan 25 min"
                    />
                    <ToolCard
                        href="/beats"
                        icon={Music2}
                        title="Mis beats"
                        desc="Pipeline idea → sketch → ready"
                    />
                </div>

                <div className="rounded-xl border p-4">
                    <h2 className="mb-3 font-medium">Análisis recientes</h2>
                    {recentAnalyses.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            Aún no hay análisis. Empieza por thumbnail o pack.
                        </p>
                    ) : (
                        <ul className="space-y-2 text-sm">
                            {recentAnalyses.map((a) => (
                                <li
                                    key={a.id}
                                    className="flex justify-between gap-2 border-b border-border/60 py-2 last:border-0"
                                >
                                    <span>
                                        {a.type} · {a.engine}
                                    </span>
                                    <span className="text-muted-foreground">
                                        {a.score !== null
                                            ? `${a.score}/100`
                                            : '—'}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </>
    );
}

function Stat({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-xl border p-4">
            <p className="text-xs text-muted-foreground">{label}</p>
            <p className="mt-1 text-2xl font-semibold">{value}</p>
        </div>
    );
}

function ToolCard({
    href,
    icon: Icon,
    title,
    desc,
}: {
    href: string;
    icon: React.ComponentType<{ className?: string }>;
    title: string;
    desc: string;
}) {
    return (
        <Link
            href={href}
            className="rounded-xl border p-4 transition hover:bg-muted/40"
        >
            <Icon className="mb-3 size-5 text-muted-foreground" />
            <p className="font-medium">{title}</p>
            <p className="mt-1 text-sm text-muted-foreground">{desc}</p>
        </Link>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
};
