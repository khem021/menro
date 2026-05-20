<?php

namespace App\Http\Livewire\Violations;

use App\Models\Inspection;
use App\Models\Violation;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ViolationForm extends Component
{
    public ?int $violationId = null;

    public string $inspection_id     = '';
    public string $violation_type    = '';
    public string $description       = '';
    public string $severity          = 'low';
    public string $penalty_status    = 'none';
    public string $resolution_status = 'open';
    public string $resolved_date     = '';

    protected $rules = [
        'inspection_id'     => 'required|integer|exists:inspections,inspection_id',
        'violation_type'    => 'required|string|max:255',
        'description'       => 'nullable|string|max:2000',
        'severity'          => 'required|in:low,medium,high,critical',
        'penalty_status'    => 'required|in:none,warning_issued,penalty_pending,penalty_applied',
        'resolution_status' => 'required|in:open,in_progress,resolved,dismissed',
        'resolved_date'     => 'nullable|date',
    ];

    public function mount($id = null)
    {
        if (!$id && request()->filled('inspection_id')) {
            $this->inspection_id = (string) request()->integer('inspection_id');
        }

        if ($id) {
            $this->violationId = (int) $id;
            $v = Violation::findOrFail($id);
            $this->inspection_id     = (string) $v->inspection_id;
            $this->violation_type    = $v->violation_type;
            $this->description       = $v->description ?? '';
            $this->severity          = $v->severity;
            $this->penalty_status    = $v->penalty_status ?? 'none';
            $this->resolution_status = $v->resolution_status ?? 'open';
            $this->resolved_date     = $v->resolved_date ?? '';
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'inspection_id'     => $this->inspection_id,
            'violation_type'    => $this->violation_type,
            'description'       => $this->description ?: null,
            'severity'          => $this->severity,
            'penalty_status'    => $this->penalty_status ?: null,
            'resolution_status' => $this->resolution_status,
            'resolved_date'     => $this->resolved_date ?: null,
        ];

        if ($this->violationId) {
            $old = Violation::find($this->violationId)?->toArray();
            Violation::findOrFail($this->violationId)->update($data);
            logAudit('update', 'Violation', $this->violationId, $old, $data);
        } else {
            $new = Violation::create($data);
            logAudit('create', 'Violation', $new->violation_id, null, $data);
        }

        Cache::forget('stats:violations');
        Cache::forget('nav:open_violations');
        Cache::forget('stats:compliance_pipeline');
        Cache::forget('dashboard:kpis');

        session()->flash('success', $this->violationId ? 'Violation updated.' : 'Violation recorded.');
        return redirect()->route('violations.index');
    }

    public function render()
    {
        $inspections = Cache::remember('lookup:inspections_recent', 300, fn() =>
            Inspection::with('wasteGenerator:generator_id,generator_name')
                ->select(['inspection_id', 'generator_id', 'inspection_date', 'compliance_status'])
                ->orderByDesc('inspection_date')
                ->limit(100)
                ->get()
        );

        return view('livewire.violations.violation-form', compact('inspections'))
            ->extends('layouts.app');
    }
}

