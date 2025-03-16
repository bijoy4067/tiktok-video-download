<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TikTokDownloaderController;

Route::get('/', [TikTokDownloaderController::class, 'index'])->name('tiktok.index');
Route::post('/download', [TikTokDownloaderController::class, 'download'])->name('tiktok.download');
Route::post('/get-file', [TikTokDownloaderController::class, 'getFile'])->name('tiktok.get-file');
