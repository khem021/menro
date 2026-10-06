<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\ViolationTicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root (controller action, not a closure — keeps `route:cache` working)
Route::get('/', [AuthController::class, 'redirectRoot']);

// Guest routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Roles that may work with each module. The components re-check these in their
// own mount()/actions; having them here as well means the route table shows
// who can reach what.
$managers   = 'role:System Administrator,MENRO Officer';
$fieldStaff = 'role:System Administrator,MENRO Officer,Field Inspector';

// Data Encoder is "responsible for encoding waste data", so it joins the
// managers for waste entries.
$encoders   = 'role:System Administrator,MENRO Officer,Data Encoder';

// Anyone but Report Viewer, which is read-only by definition. Used for the
// forms that any working role may legitimately submit.
$contributors = 'role:System Administrator,MENRO Officer,Data Encoder,Field Inspector,Barangay User';

// Authenticated routes
Route::middleware('auth.custom')->group(function () use ($managers, $fieldStaff, $encoders, $contributors) {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Generators
    Route::get('/generators', \App\Http\Livewire\Generators\GeneratorIndex::class)->name('generators.index');
    Route::get('/generators/create', \App\Http\Livewire\Generators\GeneratorForm::class)->name('generators.create');
    Route::get('/generators/{id}/edit', \App\Http\Livewire\Generators\GeneratorForm::class)->name('generators.edit');

    // Waste Entries
    Route::get('/entries', \App\Http\Livewire\Entries\EntryIndex::class)->name('entries.index');
    Route::middleware($encoders)->group(function () {
        Route::get('/entries/create', \App\Http\Livewire\Entries\EntryForm::class)->name('entries.create');
        Route::get('/entries/{id}/edit', \App\Http\Livewire\Entries\EntryForm::class)->name('entries.edit');
    });

    Route::middleware($fieldStaff)->group(function () {
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
    });

    // Violation Tickets — citation tickets issued to individuals (littering, dumping,
    // burning, no segregation). Distinct from Violations/Compliance above, which
    // tracks generator-inspection violations tied to inspection_id.
    // These list private individuals' names and addresses, so they are limited
    // to the staff who issue and manage them.
    Route::middleware($fieldStaff)->group(function () {
        Route::get('/violation-tickets', \App\Http\Livewire\ViolationTickets\ViolationTicketIndex::class)->name('violation-tickets.index');
        Route::get('/violation-tickets/create', \App\Http\Livewire\ViolationTickets\ViolationTicketForm::class)->name('violation-tickets.create');
        Route::get('/violation-tickets/{id}/edit', \App\Http\Livewire\ViolationTickets\ViolationTicketForm::class)->name('violation-tickets.edit');
        Route::get('/violation-tickets/{id}/receipt', [ViolationTicketController::class, 'receipt'])->name('violation-tickets.receipt');
    });

    // Barangay List
    Route::get('/barangays', \App\Http\Livewire\Barangays\BarangayIndex::class)->name('barangays.index');
    Route::get('/barangays/clusters', \App\Http\Livewire\Dashboard\ClusterConfig::class)->name('clusters.index');

    // Collections
    Route::middleware($managers)->group(function () {
        Route::get('/collections', \App\Http\Livewire\Collections\CollectionIndex::class)->name('collections.index');
        Route::get('/collections/create', \App\Http\Livewire\Collections\CollectionForm::class)->name('collections.create');
        Route::get('/collections/{id}/edit', \App\Http\Livewire\Collections\CollectionForm::class)->name('collections.edit');
    });

    // Incidents
    Route::get('/incidents', \App\Http\Livewire\Incidents\IncidentIndex::class)->name('incidents.index');
    Route::middleware($contributors)->group(function () {
        Route::get('/incidents/create', \App\Http\Livewire\Incidents\IncidentForm::class)->name('incidents.create');
        Route::get('/incidents/{id}/edit', \App\Http\Livewire\Incidents\IncidentForm::class)->name('incidents.edit');
    });

    // Notifications
    Route::get('/notifications', \App\Http\Livewire\Notifications\NotificationIndex::class)->name('notifications.index');

    // Analytics, Reports
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/reports', \App\Http\Livewire\Reports\ReportIndex::class)->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/print',  [ReportController::class, 'printView'])->name('reports.print');

    // Admin-only. Each of these components also re-checks with isAdmin() in
    // mount(); the middleware makes the restriction part of the route itself
    // rather than something a future component has to remember.
    Route::middleware('role:System Administrator')->group(function () {
        // Users
        Route::get('/users', \App\Http\Livewire\Users\UserIndex::class)->name('users.index');
        Route::get('/users/create', \App\Http\Livewire\Users\UserForm::class)->name('users.create');
        Route::get('/users/{id}/edit', \App\Http\Livewire\Users\UserForm::class)->name('users.edit');

        // Audit trail
        Route::get('/audit', \App\Http\Livewire\Audit\AuditIndex::class)->name('audit.index');

        // Archive — automatically archived waste-collection reports
        Route::get('/archive', \App\Http\Livewire\Archive\ArchiveIndex::class)->name('archive.index');
        Route::get('/archive/{report}/download/{format}', [ArchiveController::class, 'download'])
            ->name('archive.download')->where('format', 'xlsx|pdf');
    });

    // Settings
    Route::get('/settings', \App\Http\Livewire\Settings\SettingsForm::class)->name('settings');
});
