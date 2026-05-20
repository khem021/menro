<?php

namespace App\Http\Livewire\Reports;

use App\Models\Report;
use Livewire\Component;

class ReportIndex extends Component
{
    public string $report_type = 'monthly_waste';
    public string $date_from   = '';
    public string $date_to     = '';

    public function mount()
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to   = now()->format('Y-m-d');
    }

    public function setPeriod(string $period): void
    {
        [$this->date_from, $this->date_to] = match ($period) {
            'today'      => [now()->toDateString(), now()->toDateString()],
            'yesterday'  => [now()->subDay()->toDateString(), now()->subDay()->toDateString()],
            'this_week'  => [now()->startOfWeek()->toDateString(), now()->toDateString()],
            'last_week'  => [now()->subWeek()->startOfWeek()->toDateString(), now()->subWeek()->endOfWeek()->toDateString()],
            'this_month' => [now()->startOfMonth()->toDateString(), now()->toDateString()],
            'last_month' => [now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
            default      => [$this->date_from, $this->date_to],
        };
    }

    public function getExportUrl(): string
    {
        return route('reports.export', [
            'type' => $this->report_type,
            'from' => $this->date_from,
            'to'   => $this->date_to,
        ]);
    }

    public function getPrintUrl(): string
    {
        return route('reports.print', [
            'type' => $this->report_type,
            'from' => $this->date_from,
            'to'   => $this->date_to,
        ]);
    }

    public function render()
    {
        $reports = Report::with('generatedBy')
            ->orderByDesc('generated_at')
            ->limit(20)
            ->get();

        return view('livewire.reports.report-index', compact('reports'))
            ->extends('layouts.app');
    }
}
