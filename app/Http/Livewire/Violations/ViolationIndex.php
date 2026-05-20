<?php

namespace App\Http\Livewire\Violations;

use App\Models\Violation;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class ViolationIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $severity          = '';
    public string $resolution_status = '';
    public int    $perPage           = 15;

    public function updatingSeverity()        { $this->resetPage(); }
    public function updatingResolutionStatus(){ $this->resetPage(); }

    public function delete($id)
    {
        $v = Violation::findOrFail($id);
        logAudit('delete', 'Violation', $id, $v->toArray());
        $v->delete();
        Cache::forget('stats:violations');
        Cache::forget('nav:open_violations');
        Cache::forget('stats:compliance_pipeline');
        Cache::forget('dashboard:kpis');
        session()->flash('success', 'Violation deleted.');
    }

    public function render()
    {
        $violations = Violation::with(['inspection' => fn($q) => $q->select(['inspection_id','generator_id'])->with('wasteGenerator:generator_id,generator_name')])
            ->when($this->severity,          fn($q) => $q->where('severity', $this->severity))
            ->when($this->resolution_status, fn($q) => $q->where('resolution_status', $this->resolution_status))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        $stats = Cache::remember('stats:violations', 60, function () {
            $row = Violation::selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN resolution_status = 'open' THEN 1 ELSE 0 END) AS open,
                SUM(CASE WHEN severity = 'critical' AND resolution_status = 'open' THEN 1 ELSE 0 END) AS critical,
                SUM(CASE WHEN resolution_status = 'resolved' THEN 1 ELSE 0 END) AS resolved
            ")->first();
            return [
                'total'    => (int) $row->total,
                'open'     => (int) $row->open,
                'critical' => (int) $row->critical,
                'resolved' => (int) $row->resolved,
            ];
        });

        return view('livewire.violations.violation-index', compact('violations', 'stats'))
            ->extends('layouts.app');
    }
}
