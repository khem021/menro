<?php

namespace App\Http\Livewire\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class AuditIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $module    = '';
    public string $action    = '';
    public string $user_id   = '';
    public string $date_from = '';
    public string $date_to   = '';
    public int    $perPage   = 20;

    public function mount()
    {
        if (!isAdmin()) {
            abort(403, 'Access denied.');
        }
    }

    public function updatingModule()  { $this->resetPage(); }
    public function updatingAction()  { $this->resetPage(); }
    public function updatingUserId()  { $this->resetPage(); }

    public function render()
    {
        $logs = AuditLog::with('user:user_id,full_name')
            ->when($this->module,    fn($q) => $q->where('module', $this->module))
            ->when($this->action,    fn($q) => $q->where('action', $this->action))
            ->when($this->user_id,   fn($q) => $q->where('user_id', $this->user_id))
            ->when($this->date_from, fn($q) => $q->where('created_at', '>=', $this->date_from . ' 00:00:00'))
            ->when($this->date_to,   fn($q) => $q->where('created_at', '<=', $this->date_to   . ' 23:59:59'))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        $modules = Cache::remember('audit:modules', 300, fn() =>
            AuditLog::query()->distinct()->orderBy('module')->pluck('module')
        );
        $actions = Cache::remember('audit:actions', 300, fn() =>
            AuditLog::query()->distinct()->orderBy('action')->pluck('action')
        );
        $users   = Cache::remember('audit:users', 300, fn() =>
            User::orderBy('full_name')->get(['user_id', 'full_name'])
        );

        return view('livewire.audit.audit-index', compact('logs', 'modules', 'actions', 'users'))
            ->extends('layouts.app');
    }
}
