<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root
Route::get('/', function () {
    return session('auth_user_id')
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Guest routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated routes
Route::middleware('auth.custom')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Generators
    Route::get('/generators', \App\Http\Livewire\Generators\GeneratorIndex::class)->name('generators.index');
    Route::get('/generators/create', \App\Http\Livewire\Generators\GeneratorForm::class)->name('generators.create');
    Route::get('/generators/{id}/edit', \App\Http\Livewire\Generators\GeneratorForm::class)->name('generators.edit');

    // Waste Entries
    Route::get('/entries', \App\Http\Livewire\Entries\EntryIndex::class)->name('entries.index');
    Route::get('/entries/create', \App\Http\Livewire\Entries\EntryForm::class)->name('entries.create');
    Route::get('/entries/{id}/edit', \App\Http\Livewire\Entries\EntryForm::class)->name('entries.edit');

    // Compliance (combined Inspections + Violations + Incidents)
    Route::get('/compliance', \App\Http\Livewire\Compliance\ComplianceIndex::class)->name('compliance.index');

    // Inspections
    Route::get('/inspections', \App\Http\Livewire\Inspections\InspectionIndex::class)->name('inspections.index');
    Route::get('/inspections/create', \App\Http\Livewire\Inspections\InspectionForm::class)->name('inspections.create');
    Route::get('/inspections/{id}/edit', \App\Http\Livewire\Inspections\InspectionForm::class)->name('inspections.edit');

    // Violations
    Route::get('/violations', \App\Http\Livewire\Violations\ViolationIndex::class)->name('violations.index');
    Route::get('/violations/create', \App\Http\Livewire\Violations\ViolationForm::class)->name('violations.create');
    Route::get('/violations/{id}/edit', \App\Http\Livewire\Violations\ViolationForm::class)->name('violations.edit');

    // Barangay List
    Route::get('/barangays', \App\Http\Livewire\Barangays\BarangayIndex::class)->name('barangays.index');

    // Collections
    Route::get('/collections', \App\Http\Livewire\Collections\CollectionIndex::class)->name('collections.index');
    Route::get('/collections/create', \App\Http\Livewire\Collections\CollectionForm::class)->name('collections.create');
    Route::get('/collections/{id}/edit', \App\Http\Livewire\Collections\CollectionForm::class)->name('collections.edit');

    // Incidents
    Route::get('/incidents', \App\Http\Livewire\Incidents\IncidentIndex::class)->name('incidents.index');
    Route::get('/incidents/create', \App\Http\Livewire\Incidents\IncidentForm::class)->name('incidents.create');
    Route::get('/incidents/{id}/edit', \App\Http\Livewire\Incidents\IncidentForm::class)->name('incidents.edit');

    // Notifications
    Route::get('/notifications', \App\Http\Livewire\Notifications\NotificationIndex::class)->name('notifications.index');

    // Users (admin only)
    Route::get('/users', \App\Http\Livewire\Users\UserIndex::class)->name('users.index');
    Route::get('/users/create', \App\Http\Livewire\Users\UserForm::class)->name('users.create');
    Route::get('/users/{id}/edit', \App\Http\Livewire\Users\UserForm::class)->name('users.edit');

    // Analytics, Reports, Audit
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/reports', \App\Http\Livewire\Reports\ReportIndex::class)->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/print',  [ReportController::class, 'printView'])->name('reports.print');
    Route::get('/audit', \App\Http\Livewire\Audit\AuditIndex::class)->name('audit.index');

    // Settings
    Route::get('/settings', \App\Http\Livewire\Settings\SettingsForm::class)->name('settings');
});
