<?php

namespace App\Http\Livewire\Generators;

use App\Models\Barangay;
use App\Models\GeneratorType;
use App\Models\WasteGenerator;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class GeneratorForm extends Component
{
    public ?int $generatorId = null;

    public string $generator_name          = '';
    public string $generator_type_id       = '';
    public string $barangay_id             = '';
    public string $address                 = '';
    public string $contact_person          = '';
    public string $contact_number          = '';
    public string $email                   = '';
    public string $estimated_daily_waste_kg = '';
    public string $compliance_status       = 'for_inspection';
    public string $status                  = 'active';

    protected $rules = [
        'generator_name'           => 'required|string|max:255',
        'generator_type_id'        => 'required|integer|exists:generator_types,generator_type_id',
        'barangay_id'              => 'required|integer|exists:barangays,barangay_id',
        'address'                  => 'nullable|string|max:500',
        'contact_person'           => 'nullable|string|max:255',
        'contact_number'           => 'nullable|string|max:50',
        'email'                    => 'nullable|email|max:255',
        'estimated_daily_waste_kg' => 'nullable|numeric|min:0',
        'compliance_status'        => 'required|in:compliant,for_inspection,non_compliant',
        'status'                   => 'required|in:active,inactive',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $this->generatorId = (int) $id;
            $g = WasteGenerator::findOrFail($id);
            $this->generator_name           = $g->generator_name;
            $this->generator_type_id        = (string) $g->generator_type_id;
            $this->barangay_id              = (string) $g->barangay_id;
            $this->address                  = $g->address ?? '';
            $this->contact_person           = $g->contact_person ?? '';
            $this->contact_number           = $g->contact_number ?? '';
            $this->email                    = $g->email ?? '';
            $this->estimated_daily_waste_kg = (string) ($g->estimated_daily_waste_kg ?? '');
            $this->compliance_status        = $g->compliance_status;
            $this->status                   = $g->status;
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'generator_name'           => $this->generator_name,
            'generator_type_id'        => $this->generator_type_id,
            'barangay_id'              => $this->barangay_id,
            'address'                  => $this->address ?: null,
            'contact_person'           => $this->contact_person ?: null,
            'contact_number'           => $this->contact_number ?: null,
            'email'                    => $this->email ?: null,
            'estimated_daily_waste_kg' => $this->estimated_daily_waste_kg ?: null,
            'compliance_status'        => $this->compliance_status,
            'status'                   => $this->status,
        ];

        if ($this->generatorId) {
            $old = WasteGenerator::find($this->generatorId)?->toArray();
            WasteGenerator::findOrFail($this->generatorId)->update($data);
            logAudit('update', 'WasteGenerator', $this->generatorId, $old, $data);
        } else {
            $data['created_by'] = session('auth_user_id');
            $new = WasteGenerator::create($data);
            logAudit('create', 'WasteGenerator', $new->generator_id, null, $data);
        }

        Cache::forget('stats:generators');
        Cache::forget('lookup:generators');
        Cache::forget('lookup:generators_active');
        Cache::forget('dashboard:kpis');

        session()->flash('success', $this->generatorId ? 'Generator updated.' : 'Generator created.');
        return redirect()->route('generators.index');
    }

    public function render()
    {
        $generatorTypes = Cache::remember('lookup:generator_types', 600, fn() =>
            GeneratorType::orderBy('type_name')->get()
        );
        $barangays = Cache::remember('lookup:barangays_full', 600, fn() =>
            Barangay::orderBy('barangay_name')->get()
        );

        return view('livewire.generators.generator-form', compact('generatorTypes', 'barangays'))
            ->extends('layouts.app');
    }
}

