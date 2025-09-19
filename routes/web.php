<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;

Route::get('/', function () {
    return view('welcome');
});

// Google OAuth Routes
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::patch('/account/timezone', [AccountController::class, 'updateTimezone'])->name('account.update-timezone');
    
    // Planner routes
    Route::prefix('planner')->name('planner.')->group(function () {
        Route::get('/', [\App\Http\Controllers\PlannerController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\PlannerController::class, 'store'])->name('store');
        Route::get('/{todo}/edit', [\App\Http\Controllers\PlannerController::class, 'edit'])->name('edit');
        Route::patch('/{todo}/toggle', [\App\Http\Controllers\PlannerController::class, 'toggleComplete'])->name('toggle-complete');
        Route::put('/{todo}', [\App\Http\Controllers\PlannerController::class, 'update'])->name('update');
        Route::delete('/{todo}', [\App\Http\Controllers\PlannerController::class, 'destroy'])->name('destroy');
    });
    
    // Budget routes
    Route::prefix('budget')->name('budget.')->group(function () {
        Route::get('/', [\App\Http\Controllers\BudgetController::class, 'index'])->name('index');
    });
    
    // Logout route
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
