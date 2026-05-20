<?php

namespace App\Http\Livewire\Entries;

use App\Models\WasteEntry;
use App\Models\WasteGenerator;
use App\Models\WasteCategory;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class EntryIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search      = '';
    public string $generator_id = '';
    public string $category_id  = '';
    public string $date_from   = '';
    public string $date_to     = '';
    public int    $perPage     = 15;

    public function updatingSearch()     { $this->resetPage(); }
    public function updatingGeneratorId(){ $this->resetPage(); }
    public function updatingCategoryId() { $this->resetPage(); }

    public function delete($id)
    {
        $entry = WasteEntry::findOrFail($id);
        logAudit('delete', 'WasteEntry', $id, $entry->toArray());
        $entry->delete();
        Cache::forget('stats:entries');
        Cache::forget('dashboard:kpis');
        Cache::forget('dashboard:recent_entries');
        Cache::forget('dashboard:charts');
        session()->flash('success', 'Entry deleted.');
    }

    public function render()
    {
        $entries = WasteEntry::with([
                'wasteGenerator:generator_id,generator_name',
                'wasteCategory:category_id,category_name',
                'encodedBy:user_id,full_name',
            ])
            ->when($this->search, fn($q) =>
                $q->whereHas('wasteGenerator', fn($q2) =>
                    $q2->where('generator_name', 'like', '%' . $this->search . '%')
                )
            )
            ->when($this->generator_id, fn($q) => $q->where('generator_id', $this->generator_id))
            ->when($this->category_id,  fn($q) => $q->where('category_id', $this->category_id))
            ->when($this->date_from,    fn($q) => $q->where('entry_date', '>=', $this->date_from))
            ->when($this->date_to,      fn($q) => $q->where('entry_date', '<=', $this->date_to))
            ->orderByDesc('entry_date')
            ->paginate($this->perPage);

        $generators = Cache::remember('lookup:generators', 300, fn() =>
            WasteGenerator::orderBy('generator_name')->get(['generator_id', 'generator_name'])
        );
        $categories = Cache::remember('lookup:categories', 600, fn() =>
            WasteCategory::orderBy('category_name')->get(['category_id', 'category_name'])
        );

        $stats = Cache::remember('stats:entries', 60, function () {
            $monthStart = now()->startOfMonth()->toDateString();
            $today      = today()->toDateString();
            $row = WasteEntry::selectRaw("
                COUNT(*) AS total,
                SUM(entry_date >= ?) AS month_entries,
                SUM(CASE WHEN entry_date >= ? THEN quantity ELSE 0 END) AS month_volume,
                SUM(entry_date = ?) AS today_entries
            ", [$monthStart, $monthStart, $today])->first();
            return [
                'total'         => (int) $row->total,
                'month_entries' => (int) $row->month_entries,
                'month_volume'  => (float) $row->month_volume,
                'today_entries' => (int) $row->today_entries,
            ];
        });

        return view('livewire.entries.entry-index', compact('entries', 'generators', 'categories', 'stats'))
            ->extends('layouts.app');
    }
}
