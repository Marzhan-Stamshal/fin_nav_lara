<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DataPortController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ScenarioController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/mark-paid', [DashboardController::class, 'markPaid'])->name('dashboard.mark-paid');
    Route::post('/dashboard/close-early', [DashboardController::class, 'closeEarly'])->name('dashboard.close-early');
    Route::post('/dashboard/assign-group', [DashboardController::class, 'assignGroup'])->name('dashboard.assign-group');
    Route::post('/dashboard/clear-group', [DashboardController::class, 'clearGroup'])->name('dashboard.clear-group');
    Route::get('/backup/export/json', [DataPortController::class, 'exportJson'])->name('backup.export.json');
    Route::post('/backup/import/json', [DataPortController::class, 'importJson'])->name('backup.import.json');

    Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::get('/loans/export/csv', [LoanController::class, 'exportCsv'])->name('loans.export.csv');
    Route::get('/loans/export/sample-csv', [LoanController::class, 'exportSampleCsv'])->name('loans.export.sample-csv');
    Route::post('/loans/import/csv', [LoanController::class, 'importCsv'])->name('loans.import.csv');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
    Route::post('/loans/{loan}/mark-paid', [LoanController::class, 'markPaid'])->name('loans.mark-paid');
    Route::post('/loans/{loan}/close-early', [LoanController::class, 'closeEarly'])->name('loans.close-early');
    Route::get('/loans/{loan}/edit', [LoanController::class, 'edit'])->name('loans.edit');
    Route::put('/loans/{loan}', [LoanController::class, 'update'])->name('loans.update');
    Route::delete('/loans/{loan}', [LoanController::class, 'destroy'])->name('loans.destroy');

    Route::get('/scenarios', [ScenarioController::class, 'index'])->name('scenarios.index');
    Route::post('/scenarios', [ScenarioController::class, 'store'])->name('scenarios.store');
    Route::delete('/scenarios/{scenario}', [ScenarioController::class, 'destroy'])->name('scenarios.destroy');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/schedule', [PaymentController::class, 'schedule'])->name('payments.schedule');
    Route::post('/payments/schedule/mark-paid', [PaymentController::class, 'scheduleMarkPaid'])->name('payments.schedule.mark-paid');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/preferences', [SettingsController::class, 'updatePreferences'])->name('settings.preferences');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
