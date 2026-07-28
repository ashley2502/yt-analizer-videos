<?php

namespace App\Http\Controllers;

use App\Services\WorkWeekService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkWeekController extends Controller
{
    public function __invoke(Request $request, WorkWeekService $weeks): Response
    {
        $week = $weeks->currentWeek($request->user());

        return Inertia::render('schedule/index', [
            'week' => $week,
            'stats' => $weeks->stats($week),
            'channel' => config('ai.channel'),
        ]);
    }
}
