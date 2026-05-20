<?php

namespace App\Http\Livewire\Generators;

use App\Models\WasteGenerator;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class GeneratorIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search           = '';
    public string $status           = '';
    public string $compliance_status = '';
    public int    $perPage          = 15;

    protected $queryString = ['search', 'status', 'compliance_status'];

    public function updatingSearch()  { $this->resetPage(); }
    public function updatingStatus()  { $this->resetPage(); }
    public function updatingComplianceStatus() { $this->resetPage(); }

    public function delete($id)
    {
        $generator = WasteGenerator::findOrFail($id);
        logAudit('delete', 'WasteGenerator', $id, $generator->toArray());
        $generator->delete();
        Cache::forget('stats:generators');
        Cache::forget('lookup:generators');
        Cache::forget('lookup:generators_active');
        Cache::forget('dashboard:kpis');
        session()->flash('success', 'Generator deleted successfully.');
    }

    public function render()
    {
        $generators = WasteGenerator::with(['barangay:barangay_id,barangay_name', 'generatorType:generator_type_id,type_name'])
            ->when($this->search, fn($q) =>
                $q->where(fn($w) => $w
                    ->where('generator_name', 'like', "%{$this->search}%")
                    ->orWhere('contact_person', 'like', "%{$this->search}%"))
            )
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->compliance_status, fn($q) => $q->where('compliance_status', $this->compliance_status))
            ->orderBy('generator_name')
            ->paginate($this->perPage);

        $stats = Cache::remember('stats:generators', 60, function () {
            $row = WasteGenerator::selectRaw("
                COUNT(*) AS total,
                SUM(status = 'active') AS active,
                SUM(compliance_status = 'compliant') AS compliant,
                SUM(compliance_status = 'non_compliant') AS non_compliant
            ")->first();
            return [
                'total'         => (int) $row->total,
                'active'        => (int) $row->active,
                'compliant'     => (int) $row->compliant,
                'non_compliant' => (int) $row->non_compliant,
            ];
        });

        return view('livewire.generators.generator-index', compact('generators', 'stats'))
            ->extends('layouts.app');
    }
}
