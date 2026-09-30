<?php

namespace App\Http\Livewire\Archive;

use App\Models\Report;
use Livewire\Component;
use Livewire\WithPagination;

class ArchiveIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $report_type = '';
    public string $period      = '';
    public string $date_from   = '';
    public string $date_to     = '';
    public int    $perPage     = 20;

    private const ARCHIVED_TYPES = ['monthly_waste', 'collection_summary'];

    public function mount()
    {
        if (!isAdmin()) {
            abort(403, 'Access denied.');
        }
    }

    public function updatingReportType() { $this->resetPage(); }
    public function updatingPeriod()     { $this->resetPage(); }

    public function render()
    {
        $archives = Report::whereIn('report_type', self::ARCHIVED_TYPES)
            ->whereNotNull('period')
            ->when($this->report_type, fn($q) => $q->where('report_type', $this->report_type))
            ->when($this->period,      fn($q) => $q->where('period', $this->period))
            ->when($this->date_from,   fn($q) => $q->where('period_end', '>=', $this->date_from))
            ->when($this->date_to,     fn($q) => $q->where('period_start', '<=', $this->date_to))
            ->orderByDesc('generated_at')
            ->paginate($this->perPage);

        return view('livewire.archive.archive-index', compact('archives'))
            ->extends('layouts.app');
    }
}
