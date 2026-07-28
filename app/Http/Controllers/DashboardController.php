<?php

namespace App\Http\Controllers;

use App\Services\WorkWeekService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, WorkWeekService $weeks): Response
    {
        $user = $request->user();
        $week = $weeks->currentWeek($user);
        $stats = $weeks->stats($week);

        return Inertia::render('dashboard', [
            'aiEnabled' => (bool) config('ai.enabled'),
            'channel' => config('ai.channel'),
            'week' => $week,
            'stats' => $stats,
            'beatsCount' => $user->beats()->count(),
            'recentAnalyses' => $user->analyses()->latest()->limit(5)->get(),
            'nextTask' => $week->tasks->firstWhere('is_done', false),
        ]);
    }
}
