<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Public\BlogController as PublicBlogController;
use Illuminate\Support\Facades\Route;

// Guest-only routes (login page)
Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
         ->middleware('throttle:admin-login')
         ->name('admin.login.post');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
     ->middleware('auth:admin')
     ->name('admin.logout');

// Protected admin routes
Route::prefix('admin')
     ->name('admin.')
     ->middleware('admin.auth')
     ->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile group
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [AuthController::class, 'showProfile'])->name('index');
        Route::post('/password', [AuthController::class, 'changePassword'])->name('password');
    });

    // Blog group (controllers built in Phase 3)
    Route::prefix('blogs')->name('blogs.')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/create', [BlogController::class, 'create'])->name('create');
        Route::post('/', [BlogController::class, 'store'])->name('store');
        Route::get('/{blog}/show', [BlogController::class, 'show'])->name('show');
        Route::get('/{blog}/edit', [BlogController::class, 'edit'])->name('edit');
        Route::put('/{blog}', [BlogController::class, 'update'])->name('update');
        Route::delete('/{blog}', [BlogController::class, 'destroy'])->name('destroy');
        Route::post('/{blog}/toggle', [BlogController::class, 'toggleStatus'])->name('toggle');
    });
});
