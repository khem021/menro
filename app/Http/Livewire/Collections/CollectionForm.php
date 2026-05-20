<?php

namespace App\Http\Livewire\Collections;

use App\Models\Barangay;
use App\Models\CollectionSchedule;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class CollectionForm extends Component
{
    public ?int $scheduleId = null;

    public string $barangay_id      = '';
    public string $collection_date  = '';
    public string $waste_type       = 'mixed';
    public string $assigned_team    = '';
    public string $assigned_vehicle = '';
    public string $status           = 'pending';
    public string $notes            = '';

    protected $rules = [
        'barangay_id'      => 'required|integer|exists:barangays,barangay_id',
        'collection_date'  => 'required|date',
        'waste_type'       => 'required|string|max:100',
        'assigned_team'    => 'nullable|string|max:255',
        'assigned_vehicle' => 'nullable|string|max:255',
        'status'           => 'required|in:pending,confirmed,completed,missed,cancelled',
        'notes'            => 'nullable|string|max:1000',
    ];

    public function mount($id = null)
    {
        $this->collection_date = now()->addDay()->format('Y-m-d');

        if ($id) {
            $this->scheduleId = (int) $id;
            $s = CollectionSchedule::findOrFail($id);
            $this->barangay_id      = (string) $s->barangay_id;
            $this->collection_date  = $s->collection_date;
            $this->waste_type       = $s->waste_type;
            $this->assigned_team    = $s->assigned_team ?? '';
            $this->assigned_vehicle = $s->assigned_vehicle ?? '';
            $this->status           = $s->status;
            $this->notes            = $s->notes ?? '';
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'barangay_id'      => $this->barangay_id,
            'collection_date'  => $this->collection_date,
            'waste_type'       => $this->waste_type,
            'assigned_team'    => $this->assigned_team ?: null,
            'assigned_vehicle' => $this->assigned_vehicle ?: null,
            'status'           => $this->status,
            'notes'            => $this->notes ?: null,
        ];

        if ($this->scheduleId) {
            $old = CollectionSchedule::find($this->scheduleId)?->toArray();
            CollectionSchedule::findOrFail($this->scheduleId)->update($data);
            logAudit('update', 'CollectionSchedule', $this->scheduleId, $old, $data);
        } else {
            $data['created_by'] = session('auth_user_id');
            $new = CollectionSchedule::create($data);
            logAudit('create', 'CollectionSchedule', $new->schedule_id, null, $data);
        }

        Cache::forget('stats:collections');
        Cache::forget('dashboard:upcoming_collections');

        session()->flash('success', $this->scheduleId ? 'Schedule updated.' : 'Schedule created.');
        return redirect()->route('collections.index');
    }

    public function render()
    {
        $barangays = Cache::remember('lookup:barangays', 600, fn() =>
            Barangay::orderBy('barangay_name')->get(['barangay_id', 'barangay_name'])
        );

        return view('livewire.collections.collection-form', compact('barangays'))
            ->extends('layouts.app');
    }
}

