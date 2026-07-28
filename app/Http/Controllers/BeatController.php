<?php

namespace App\Http\Controllers;

use App\Models\Beat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BeatController extends Controller
{
    public function index(Request $request): Response
    {
        $beats = $request->user()
            ->beats()
            ->latest()
            ->get();

        return Inertia::render('beats/index', [
            'beats' => $beats,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'artists' => ['nullable', 'string', 'max:160'],
            'bpm' => ['nullable', 'integer', 'min:40', 'max:220'],
            'musical_key' => ['nullable', 'string', 'max:40'],
            'mood' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $beat = $request->user()->beats()->create([
            ...$data,
            'status' => 'idea',
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Beat creado.']);

        return to_route('beats.show', $beat);
    }

    public function show(Request $request, Beat $beat): Response
    {
        abort_unless($beat->user_id === $request->user()->id, 403);

        return Inertia::render('beats/show', [
            'beat' => $beat->load(['analyses' => fn ($q) => $q->latest()->limit(10)]),
            'aiEnabled' => (bool) config('ai.enabled'),
        ]);
    }

    public function update(Request $request, Beat $beat): RedirectResponse
    {
        abort_unless($beat->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:120'],
            'artists' => ['nullable', 'string', 'max:160'],
            'bpm' => ['nullable', 'integer', 'min:40', 'max:220'],
            'musical_key' => ['nullable', 'string', 'max:40'],
            'mood' => ['nullable', 'string', 'max:120'],
            'status' => ['sometimes', 'in:idea,sketch,mix,ready,uploaded'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $beat->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Beat actualizado.']);

        return back();
    }

    public function destroy(Request $request, Beat $beat): RedirectResponse
    {
        abort_unless($beat->user_id === $request->user()->id, 403);
        $beat->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Beat eliminado.']);

        return to_route('beats.index');
    }
}
