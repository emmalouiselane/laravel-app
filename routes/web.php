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

// Error Reporting
Route::match(['get', 'post'], '/report-issue', [\App\Http\Controllers\ReportController::class, 'store'])->name('report.issue');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::patch('/account/timezone', [AccountController::class, 'updateTimezone'])->name('account.update-timezone');
    //Route::patch('/account/dark-mode', [AccountController::class, 'updateDarkMode'])->name('account.update-dark-mode');

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
        Route::get('/settings', [\App\Http\Controllers\BudgetController::class, 'settings'])->name('settings');
        Route::patch('/settings', [\App\Http\Controllers\BudgetController::class, 'updateSettings'])->name('settings.update');
        Route::post('/payments', [\App\Http\Controllers\BudgetController::class, 'storePayment'])->name('payments.store');
        Route::patch('/payments/{payment}', [\App\Http\Controllers\BudgetController::class, 'updatePayment'])->name('payments.update');
        Route::patch('/payments/{payment}/mark-paid', [\App\Http\Controllers\BudgetController::class, 'markPaymentPaid'])->name('payments.mark-paid');
        Route::patch('/occurrences/{occurrence}/mark-paid', [\App\Http\Controllers\BudgetController::class, 'markOccurrencePaid'])->name('occurrences.mark-paid');
        Route::patch('/occurrences/{occurrence}/mark-failed', [\App\Http\Controllers\BudgetController::class, 'markOccurrenceFailed'])->name('occurrences.mark-failed');
        Route::patch('/occurrences/{occurrence}/mark-unpaid', [\App\Http\Controllers\BudgetController::class, 'markOccurrenceUnpaid'])->name('occurrences.mark-unpaid');
        Route::delete('/payments/{payment}', [\App\Http\Controllers\BudgetController::class, 'destroyPayment'])->name('payments.destroy');

        // Category routes
        Route::post('/categories', [\App\Http\Controllers\BudgetController::class, 'storeCategory'])->name('categories.store');
        Route::patch('/categories/{category}', [\App\Http\Controllers\BudgetController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [\App\Http\Controllers\BudgetController::class, 'destroyCategory'])->name('categories.destroy');
    });

    // Sticky Notes routes
    Route::prefix('sticky-notes')->name('sticky-notes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\StickyNoteController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\StickyNoteController::class, 'store'])->name('store');
        Route::put('/{stickyNote}', [\App\Http\Controllers\StickyNoteController::class, 'update'])->name('update');
        Route::delete('/{stickyNote}', [\App\Http\Controllers\StickyNoteController::class, 'destroy'])->name('destroy');
        Route::patch('/{stickyNote}/toggle-pin', [\App\Http\Controllers\StickyNoteController::class, 'togglePin'])->name('toggle-pin');
    });

    // Logout route
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
