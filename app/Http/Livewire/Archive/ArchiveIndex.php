<?php

namespace App\Http\Livewire\Archive;

use App\Models\AuditLog;
use App\Models\Report;
use App\Support\Archivable;
use App\Support\Like;
use Livewire\Component;
use Livewire\WithPagination;

class ArchiveIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    /** 'reports' = the generated report files; 'deleted' = archived records. */
    public string $tab = 'reports';

    // Reports tab
    public string $report_type = '';
    public string $period      = '';
    public string $date_from   = '';
    public string $date_to     = '';

    // Deleted-records tab
    public string $record_type      = 'violation_tickets';
    public string $search           = '';
    public string $deleted_from     = '';
    public string $deleted_to       = '';

    public int $perPage = 20;

    private const ARCHIVED_TYPES = ['monthly_waste', 'collection_summary'];

    public function mount()
    {
        if (!canAccess(...ROLES_STAFF())) {
            abort(403, 'Access denied.');
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab === 'deleted' ? 'deleted' : 'reports';
        $this->resetPage();
    }

    public function updatingReportType() { $this->resetPage(); }
    public function updatingPeriod()     { $this->resetPage(); }
    public function updatingRecordType() { $this->resetPage(); }
    public function updatingSearch()     { $this->resetPage(); }

    /**
     * Puts an archived record back.
     *
     * The guard has to live here rather than only in mount(): mount() does not
     * re-run on Livewire action requests, and route middleware never sees them.
     * It mirrors the guard on the matching delete action, so nobody can restore
     * a kind of record they were never allowed to remove.
     */
    public function restore(int $id): void
    {
        $config = Archivable::get($this->record_type);

        if (!$config || !Archivable::canRestore($this->record_type)) {
            abort(403, 'Access denied.');
        }

        $model = $config['model']::onlyTrashed()->findOrFail($id);

        // Re-checked with the row in hand: some types are owned per record
        // rather than gated per role, and only now can that be judged.
        if (!Archivable::canRestore($this->record_type, $model)) {
            abort(403, 'You can only restore your own records.');
        }

        // A child restored under an archived parent would show a dash where the
        // parent's name belongs and count toward totals the parent is missing
        // from. Restoring the parent brings its children back anyway, so the
        // right instruction is to start there.
        foreach ($config['parents'] as $relation => $noun) {
            $parent = $model->{$relation}()->withTrashed()->first();

            if ($parent && $parent->trashed()) {
                session()->flash('error',
                    'Restore the ' . $noun . ' this record belongs to first.');

                return;
            }
        }

        $model->restore();
        logAudit('restore', $config['module'], $id, null, $model->toArray());

        session()->flash('success', $config['label'] . ' record restored.');
    }

    public function render()
    {
        $archives = null;
        $records  = null;
        $deletions = collect();
        $config   = Archivable::get($this->record_type);

        if ($this->tab === 'deleted' && $config) {
            $records = $config['model']::onlyTrashed()
                // Only where the type has a text column to search: LOWER() on a
                // date column needs a database-specific cast, and this codebase
                // keeps its SQL portable.
                ->when($this->search && $config['search'], fn($q) =>
                    $q->whereRaw('LOWER(' . $config['search'] . ') LIKE ?', [Like::contains($this->search)]))
                ->when($this->deleted_from, fn($q) => $q->whereDate('deleted_at', '>=', $this->deleted_from))
                ->when($this->deleted_to,   fn($q) => $q->whereDate('deleted_at', '<=', $this->deleted_to))
                ->orderByDesc('deleted_at')
                ->paginate($this->perPage);

            // Who deleted each row, from the audit trail rather than a
            // deleted_by column: 14 of the 16 delete actions already record the
            // full row there. It can legitimately be missing - logAudit()
            // swallows its own failures - so the view falls back to a dash and
            // nothing here depends on it.
            $deletions = AuditLog::with('user:user_id,full_name')
                ->where('module', $config['module'])
                ->where('action', 'delete')
                ->whereIn('record_id', collect($records->items())->pluck($config['key'])->all())
                ->get()
                ->keyBy('record_id');
        } else {
            $archives = Report::whereIn('report_type', self::ARCHIVED_TYPES)
                ->whereNotNull('period')
                ->when($this->report_type, fn($q) => $q->where('report_type', $this->report_type))
                ->when($this->period,      fn($q) => $q->where('period', $this->period))
                ->when($this->date_from,   fn($q) => $q->where('period_end', '>=', $this->date_from))
                ->when($this->date_to,     fn($q) => $q->where('period_start', '<=', $this->date_to))
                ->orderByDesc('generated_at')
                ->paginate($this->perPage);
        }

        return view('livewire.archive.archive-index', [
            'archives'   => $archives,
            'records'    => $records,
            'deletions'  => $deletions,
            'config'     => $config,
            'types'      => Archivable::options(),
            'canRestore' => $config ? Archivable::canRestore($this->record_type) : false,
        ])->extends('layouts.app');
    }
}
