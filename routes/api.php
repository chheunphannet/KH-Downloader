<?php

use App\Http\Controllers\StreamController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MetricsController;
Route::post('/analyze', [StreamController::class, 'analyze']);
Route::get('/download', [StreamController::class, 'download'])
    ->middleware('signed')
    ->name('video.download');
Route::get('/server-status', [StreamController::class, 'serverStatus']);

Route::middleware('admin.api')->group(function () {
    Route::get('/metrics', [MetricsController::class, 'index']);
});
