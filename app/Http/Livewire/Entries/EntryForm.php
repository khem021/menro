<?php

namespace App\Http\Livewire\Entries;

use App\Models\WasteEntry;
use App\Models\WasteGenerator;
use App\Models\WasteCategory;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class EntryForm extends Component
{
    public ?int $entryId = null;

    public string $generator_id = '';
    public string $category_id  = '';
    public string $quantity      = '';
    public string $unit          = 'kg';
    public string $entry_date   = '';
    public string $remarks      = '';

    protected $rules = [
        'generator_id' => 'required|integer|exists:waste_generators,generator_id',
        'category_id'  => 'required|integer|exists:waste_categories,category_id',
        'quantity'     => 'required|numeric|min:0.01',
        'unit'         => 'required|in:kg,ton,liter,cubic_meter',
        'entry_date'   => 'required|date',
        'remarks'      => 'nullable|string|max:1000',
    ];

    public function mount($id = null)
    {
        $this->entry_date = now()->format('Y-m-d');
        if ($id) {
            $this->entryId = (int) $id;
            $e = WasteEntry::findOrFail($id);
            $this->generator_id = (string) $e->generator_id;
            $this->category_id  = (string) $e->category_id;
            $this->quantity     = (string) $e->quantity;
            $this->unit         = $e->unit;
            $this->entry_date   = $e->entry_date;
            $this->remarks      = $e->remarks ?? '';
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'generator_id' => $this->generator_id,
            'category_id'  => $this->category_id,
            'quantity'     => $this->quantity,
            'unit'         => $this->unit,
            'entry_date'   => $this->entry_date,
            'remarks'      => $this->remarks ?: null,
        ];

        if ($this->entryId) {
            $old = WasteEntry::find($this->entryId)?->toArray();
            WasteEntry::findOrFail($this->entryId)->update($data);
            logAudit('update', 'WasteEntry', $this->entryId, $old, $data);
        } else {
            $data['encoded_by'] = session('auth_user_id');
            $new = WasteEntry::create($data);
            logAudit('create', 'WasteEntry', $new->entry_id, null, $data);
        }

        Cache::forget('stats:entries');
        Cache::forget('dashboard:kpis');
        Cache::forget('dashboard:recent_entries');
        Cache::forget('dashboard:charts');

        session()->flash('success', $this->entryId ? 'Entry updated.' : 'Entry recorded.');
        return redirect()->route('entries.index');
    }

    public function render()
    {
        $generators = Cache::remember('lookup:generators_active', 300, fn() =>
            WasteGenerator::where('status', 'active')->orderBy('generator_name')->get(['generator_id', 'generator_name'])
        );
        $categories = Cache::remember('lookup:categories', 600, fn() =>
            WasteCategory::orderBy('category_name')->get(['category_id', 'category_name'])
        );

        return view('livewire.entries.entry-form', compact('generators', 'categories'))
            ->extends('layouts.app');
    }
}

