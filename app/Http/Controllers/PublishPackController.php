<?php

namespace App\Http\Controllers;

use App\Models\Beat;
use App\Services\Analysis\PublishPackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublishPackController extends Controller
{
    public function create(Request $request): Response
    {
        $beats = $request->user()->beats()->latest()->get();
        $selectedId = $request->integer('beat_id') ?: null;
        $selected = $selectedId
            ? $beats->firstWhere('id', $selectedId)
            : null;

        return Inertia::render('tools/publish-pack', [
            'aiEnabled' => (bool) config('ai.enabled'),
            'beats' => $beats,
            'pack' => null,
            'form' => $selected ? [
                'beat_id' => (string) $selected->id,
                'title' => $selected->title,
                'artists' => $selected->artists ?? '',
                'bpm' => $selected->bpm ? (string) $selected->bpm : '',
                'musical_key' => $selected->musical_key ?? '',
                'mood' => $selected->mood ?? 'dark boom bap',
                'notes' => '',
            ] : null,
        ]);
    }

    public function store(Request $request, PublishPackService $service): RedirectResponse|Response
    {
        $data = $request->validate([
            'beat_id' => ['nullable', 'exists:beats,id'],
            'title' => ['required', 'string', 'max:120'],
            'artists' => ['nullable', 'string', 'max:160'],
            'bpm' => ['nullable', 'integer', 'min:40', 'max:220'],
            'musical_key' => ['nullable', 'string', 'max:40'],
            'mood' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $pack = $service->generate($data);

        $beat = null;
        if (! empty($data['beat_id'])) {
            $beat = Beat::query()->where('user_id', $request->user()->id)->findOrFail($data['beat_id']);
            $beat->update(['publish_pack' => $pack]);
        }

        $request->user()->analyses()->create([
            'beat_id' => $beat?->id,
            'type' => 'publish_pack',
            'engine' => $pack['engine'],
            'score' => null,
            'input' => $data,
            'result' => $pack,
        ]);

        return Inertia::render('tools/publish-pack', [
            'aiEnabled' => (bool) config('ai.enabled'),
            'beats' => $request->user()->beats()->latest()->get(),
            'pack' => $pack,
            'form' => $data,
        ]);
    }
}
