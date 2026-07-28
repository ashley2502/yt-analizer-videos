<?php

namespace App\Http\Controllers;

use App\Models\Beat;
use App\Services\Analysis\ShortsAssistantService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShortsAssistantController extends Controller
{
    public function create(Request $request): Response
    {
        $beats = $request->user()->beats()->latest()->get();
        $selectedId = $request->integer('beat_id') ?: null;
        $selected = $selectedId
            ? $beats->firstWhere('id', $selectedId)
            : null;

        return Inertia::render('tools/shorts', [
            'aiEnabled' => (bool) config('ai.enabled'),
            'beats' => $beats,
            'plan' => null,
            'form' => $selected ? [
                'beat_id' => (string) $selected->id,
                'title' => $selected->title,
                'artists' => $selected->artists ?? '',
                'bpm' => $selected->bpm ? (string) $selected->bpm : '',
                'drop_second' => '12',
                'duration_seconds' => '180',
                'notes' => '',
            ] : null,
        ]);
    }

    public function store(Request $request, ShortsAssistantService $service): Response
    {
        $data = $request->validate([
            'beat_id' => ['nullable', 'exists:beats,id'],
            'title' => ['required', 'string', 'max:120'],
            'artists' => ['nullable', 'string', 'max:160'],
            'bpm' => ['nullable', 'integer', 'min:40', 'max:220'],
            'drop_second' => ['nullable', 'integer', 'min:0', 'max:600'],
            'duration_seconds' => ['nullable', 'integer', 'min:30', 'max:600'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $plan = $service->generate($data);

        $beat = null;
        if (! empty($data['beat_id'])) {
            $beat = Beat::query()->where('user_id', $request->user()->id)->findOrFail($data['beat_id']);
            $beat->update(['shorts_plan' => $plan]);
        }

        $request->user()->analyses()->create([
            'beat_id' => $beat?->id,
            'type' => 'shorts',
            'engine' => $plan['engine'],
            'input' => $data,
            'result' => $plan,
        ]);

        return Inertia::render('tools/shorts', [
            'aiEnabled' => (bool) config('ai.enabled'),
            'beats' => $request->user()->beats()->latest()->get(),
            'plan' => $plan,
            'form' => $data,
        ]);
    }
}
