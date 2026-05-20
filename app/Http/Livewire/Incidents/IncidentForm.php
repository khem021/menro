<?php

namespace App\Http\Livewire\Incidents;

use App\Models\Barangay;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class IncidentForm extends Component
{
    public ?int $incidentId = null;

    public string $barangay_id       = '';
    public string $incident_type     = 'illegal_dumping';
    public string $description       = '';
    public string $location_details  = '';
    public string $date_reported     = '';
    public string $status            = 'reported';
    public string $assigned_to       = '';
    public string $resolution_notes  = '';

    protected $rules = [
        'barangay_id'      => 'required|integer|exists:barangays,barangay_id',
        'incident_type'    => 'required|in:illegal_dumping,open_burning,improper_disposal,other',
        'description'      => 'required|string|max:2000',
        'location_details' => 'nullable|string|max:1000',
        'date_reported'    => 'required|date',
        'status'           => 'required|in:reported,for_validation,under_investigation,resolved,closed',
        'assigned_to'      => 'nullable|integer|exists:users,user_id',
        'resolution_notes' => 'nullable|string|max:2000',
    ];

    public function mount($id = null)
    {
        $this->date_reported = now()->format('Y-m-d');

        if ($id) {
            $this->incidentId = (int) $id;
            $i = Incident::findOrFail($id);
            $this->barangay_id      = (string) $i->barangay_id;
            $this->incident_type    = $i->incident_type;
            $this->description      = $i->description;
            $this->location_details = $i->location_details ?? '';
            $this->date_reported    = $i->date_reported;
            $this->status           = $i->status;
            $this->assigned_to      = (string) ($i->assigned_to ?? '');
            $this->resolution_notes = $i->resolution_notes ?? '';
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'barangay_id'      => $this->barangay_id,
            'incident_type'    => $this->incident_type,
            'description'      => $this->description,
            'location_details' => $this->location_details ?: null,
            'date_reported'    => $this->date_reported,
            'status'           => $this->status,
            'assigned_to'      => $this->assigned_to ?: null,
            'resolution_notes' => $this->resolution_notes ?: null,
        ];

        if ($this->incidentId) {
            $old = Incident::find($this->incidentId)?->toArray();
            $inc = Incident::findOrFail($this->incidentId);
            if ($this->status === 'resolved' && $inc->status !== 'resolved') {
                $data['resolved_at'] = now();
            }
            $inc->update($data);
            logAudit('update', 'Incident', $this->incidentId, $old, $data);
        } else {
            $data['reported_by'] = session('auth_user_id');
            $new = Incident::create($data);
            logAudit('create', 'Incident', $new->incident_id, null, $data);
        }

        Cache::forget('stats:incidents');
        Cache::forget('stats:compliance_pipeline');
        Cache::forget('dashboard:kpis');
        Cache::forget('dashboard:recent_incidents');

        session()->flash('success', $this->incidentId ? 'Incident updated.' : 'Incident reported.');
        return redirect()->route('incidents.index');
    }

    public function render()
    {
        $barangays = Cache::remember('lookup:barangays', 600, fn() =>
            Barangay::orderBy('barangay_name')->get(['barangay_id', 'barangay_name'])
        );
        $officers = Cache::remember('lookup:users_active', 300, fn() =>
            User::where('status', 'active')->orderBy('full_name')->get(['user_id', 'full_name'])
        );

        return view('livewire.incidents.incident-form', compact('barangays', 'officers'))
            ->extends('layouts.app');
    }
}

