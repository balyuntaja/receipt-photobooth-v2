<?php

use App\Http\Controllers\BoothController;
use App\Http\Controllers\BoothSessionController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\MidtransWebhookController;
use App\Models\Frame;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');

// Kiosk routes (public, no auth)
Route::get('/booth/{project}', [BoothController::class, 'start'])
    ->name('booth.start');

Route::post('/booth/session/{session}/frame', [BoothSessionController::class, 'saveFrame'])
    ->name('booth.session.frame');

Route::patch('/booth/session/{session}', [BoothSessionController::class, 'update'])
    ->name('booth.session.update');

Route::post('/booth/session/{session}/media', [BoothSessionController::class, 'saveMedia'])
    ->name('booth.session.media');

Route::post('/booth/session/{session}/create-payment', [BoothSessionController::class, 'createPayment'])
    ->name('booth.session.create-payment');

Route::post('/booth/session/{session}/apply-voucher', [BoothSessionController::class, 'applyVoucher'])
    ->name('booth.session.apply-voucher');

Route::post('/booth/session/{session}/confirm-free', [BoothSessionController::class, 'confirmFree'])
    ->name('booth.session.confirm-free');

Route::get('/booth/session/{session}/media', [BoothSessionController::class, 'getMedia'])
    ->name('booth.session.media.index');

Route::get('/booth/result/{session}', [BoothController::class, 'result'])
    ->name('booth.result');

// Midtrans webhook (no CSRF)
Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.notification')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Admin: delete project (used by project card dropdown)
Route::middleware(['web', 'auth'])->prefix('admin')->name('filament.admin.')->group(function () {
    Route::delete('projects/{project}/delete', function (Project $project) {
        abort_unless($project->user_id === Auth::id(), 403);
        $project->delete();
        return redirect()->route('filament.admin.resources.projects.index');
    })->name('projects.delete');

    Route::delete('frames/{frame}/delete', function (Frame $frame) {
        abort_unless($frame->user_id === Auth::id(), 403);
        $frame->delete();
        return redirect()->route('filament.admin.resources.frames.index');
    })->name('frames.delete');
});
