<?php

namespace App\Http\Livewire\Barangays;

use App\Models\Barangay;
use App\Models\BarangaySector;
use App\Models\SectorMember;
use Livewire\Component;

class BarangayIndex extends Component
{
    public string $search     = '';
    public ?int   $selectedId = null;

    // ── Barangay modal ──
    public bool   $showBrgyModal = false;
    public ?int   $editingBrgyId = null;
    public string $brgy_name     = '';

    // ── Sector members modal ──
    public bool   $showMembersModal          = false;
    public ?int   $viewingSectorId           = null;
    public string $viewingSectorName         = '';
    public int    $viewingSectorNumber       = 0;
    public string $viewingSectorBarangay     = '';
    public array  $sectorMembers             = [];
    public bool   $showAddMemberForm         = false;
    public string $member_name               = '';
    public string $member_address            = '';
    public string $member_contact            = '';

    // ── Sector edit modal ──
    public bool   $showSectorModal           = false;
    public ?int   $editingSectorId           = null;
    public string $sector_name              = '';
    public int    $editingSectorNumber      = 0;
    public string $editingSectorBarangay    = '';
    // new detail fields
    public string $sector_household_count   = '';
    public string $sector_leader_name       = '';
    public string $sector_leader_contact    = '';
    public string $sector_daily_waste        = '';
    public string $sector_waste_frequency   = '';
    public string $sector_collection_day    = '';

    // ── Select / deselect ──
    public function select(int $id): void
    {
        $this->selectedId = ($this->selectedId === $id) ? null : $id;
    }

    // ── Barangay CRUD ──
    public function openAdd(): void
    {
        $this->reset('brgy_name', 'editingBrgyId');
        $this->showBrgyModal = true;
    }

    public function openEdit(int $id): void
    {
        $b = Barangay::findOrFail($id);
        $this->editingBrgyId = $id;
        $this->brgy_name     = $b->barangay_name;
        $this->showBrgyModal = true;
    }

    public function saveBrgy(): void
    {
        $this->validate(['brgy_name' => 'required|string|max:100']);

        if ($this->editingBrgyId) {
            $b = Barangay::findOrFail($this->editingBrgyId);
            $old = $b->toArray();
            $b->update(['barangay_name' => $this->brgy_name]);
            logAudit('update', 'Barangay', $b->barangay_id, $old, $b->fresh()->toArray());
            session()->flash('success', 'Barangay name updated.');
        } else {
            $b = Barangay::create([
                'barangay_name' => $this->brgy_name,
                'municipality'  => 'Madrid',
                'province'      => 'Surigao del Sur',
            ]);
            for ($i = 1; $i <= 6; $i++) {
                BarangaySector::create([
                    'barangay_id'   => $b->barangay_id,
                    'sector_number' => $i,
                    'sector_name'   => 'Sector ' . $i,
                ]);
            }
            logAudit('create', 'Barangay', $b->barangay_id, null, $b->toArray());
            $this->selectedId = $b->barangay_id;
            session()->flash('success', 'Barangay added with 6 default sectors.');
        }

        $this->showBrgyModal = false;
    }

    public function deleteBrgy(int $id): void
    {
        $b = Barangay::withCount(['wasteGenerators', 'collectionSchedules', 'incidents'])
                     ->findOrFail($id);

        $linked = [];
        if ($b->waste_generators_count)     $linked[] = $b->waste_generators_count    . ' waste generator(s)';
        if ($b->collection_schedules_count) $linked[] = $b->collection_schedules_count . ' collection schedule(s)';
        if ($b->incidents_count)            $linked[] = $b->incidents_count            . ' incident(s)';

        if (!empty($linked)) {
            session()->flash('error',
                'Cannot delete "' . $b->barangay_name . '" — it has linked records: ' . implode(', ', $linked) . '.'
            );
            return;
        }

        logAudit('delete', 'Barangay', $id, $b->toArray());
        $b->delete();
        if ($this->selectedId === $id) {
            $this->selectedId = null;
        }
        session()->flash('success', 'Barangay deleted.');
    }

    // ── Sector members ──
    public function openSectorMembers(int $sectorId): void
    {
        $s = BarangaySector::with(['barangay:barangay_id,barangay_name', 'members'])->findOrFail($sectorId);

        $this->viewingSectorId       = $sectorId;
        $this->viewingSectorName     = $s->sector_name;
        $this->viewingSectorNumber   = $s->sector_number;
        $this->viewingSectorBarangay = $s->barangay->barangay_name ?? '';
        $this->sectorMembers         = $s->members->toArray();
        $this->showAddMemberForm     = false;
        $this->reset('member_name', 'member_address', 'member_contact');
        $this->showMembersModal      = true;
    }

