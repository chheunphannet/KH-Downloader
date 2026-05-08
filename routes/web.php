<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');

Route::get('/sitemap.xml', function () {
    return response()->view('sitemap')->header('Content-Type', 'text/xml');
});

Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Disallow: /admin/\n\n";
    $content .= "Sitemap: " . url('/sitemap.xml');
    return response($content)->header('Content-Type', 'text/plain');
});

Route::view('/admin/metrics', 'admin.metrics')->name('admin.metrics');
