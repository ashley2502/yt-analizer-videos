<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkTask;
use App\Models\WorkWeek;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WorkWeekService
{
    public function currentWeek(User $user): WorkWeek
    {
        $start = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();

        // whereDate: SQLite may store date casts as "Y-m-d H:i:s", so exact
        // equality on "Y-m-d" misses the row and firstOrCreate blows the unique key.
        $week = WorkWeek::query()
            ->where('user_id', $user->id)
            ->whereDate('week_start', $start)
            ->first();

        if (! $week) {
            $week = WorkWeek::query()->create([
                'user_id' => $user->id,
                'week_start' => $start,
                'beat_goal' => config('ai.channel.weekly_beat_goal', 2),
                'shorts_goal' => config('ai.channel.weekly_shorts_goal', 4),
            ]);
        }

        if ($week->tasks()->count() === 0) {
            $this->seedDefaultTasks($week);
        }

        return $week->load(['tasks.beat']);
    }

    public function seedDefaultTasks(WorkWeek $week): void
    {
        $defaults = [
            ['title' => 'Sesión 25 min: sketch beat #1', 'category' => 'produce', 'minutes' => 25],
            ['title' => 'Sesión 25 min: arrangement beat #1', 'category' => 'produce', 'minutes' => 25],
            ['title' => 'Export + nombrar beat #1', 'category' => 'produce', 'minutes' => 15],
            ['title' => 'Generar pack YouTube (títulos/desc)', 'category' => 'publish', 'minutes' => 15],
            ['title' => 'Auditar thumbnail', 'category' => 'publish', 'minutes' => 15],
            ['title' => 'Cortar 2 Shorts (sin cámara)', 'category' => 'shorts', 'minutes' => 30],
            ['title' => 'Sesión 25 min: sketch beat #2', 'category' => 'produce', 'minutes' => 25],
            ['title' => 'Subir video largo + pinear comentario', 'category' => 'publish', 'minutes' => 20],
        ];

        foreach ($defaults as $index => $task) {
            WorkTask::query()->create([
                'user_id' => $week->user_id,
                'work_week_id' => $week->id,
                'title' => $task['title'],
                'category' => $task['category'],
                'minutes' => $task['minutes'],
                'sort_order' => $index,
                'due_date' => Carbon::parse($week->week_start)->addDays(min($index, 6)),
            ]);
        }
    }

    public function markSession(WorkWeek $week): void
    {
        $today = Carbon::today();
        $last = $week->last_session_date ? Carbon::parse($week->last_session_date) : null;

        if ($last && $last->isToday()) {
            return;
        }

        if ($last && $last->diffInDays($today) === 1) {
            $week->session_streak++;
        } elseif (! $last || $last->diffInDays($today) > 1) {
            $week->session_streak = 1;
        }

        $week->last_session_date = $today;
        $week->save();
    }

    /**
     * @return array{done: int, total: int, produce_done: int, shorts_done: int}
     */
    public function stats(WorkWeek $week): array
    {
        /** @var Collection<int, WorkTask> $tasks */
        $tasks = $week->tasks;

        return [
            'done' => $tasks->where('is_done', true)->count(),
            'total' => $tasks->count(),
            'produce_done' => $tasks->where('category', 'produce')->where('is_done', true)->count(),
            'shorts_done' => $tasks->where('category', 'shorts')->where('is_done', true)->count(),
        ];
    }
}
