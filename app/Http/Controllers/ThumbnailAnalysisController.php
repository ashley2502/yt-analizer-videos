<?php

namespace App\Http\Controllers;

use App\Models\Beat;
use App\Services\Analysis\ThumbnailAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ThumbnailAnalysisController extends Controller
{
    public function create(Request $request): Response
    {
        $recent = $request->user()
            ->analyses()
            ->where('type', 'thumbnail')
            ->latest()
            ->limit(8)
            ->get();

        $selected = null;
        if ($request->integer('analysis')) {
            $selected = $recent->firstWhere('id', $request->integer('analysis'))
                ?? $request->user()
                    ->analyses()
                    ->where('type', 'thumbnail')
                    ->whereKey($request->integer('analysis'))
                    ->first();
        }

        return Inertia::render('tools/thumbnail', [
            'aiEnabled' => (bool) config('ai.enabled'),
            'beats' => $request->user()->beats()->latest()->get(['id', 'title', 'artists']),
            'recent' => $recent,
            'selected' => $selected,
        ]);
    }

    public function store(Request $request, ThumbnailAnalysisService $service): RedirectResponse
    {
        $data = $request->validate([
            'thumbnail' => ['required', 'image', 'max:8192'],
            'beat_id' => ['nullable', 'exists:beats,id'],
            'title' => ['nullable', 'string', 'max:160'],
            'artists' => ['nullable', 'string', 'max:160'],
        ]);

        $beat = null;
        if (! empty($data['beat_id'])) {
            $beat = Beat::query()->where('user_id', $request->user()->id)->findOrFail($data['beat_id']);
        }

        $path = $request->file('thumbnail')->store('thumbnails/'.$request->user()->id, 'public');
        $absolute = Storage::disk('public')->path($path);

        $result = $service->analyze(
            $absolute,
            $data['title'] ?? $beat?->title,
            $data['artists'] ?? $beat?->artists,
        );

        $analysis = $request->user()->analyses()->create([
            'beat_id' => $beat?->id,
            'type' => 'thumbnail',
            'engine' => $result['engine'],
            'thumbnail_path' => $path,
            'score' => $result['score'],
            'input' => [
                'title' => $data['title'] ?? $beat?->title,
                'artists' => $data['artists'] ?? $beat?->artists,
            ],
            'result' => $result,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Thumbnail score: {$analysis->score}/100 ({$analysis->engine})",
        ]);

        return to_route('tools.thumbnail', ['analysis' => $analysis->id]);
    }
}
