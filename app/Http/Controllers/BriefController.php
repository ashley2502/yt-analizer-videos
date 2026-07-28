<?php

namespace App\Http\Controllers;

use App\Models\Beat;
use App\Services\Analysis\BriefService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BriefController extends Controller
{
    public function create(Request $request): Response
    {
        $beats = $request->user()->beats()->latest()->get();
        $selectedId = $request->integer('beat_id') ?: null;
        $selected = $selectedId
            ? $beats->firstWhere('id', $selectedId)
            : null;

        return Inertia::render('tools/brief', [
            'aiEnabled' => (bool) config('ai.enabled'),
            'beats' => $beats,
            'brief' => null,
            'form' => $selected ? [
                'beat_id' => (string) $selected->id,
                'artists' => $selected->artists ?? '',
                'mood' => $selected->mood ?? 'dark, dusty, grimy',
                'notes' => '',
            ] : null,
        ]);
    }

    public function store(Request $request, BriefService $service): Response
    {
        $data = $request->validate([
            'beat_id' => ['nullable', 'exists:beats,id'],
            'artists' => ['nullable', 'string', 'max:160'],
            'mood' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $brief = $service->generate($data);

        $beat = null;
        if (! empty($data['beat_id'])) {
            $beat = Beat::query()->where('user_id', $request->user()->id)->findOrFail($data['beat_id']);
            $beat->update([
                'brief' => $brief,
                'bpm' => $beat->bpm ?: $brief['bpm'],
                'musical_key' => $beat->musical_key ?: $brief['musical_key'],
                'status' => $beat->status === 'idea' ? 'sketch' : $beat->status,
            ]);
        }

        $request->user()->analyses()->create([
            'beat_id' => $beat?->id,
            'type' => 'brief',
            'engine' => $brief['engine'],
            'input' => $data,
            'result' => $brief,
        ]);

        return Inertia::render('tools/brief', [
            'aiEnabled' => (bool) config('ai.enabled'),
            'beats' => $request->user()->beats()->latest()->get(),
            'brief' => $brief,
            'form' => $data,
        ]);
    }
}
