<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\BlogController as PublicBlogController;

// Admin routes must be required FIRST before any other routes
require __DIR__.'/admin.php';

// Public blog routes
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [PublicBlogController::class, 'index'])->name('index');
    Route::get('/{slug}', [PublicBlogController::class, 'show'])->name('show');
});
