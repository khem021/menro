<?php

namespace App\Http\Livewire\Inspections;

use App\Models\Inspection;
use App\Models\WasteGenerator;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class InspectionIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $generator_id      = '';
    public string $compliance_status = '';
    public string $date_from        = '';
    public string $date_to          = '';
    public int    $perPage          = 15;

    public function updatingGeneratorId()     { $this->resetPage(); }
    public function updatingComplianceStatus(){ $this->resetPage(); }

    public function delete($id)
    {
        $insp = Inspection::findOrFail($id);
        logAudit('delete', 'Inspection', $id, $insp->toArray());
        $insp->delete();
        Cache::forget('stats:inspections');
        Cache::forget('stats:compliance_pipeline');
        session()->flash('success', 'Inspection deleted.');
    }

    public function render()
    {
        $inspections = Inspection::with([
                'wasteGenerator:generator_id,generator_name',
                'inspector:user_id,full_name',
            ])
            ->when($this->generator_id,      fn($q) => $q->where('generator_id', $this->generator_id))
            ->when($this->compliance_status, fn($q) => $q->where('compliance_status', $this->compliance_status))
            ->when($this->date_from,         fn($q) => $q->where('inspection_date', '>=', $this->date_from))
            ->when($this->date_to,           fn($q) => $q->where('inspection_date', '<=', $this->date_to))
            ->orderByDesc('inspection_date')
            ->paginate($this->perPage);

        $generators = Cache::remember('lookup:generators', 300, fn() =>
            WasteGenerator::orderBy('generator_name')->get(['generator_id', 'generator_name'])
        );

        $stats = Cache::remember('stats:inspections', 60, function () {
            $today = today()->toDateString();
            $row = Inspection::selectRaw("
                COUNT(*) AS total,
                SUM(compliance_status = 'compliant') AS compliant,
                SUM(compliance_status = 'violation') AS violations,
                SUM(compliance_status = 'for_follow_up' AND next_follow_up IS NOT NULL AND next_follow_up >= ?) AS follow_ups
            ", [$today])->first();
            return [
                'total'      => (int) $row->total,
                'compliant'  => (int) $row->compliant,
                'violations' => (int) $row->violations,
                'follow_ups' => (int) $row->follow_ups,
            ];
        });

        return view('livewire.inspections.inspection-index', compact('inspections', 'generators', 'stats'))
            ->extends('layouts.app');
    }
}
