<?php

namespace App\Http\Controllers;

use App\Models\WorkTask;
use App\Services\WorkWeekService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkTaskController extends Controller
{
    public function store(Request $request, WorkWeekService $weeks): RedirectResponse
    {
        $week = $weeks->currentWeek($request->user());

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'in:produce,publish,shorts,other'],
            'minutes' => ['nullable', 'integer', 'min:5', 'max:240'],
            'due_date' => ['nullable', 'date'],
        ]);

        $request->user()->workTasks()->create([
            ...$data,
            'work_week_id' => $week->id,
            'minutes' => $data['minutes'] ?? 25,
            'sort_order' => $week->tasks()->count(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tarea agregada.']);

        return back();
    }

    public function toggle(Request $request, WorkTask $task, WorkWeekService $weeks): RedirectResponse
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $task->is_done = ! $task->is_done;
        $task->completed_at = $task->is_done ? now() : null;
        $task->save();

        if ($task->is_done) {
            $weeks->markSession($task->workWeek);
        }

        return back();
    }

    public function destroy(Request $request, WorkTask $task): RedirectResponse
    {
        abort_unless($task->user_id === $request->user()->id, 403);
        $task->delete();

        return back();
    }
}
