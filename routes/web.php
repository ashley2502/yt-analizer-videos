<?php

use App\Http\Controllers\BeatController;
use App\Http\Controllers\BriefController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublishPackController;
use App\Http\Controllers\ShortsAssistantController;
use App\Http\Controllers\ThumbnailAnalysisController;
use App\Http\Controllers\WorkTaskController;
use App\Http\Controllers\WorkWeekController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('schedule', WorkWeekController::class)->name('schedule');
    Route::post('tasks', [WorkTaskController::class, 'store'])->name('tasks.store');
    Route::patch('tasks/{task}/toggle', [WorkTaskController::class, 'toggle'])->name('tasks.toggle');
    Route::delete('tasks/{task}', [WorkTaskController::class, 'destroy'])->name('tasks.destroy');

    Route::resource('beats', BeatController::class)->except(['create', 'edit']);

    Route::get('tools/thumbnail', [ThumbnailAnalysisController::class, 'create'])->name('tools.thumbnail');
    Route::post('tools/thumbnail', [ThumbnailAnalysisController::class, 'store'])->name('tools.thumbnail.store');

    Route::get('tools/publish-pack', [PublishPackController::class, 'create'])->name('tools.publish-pack');
    Route::post('tools/publish-pack', [PublishPackController::class, 'store'])->name('tools.publish-pack.store');

    Route::get('tools/shorts', [ShortsAssistantController::class, 'create'])->name('tools.shorts');
    Route::post('tools/shorts', [ShortsAssistantController::class, 'store'])->name('tools.shorts.store');

    Route::get('tools/brief', [BriefController::class, 'create'])->name('tools.brief');
    Route::post('tools/brief', [BriefController::class, 'store'])->name('tools.brief.store');
});

require __DIR__.'/settings.php';
