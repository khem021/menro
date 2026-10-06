<?php

namespace App\Http\Livewire\Users;

use App\Models\Role;
use App\Models\User;
use App\Support\Like;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search  = '';
    public string $role_id = '';
    public string $status  = '';
    public int    $perPage = 15;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingRoleId() { $this->resetPage(); }
    public function updatingStatus() { $this->resetPage(); }

    public function mount()
    {
        if (!isAdmin()) {
            abort(403, 'Access denied.');
        }
    }

    public function delete($id)
    {
        // mount() does not re-run on Livewire action requests, and route
        // middleware never sees them, so the check has to live here too.
        requireRole('System Administrator');

        if ($id === session('auth_user_id')) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }
        $u = User::findOrFail($id);
        logAudit('delete', 'User', $id, ['username' => $u->username]);
        $u->delete();
        session()->flash('success', 'User deleted.');
    }

    public function render()
    {
        $users = User::with('role:role_id,role_name')
            ->when($this->search, fn($q) =>
                $q->where(fn($w) => $w
                    ->whereRaw('LOWER(full_name) LIKE ?', [Like::contains($this->search)])
                    ->orWhereRaw('LOWER(username) LIKE ?', [Like::contains($this->search)])
                    ->orWhereRaw('LOWER(email) LIKE ?', [Like::contains($this->search)]))
            )
            ->when($this->role_id, fn($q) => $q->where('role_id', $this->role_id))
            ->when($this->status,  fn($q) => $q->where('status', $this->status))
            ->orderBy('full_name')
            ->paginate($this->perPage);

        $roles = Cache::remember('users:roles', 600, fn() =>
            Role::orderBy('role_name')->get(['role_id', 'role_name'])
        );

        $stats = Cache::remember('stats:users', 60, function () {
            $row = User::selectRaw("COUNT(*) AS total, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active")->first();
            return [
                'total'    => (int) $row->total,
                'active'   => (int) $row->active,
                'inactive' => (int) ($row->total - $row->active),
            ];
        });
        $stats['roles_count'] = $roles->count();

        return view('livewire.users.user-index', compact('users', 'roles', 'stats'))
            ->extends('layouts.app');
    }
}
