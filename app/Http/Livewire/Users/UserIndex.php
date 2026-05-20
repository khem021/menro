<?php

namespace App\Http\Livewire\Users;

use App\Models\Role;
use App\Models\User;
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
        if ($id === session('auth_user_id')) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }
        $u = User::findOrFail($id);
        logAudit('delete', 'User', $id, ['username' => $u->username]);
        $u->delete();
        Cache::forget('stats:users');
        Cache::forget('audit:users');
        Cache::forget('lookup:users_active');
        Cache::forget('lookup:users_notif');
        Cache::forget('lookup:inspectors');
        session()->flash('success', 'User deleted.');
    }

    public function render()
    {
        $users = User::with('role:role_id,role_name')
            ->when($this->search, fn($q) =>
                $q->where(fn($w) => $w
                    ->where('full_name', 'like', "%{$this->search}%")
                    ->orWhere('username', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))
            )
            ->when($this->role_id, fn($q) => $q->where('role_id', $this->role_id))
            ->when($this->status,  fn($q) => $q->where('status', $this->status))
            ->orderBy('full_name')
            ->paginate($this->perPage);

        $roles = Cache::remember('users:roles', 600, fn() =>
            Role::orderBy('role_name')->get(['role_id', 'role_name'])
        );

        $stats = Cache::remember('stats:users', 60, function () {
            $row = User::selectRaw("COUNT(*) AS total, SUM(status = 'active') AS active")->first();
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