    public function saveNewMember(): void
    {
        $this->validate([
            'member_name'    => 'required|string|max:150',
            'member_address' => 'nullable|string|max:255',
            'member_contact' => 'nullable|string|max:30',
        ]);

        $member = SectorMember::create([
            'sector_id'      => $this->viewingSectorId,
            'full_name'      => $this->member_name,
            'address'        => $this->member_address ?: null,
            'contact_number' => $this->member_contact ?: null,
        ]);

        logAudit('create', 'SectorMember', $member->member_id, null, $member->toArray());

        $this->sectorMembers     = SectorMember::where('sector_id', $this->viewingSectorId)->orderBy('full_name')->get()->toArray();
        $this->showAddMemberForm = false;
        $this->reset('member_name', 'member_address', 'member_contact');
    }

    public function deleteMember(int $memberId): void
    {
        $member = SectorMember::findOrFail($memberId);
        logAudit('delete', 'SectorMember', $memberId, $member->toArray());
        $member->delete();
        $this->sectorMembers = SectorMember::where('sector_id', $this->viewingSectorId)->orderBy('full_name')->get()->toArray();
    }

    // ── Sector edit ──
    public function openEditSector(int $sectorId): void
    {
        $s = BarangaySector::with('barangay:barangay_id,barangay_name')->findOrFail($sectorId);

        $this->editingSectorId        = $sectorId;
        $this->editingSectorNumber    = $s->sector_number;
        $this->editingSectorBarangay  = $s->barangay->barangay_name ?? '';
        $this->sector_name            = $s->sector_name;
        $this->sector_household_count = (string) ($s->household_count ?? '');
        $this->sector_leader_name     = $s->purok_leader_name ?? '';
        $this->sector_leader_contact  = $s->purok_leader_contact ?? '';
        $this->sector_daily_waste      = (string) ($s->estimated_daily_waste_kg ?? '');
        $this->sector_waste_frequency  = $s->waste_frequency ?? '';
        $this->sector_collection_day   = $s->collection_day ?? '';
        $this->showSectorModal        = true;
    }

    public function saveSector(): void
    {
        $this->validate([
            'sector_name'             => 'required|string|max:100',
            'sector_household_count'  => 'nullable|integer|min:0|max:9999',
            'sector_leader_name'      => 'nullable|string|max:100',
            'sector_leader_contact'   => 'nullable|string|max:30',
            'sector_daily_waste'       => 'nullable|numeric|min:0|max:99999',
            'sector_waste_frequency'   => 'nullable|string|in:daily,weekly,monthly',
            'sector_collection_day'    => 'nullable|string|max:15',
        ]);

        $s   = BarangaySector::findOrFail($this->editingSectorId);
        $old = $s->toArray();

        $s->update([
            'sector_name'              => $this->sector_name,
            'household_count'          => $this->sector_household_count !== '' ? (int) $this->sector_household_count : null,
            'purok_leader_name'        => $this->sector_leader_name ?: null,
            'purok_leader_contact'     => $this->sector_leader_contact ?: null,
            'estimated_daily_waste_kg' => $this->sector_daily_waste !== '' ? (float) $this->sector_daily_waste : null,
            'waste_frequency'          => $this->sector_waste_frequency ?: null,
            'collection_day'           => $this->sector_collection_day ?: null,
        ]);

        logAudit('update', 'BarangaySector', $s->sector_id, $old, $s->fresh()->toArray());

        $this->showSectorModal = false;
        session()->flash('success', 'Sector updated.');
    }

    public function render()
    {
        $barangays = Barangay::with('sectors')
            ->when($this->search, fn($q) => $q->where('barangay_name', 'ilike', '%' . $this->search . '%'))
            ->orderBy('barangay_name')
            ->get();

        $selected = $this->selectedId
            ? $barangays->firstWhere('barangay_id', $this->selectedId)
            : null;

        $totalSectors = $barangays->sum(fn($b) => $b->sectors->count());

        return view('livewire.barangays.barangay-index',
            compact('barangays', 'selected', 'totalSectors')
        )->extends('layouts.app');
    }
}
