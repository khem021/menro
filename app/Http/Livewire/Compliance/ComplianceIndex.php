<?php

namespace App\Http\Livewire\Compliance;

use App\Models\Barangay;
use App\Models\Incident;
use App\Models\Inspection;
use App\Models\Violation;
use App\Models\WasteGenerator;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class ComplianceIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $tab = 'inspections';

    // ── Inspection filters ──
    public string $insp_generator_id      = '';
    public string $insp_compliance_status = '';
    public string $insp_date_from         = '';
    public string $insp_date_to           = '';

    // ── Violation filters ──
    public string $vio_severity          = '';
    public string $vio_resolution_status = '';

    // ── Incident filters ──
    public string $inc_type        = '';
    public string $inc_status      = '';
    public string $inc_barangay_id = '';

    public int $perPage = 15;

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    // Reset page on any filter change
    public function updatingInspGeneratorId()     { $this->resetPage(); }
    public function updatingInspComplianceStatus(){ $this->resetPage(); }
    public function updatingVioSeverity()         { $this->resetPage(); }
    public function updatingVioResolutionStatus() { $this->resetPage(); }
    public function updatingIncType()             { $this->resetPage(); }
    public function updatingIncStatus()           { $this->resetPage(); }
    public function updatingIncBarangayId()       { $this->resetPage(); }

    // ── Delete handlers ──
    public function deleteInspection(int $id): void
    {
        $r = Inspection::findOrFail($id);
        logAudit('delete', 'Inspection', $id, $r->toArray());
        $r->delete();
        Cache::forget('stats:compliance_pipeline');
        Cache::forget('stats:inspections');
        session()->flash('success', 'Inspection deleted.');
    }

    public function advanceViolation(int $id): void
    {
        $v = Violation::findOrFail($id);
        $old = $v->only(['resolution_status', 'resolved_date']);

        $next = match ($v->resolution_status) {
            'open'        => 'in_progress',
            'in_progress' => 'resolved',
            default       => null,
        };

        if (! $next) return;

        $v->update([
            'resolution_status' => $next,
            'resolved_date'     => $next === 'resolved' ? now()->toDateString() : null,
        ]);

        logAudit('update', 'Violation (quick-advance)', $id, $old, ['resolution_status' => $next]);

        Cache::forget('stats:compliance_pipeline');
        Cache::forget('stats:violations');
        Cache::forget('nav:open_violations');
        Cache::forget('dashboard:kpis');

        $label = $next === 'in_progress' ? 'In Progress' : 'Resolved';
        session()->flash('success', "Violation marked as {$label}.");
    }

    public function deleteViolation(int $id): void
    {
        $r = Violation::findOrFail($id);
        logAudit('delete', 'Violation', $id, $r->toArray());
        $r->delete();
        Cache::forget('stats:compliance_pipeline');
        Cache::forget('stats:violations');
        Cache::forget('nav:open_violations');
        Cache::forget('dashboard:kpis');
        session()->flash('success', 'Violation deleted.');
    }

    public function deleteIncident(int $id): void
    {
        $r = Incident::findOrFail($id);
        logAudit('delete', 'Incident', $id, $r->toArray());
        $r->delete();
        Cache::forget('stats:compliance_pipeline');
        Cache::forget('stats:incidents');
        Cache::forget('dashboard:kpis');
        Cache::forget('dashboard:recent_incidents');
        session()->flash('success', 'Incident deleted.');
    }

    public function render()
    {
        // Only load data for the active tab
        $inspections = $this->tab === 'inspections'
            ? Inspection::with([
                    'wasteGenerator:generator_id,generator_name',
                    'inspector:user_id,full_name',
                ])
                ->when($this->insp_generator_id,      fn($q) => $q->where('generator_id', $this->insp_generator_id))
                ->when($this->insp_compliance_status, fn($q) => $q->where('compliance_status', $this->insp_compliance_status))
                ->when($this->insp_date_from,         fn($q) => $q->where('inspection_date', '>=', $this->insp_date_from))
                ->when($this->insp_date_to,           fn($q) => $q->where('inspection_date', '<=', $this->insp_date_to))
                ->orderByDesc('inspection_date')
                ->paginate($this->perPage)
            : null;

        $violations = $this->tab === 'violations'
            ? Violation::with([
                    'inspection' => fn($q) => $q->select(['inspection_id', 'generator_id'])
                        ->with('wasteGenerator:generator_id,generator_name'),
                ])
                ->when($this->vio_severity,          fn($q) => $q->where('severity', $this->vio_severity))
                ->when($this->vio_resolution_status, fn($q) => $q->where('resolution_status', $this->vio_resolution_status))
                ->orderByDesc('created_at')
                ->paginate($this->perPage)
            : null;

        $incidents = $this->tab === 'incidents'
            ? Incident::with([
                    'barangay:barangay_id,barangay_name',
                    'assignee:user_id,full_name',
                ])
                ->when($this->inc_type,        fn($q) => $q->where('incident_type', $this->inc_type))
                ->when($this->inc_status,      fn($q) => $q->where('status', $this->inc_status))
                ->when($this->inc_barangay_id, fn($q) => $q->where('barangay_id', $this->inc_barangay_id))
                ->orderByDesc('date_reported')
                ->paginate($this->perPage)
            : null;

        $generators = Cache::remember('lookup:generators', 300, fn() =>
            WasteGenerator::orderBy('generator_name')->get(['generator_id', 'generator_name'])
        );

        $barangays = Cache::remember('lookup:barangays', 600, fn() =>
            Barangay::orderBy('barangay_name')->get(['barangay_id', 'barangay_name'])
        );

        // Enforcement pipeline stats
        $pipeline = Cache::remember('stats:compliance_pipeline', 60, function () {
            $insp = Inspection::selectRaw(
                "COUNT(*) AS total,
                 SUM(CASE WHEN compliance_status = 'compliant' THEN 1 ELSE 0 END) AS compliant,
                 SUM(CASE WHEN compliance_status IN ('for_follow_up','warning','violation') THEN 1 ELSE 0 END) AS pending"
            )->first();

            $vio = Violation::selectRaw(
                "COUNT(*) AS total,
                 SUM(CASE WHEN resolution_status = 'open' THEN 1 ELSE 0 END) AS open,
                 SUM(CASE WHEN severity = 'critical' AND resolution_status = 'open' THEN 1 ELSE 0 END) AS critical"
            )->first();

            $inc = Incident::selectRaw(
                "COUNT(*) AS total,
                 SUM(CASE WHEN status IN ('reported','for_validation','under_investigation') THEN 1 ELSE 0 END) AS active,
                 SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved"
            )->first();

            return [
                'insp_total'   => (int) $insp->total,
                'insp_comply'  => (int) $insp->compliant,
                'insp_pending' => (int) $insp->pending,
                'vio_total'    => (int) $vio->total,
                'vio_open'     => (int) $vio->open,
                'vio_critical' => (int) $vio->critical,
                'inc_total'    => (int) $inc->total,
                'inc_active'   => (int) $inc->active,
                'inc_resolved' => (int) $inc->resolved,
            ];
        });

        return view('livewire.compliance.compliance-index', compact(
            'inspections', 'violations', 'incidents',
            'generators', 'barangays', 'pipeline'
        ))->extends('layouts.app');
    }
}
