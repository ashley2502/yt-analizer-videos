import { Head, useForm, usePage } from '@inertiajs/react';
import { Check, Plus, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Task = {
    id: number;
    title: string;
    category: string;
    minutes: number;
    is_done: boolean;
    due_date: string | null;
};

type PageProps = {
    week: {
        id: number;
        week_start: string;
        beat_goal: number;
        shorts_goal: number;
        session_streak: number;
        tasks: Task[];
    };
    stats: { done: number; total: number };
    channel: { weekly_beat_goal: number; weekly_shorts_goal: number };
};

export default function ScheduleIndex() {
    const { week, stats } = usePage().props as unknown as PageProps;
    const form = useForm({
        title: '',
        category: 'produce',
        minutes: 25,
        due_date: '',
    });
    const toggle = useForm({});

    return (
        <>
            <Head title="Schedule" />
            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Schedule semanal</h1>
                    <p className="text-sm text-muted-foreground">
                        Semana del {week.week_start} · {stats.done}/{stats.total}{' '}
                        · streak {week.session_streak} · meta {week.beat_goal}{' '}
                        beats / {week.shorts_goal} Shorts
                    </p>
                </div>

                <div className="h-2 overflow-hidden rounded-full bg-muted">
                    <div
                        className="h-full bg-primary transition-all"
                        style={{
                            width: `${stats.total ? (stats.done / stats.total) * 100 : 0}%`,
                        }}
                    />
                </div>

                <ul className="space-y-2">
                    {week.tasks.map((task) => (
                        <li
                            key={task.id}
                            className="flex items-center gap-3 rounded-xl border p-3"
                        >
                            <Button
                                size="icon"
                                variant={task.is_done ? 'default' : 'outline'}
                                onClick={() =>
                                    toggle.patch(`/tasks/${task.id}/toggle`)
                                }
                            >
                                <Check />
                            </Button>
                            <div className="min-w-0 flex-1">
                                <p
                                    className={
                                        task.is_done
                                            ? 'text-muted-foreground line-through'
                                            : 'font-medium'
                                    }
                                >
                                    {task.title}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {task.category} · {task.minutes} min
                                    {task.due_date ? ` · ${task.due_date}` : ''}
                                </p>
                            </div>
                            <Button
                                size="icon"
                                variant="ghost"
                                onClick={() =>
                                    toggle.delete(`/tasks/${task.id}`)
                                }
                            >
                                <Trash2 />
                            </Button>
                        </li>
                    ))}
                </ul>

                <form
                    className="space-y-3 rounded-xl border p-4"
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post('/tasks', {
                            onSuccess: () => form.reset('title'),
                        });
                    }}
                >
                    <p className="font-medium">Nueva tarea</p>
                    <div className="space-y-2">
                        <Label htmlFor="title">Título</Label>
                        <Input
                            id="title"
                            value={form.data.title}
                            onChange={(e) =>
                                form.setData('title', e.target.value)
                            }
                            placeholder="Hoy solo 25 min: drums"
                        />
                    </div>
                    <div className="grid grid-cols-2 gap-3">
                        <div className="space-y-2">
                            <Label htmlFor="category">Categoría</Label>
                            <select
                                id="category"
                                className="h-9 w-full rounded-md border bg-background px-3 text-sm"
                                value={form.data.category}
                                onChange={(e) =>
                                    form.setData('category', e.target.value)
                                }
                            >
                                <option value="produce">Producir</option>
                                <option value="publish">Publicar</option>
                                <option value="shorts">Shorts</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="minutes">Minutos</Label>
                            <Input
                                id="minutes"
                                type="number"
                                value={form.data.minutes}
                                onChange={(e) =>
                                    form.setData(
                                        'minutes',
                                        Number(e.target.value),
                                    )
                                }
                            />
                        </div>
                    </div>
                    <Button type="submit" disabled={form.processing}>
                        <Plus />
                        Agregar
                    </Button>
                </form>
            </div>
        </>
    );
}

ScheduleIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Schedule', href: '/schedule' },
    ],
};
