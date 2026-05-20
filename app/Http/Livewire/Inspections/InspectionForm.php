<?php

namespace App\Http\Livewire\Inspections;

use App\Models\Inspection;
use App\Models\User;
use App\Models\WasteGenerator;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class InspectionForm extends Component
{
    public ?int $inspectionId = null;

    public string $generator_id      = '';
    public string $inspection_date   = '';
    public string $inspector_id      = '';
    public string $compliance_status = 'compliant';
    public string $segregation_score = '';
    public string $remarks           = '';
    public string $recommendation    = '';
    public string $next_follow_up    = '';

    protected $rules = [
        'generator_id'      => 'required|integer|exists:waste_generators,generator_id',
        'inspection_date'   => 'required|date',
        'inspector_id'      => 'required|integer|exists:users,user_id',
        'compliance_status' => 'required|in:compliant,warning,for_follow_up,violation',
        'segregation_score' => 'nullable|integer|min:0|max:100',
        'remarks'           => 'nullable|string|max:2000',
        'recommendation'    => 'nullable|string|max:2000',
        'next_follow_up'    => 'nullable|date',
    ];

    public function mount($id = null)
    {
        $this->inspection_date = now()->format('Y-m-d');
        $this->inspector_id    = (string) session('auth_user_id');

        if ($id) {
            $this->inspectionId = (int) $id;
            $i = Inspection::findOrFail($id);
            $this->generator_id      = (string) $i->generator_id;
            $this->inspection_date   = $i->inspection_date;
            $this->inspector_id      = (string) $i->inspector_id;
            $this->compliance_status = $i->compliance_status;
            $this->segregation_score = (string) ($i->segregation_score ?? '');
            $this->remarks           = $i->remarks ?? '';
            $this->recommendation    = $i->recommendation ?? '';
            $this->next_follow_up    = $i->next_follow_up ?? '';
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'generator_id'      => $this->generator_id,
            'inspection_date'   => $this->inspection_date,
            'inspector_id'      => $this->inspector_id,
            'compliance_status' => $this->compliance_status,
            'segregation_score' => $this->segregation_score ?: null,
            'remarks'           => $this->remarks ?: null,
            'recommendation'    => $this->recommendation ?: null,
            'next_follow_up'    => $this->next_follow_up ?: null,
        ];

        $new = null;
        if ($this->inspectionId) {
            $old = Inspection::find($this->inspectionId)?->toArray();
            Inspection::findOrFail($this->inspectionId)->update($data);
            logAudit('update', 'Inspection', $this->inspectionId, $old, $data);
        } else {
            $new = Inspection::create($data);
            logAudit('create', 'Inspection', $new->inspection_id, null, $data);
        }

        // Update generator compliance status
        $generatorCompliance = match($this->compliance_status) {
            'compliant'    => 'compliant',
            'warning',
            'for_follow_up'=> 'for_inspection',
            'violation'    => 'non_compliant',
            default        => 'for_inspection',
        };
        WasteGenerator::where('generator_id', $this->generator_id)
            ->update(['compliance_status' => $generatorCompliance]);

        Cache::forget('stats:inspections');
        Cache::forget('stats:generators');
        Cache::forget('stats:compliance_pipeline');
        Cache::forget('dashboard:kpis');

        $savedId = $this->inspectionId ?? ($new->inspection_id ?? null);

        if ($this->compliance_status === 'violation' && $savedId) {
            session()->flash('success', $this->inspectionId ? 'Inspection updated with violation status.' : 'Inspection recorded with violation status.');
            session()->flash('success_cta', [
                'label' => 'Record Violation Now →',
                'url'   => route('violations.create') . '?inspection_id=' . $savedId,
            ]);
        } else {
            session()->flash('success', $this->inspectionId ? 'Inspection updated.' : 'Inspection recorded.');
        }

        return redirect()->route('inspections.index');
    }

    public function render()
    {
        $generators = Cache::remember('lookup:generators_active', 300, fn() =>
            WasteGenerator::where('status', 'active')->orderBy('generator_name')->get(['generator_id', 'generator_name'])
        );
        $inspectors = Cache::remember('lookup:inspectors', 600, fn() =>
            User::whereHas('role', fn($q) => $q->whereIn('role_name', ['Field Inspector', 'MENRO Officer', 'System Administrator']))
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get(['user_id', 'full_name'])
        );

        return view('livewire.inspections.inspection-form', compact('generators', 'inspectors'))
            ->extends('layouts.app');
    }
}

