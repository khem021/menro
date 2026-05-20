<?php

namespace App\Http\Livewire\Incidents;

use App\Models\Barangay;
use App\Models\Incident;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class IncidentIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $incident_type = '';
    public string $status        = '';
    public string $barangay_id   = '';
    public int    $perPage       = 15;

    public function updatingIncidentType(){ $this->resetPage(); }
    public function updatingStatus()     { $this->resetPage(); }
    public function updatingBarangayId() { $this->resetPage(); }

    public function delete($id)
    {
        $i = Incident::findOrFail($id);
        logAudit('delete', 'Incident', $id, $i->toArray());
        $i->delete();
        Cache::forget('stats:incidents');
        Cache::forget('stats:compliance_pipeline');
        Cache::forget('dashboard:kpis');
        Cache::forget('dashboard:recent_incidents');
        session()->flash('success', 'Incident deleted.');
    }

    public function render()
    {
        $incidents = Incident::with([
                'barangay:barangay_id,barangay_name',
                'assignee:user_id,full_name',
            ])
            ->when($this->incident_type, fn($q) => $q->where('incident_type', $this->incident_type))
            ->when($this->status,        fn($q) => $q->where('status', $this->status))
            ->when($this->barangay_id,   fn($q) => $q->where('barangay_id', $this->barangay_id))
            ->orderByDesc('date_reported')
            ->paginate($this->perPage);

        $barangays = Cache::remember('lookup:barangays', 600, fn() =>
            Barangay::orderBy('barangay_name')->get(['barangay_id', 'barangay_name'])
        );

        $stats = Cache::remember('stats:incidents', 60, function () {
            $row = Incident::selectRaw("
                COUNT(*) AS total,
                SUM(status IN ('reported','for_validation')) AS open,
                SUM(status = 'under_investigation') AS under_inv,
                SUM(status = 'resolved') AS resolved
            ")->first();
            return [
                'total'     => (int) $row->total,
                'open'      => (int) $row->open,
                'under_inv' => (int) $row->under_inv,
                'resolved'  => (int) $row->resolved,
            ];
        });

        return view('livewire.incidents.incident-index', compact('incidents', 'barangays', 'stats'))
            ->extends('layouts.app');
    }
}
