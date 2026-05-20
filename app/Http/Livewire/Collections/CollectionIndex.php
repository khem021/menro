<?php

namespace App\Http\Livewire\Collections;

use App\Models\Barangay;
use App\Models\CollectionSchedule;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class CollectionIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $barangay_id = '';
    public string $status      = '';
    public string $date_from   = '';
    public string $date_to     = '';
    public int    $perPage     = 15;

    public function updatingBarangayId(){ $this->resetPage(); }
    public function updatingStatus()   { $this->resetPage(); }

    public function delete($id)
    {
        $s = CollectionSchedule::findOrFail($id);
        logAudit('delete', 'CollectionSchedule', $id, $s->toArray());
        $s->delete();
        Cache::forget('stats:collections');
        Cache::forget('dashboard:upcoming_collections');
        session()->flash('success', 'Collection schedule deleted.');
    }

    public function markCompleted($id)
    {
        $s = CollectionSchedule::findOrFail($id);
        $s->update(['status' => 'completed']);
        logAudit('update', 'CollectionSchedule', $id, ['status' => $s->getOriginal('status')], ['status' => 'completed']);
        Cache::forget('stats:collections');
        Cache::forget('dashboard:upcoming_collections');
        session()->flash('success', 'Collection marked as completed.');
    }

    public function render()
    {
        $collections = CollectionSchedule::with([
                'barangay:barangay_id,barangay_name',
                'createdBy:user_id,full_name',
            ])
            ->when($this->barangay_id, fn($q) => $q->where('barangay_id', $this->barangay_id))
            ->when($this->status,      fn($q) => $q->where('status', $this->status))
            ->when($this->date_from,   fn($q) => $q->where('collection_date', '>=', $this->date_from))
            ->when($this->date_to,     fn($q) => $q->where('collection_date', '<=', $this->date_to))
            ->orderByDesc('collection_date')
            ->paginate($this->perPage);

        $barangays = Cache::remember('lookup:barangays', 600, fn() =>
            Barangay::orderBy('barangay_name')->get(['barangay_id', 'barangay_name'])
        );

        $stats = Cache::remember('stats:collections', 60, function () {
            $row = CollectionSchedule::selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed
            ")->first();
            return [
                'total'     => (int) $row->total,
                'pending'   => (int) $row->pending,
                'confirmed' => (int) $row->confirmed,
                'completed' => (int) $row->completed,
            ];
        });

        return view('livewire.collections.collection-index', compact('collections', 'barangays', 'stats'))
            ->extends('layouts.app');
    }
}
