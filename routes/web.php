<?php

use App\Http\Controllers\BoothController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/booth/{project}', [BoothController::class, 'start'])
    ->name('booth.start');
