<?php

use App\Http\Controllers\BoothController;
use App\Http\Controllers\BoothSessionController;
use App\Models\Frame;
use App\Models\Project;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Kiosk routes (public, no auth)
Route::get('/booth/{project}', [BoothController::class, 'start'])
    ->name('booth.start');

Route::post('/booth/session/{session}/frame', [BoothSessionController::class, 'saveFrame'])
    ->name('booth.session.frame');

Route::patch('/booth/session/{session}', [BoothSessionController::class, 'update'])
    ->name('booth.session.update');

Route::post('/booth/session/{session}/media', [BoothSessionController::class, 'saveMedia'])
    ->name('booth.session.media');

Route::get('/booth/session/{session}/media', [BoothSessionController::class, 'getMedia'])
    ->name('booth.session.media.index');

Route::get('/booth/result/{session}', [BoothController::class, 'result'])
    ->name('booth.result');

// Admin: delete project (used by project card dropdown)
Route::middleware(['web', 'auth'])->prefix('admin')->name('filament.admin.')->group(function () {
    Route::delete('projects/{project}/delete', function (Project $project) {
        abort_unless($project->user_id === auth()->id(), 403);
        $project->delete();
        return redirect()->route('filament.admin.resources.projects.index');
    })->name('projects.delete');

    Route::delete('frames/{frame}/delete', function (Frame $frame) {
        abort_unless($frame->user_id === auth()->id(), 403);
        $frame->delete();
        return redirect()->route('filament.admin.resources.frames.index');
    })->name('frames.delete');
});
