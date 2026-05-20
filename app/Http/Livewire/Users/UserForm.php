<?php

namespace App\Http\Livewire\Users;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class UserForm extends Component
{
    public ?int $userId = null;

    public string $full_name             = '';
    public string $email                 = '';
    public string $username              = '';
    public string $password              = '';
    public string $password_confirmation = '';
    public string $role_id               = '';
    public string $status                = 'active';

    public function mount($id = null)
    {
        if (!isAdmin()) {
            abort(403, 'Access denied.');
        }
        if ($id) {
            $this->userId = (int) $id;
            $u = User::findOrFail($id);
            $this->full_name = $u->full_name;
            $this->email     = $u->email ?? '';
            $this->username  = $u->username;
            $this->role_id   = (string) $u->role_id;
            $this->status    = $u->status;
        }
    }

    protected function rules(): array
    {
        $pwRule = $this->userId ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed';

        return [
            'full_name' => 'required|string|max:255',
            'email'     => 'nullable|email|max:255|unique:users,email' . ($this->userId ? ",{$this->userId},user_id" : ''),
            'username'  => 'required|string|max:100|unique:users,username' . ($this->userId ? ",{$this->userId},user_id" : ''),
            'password'  => $pwRule,
            'role_id'   => 'required|integer|exists:roles,role_id',
            'status'    => 'required|in:active,inactive',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'full_name' => $this->full_name,
            'email'     => $this->email ?: null,
            'username'  => $this->username,
            'role_id'   => $this->role_id,
            'status'    => $this->status,
        ];

        if ($this->password) {
            $data['password_hash'] = bcrypt($this->password);
        }

        if ($this->userId) {
            $old = User::find($this->userId)?->only(['full_name', 'email', 'username', 'role_id', 'status']);
            User::findOrFail($this->userId)->update($data);
            logAudit('update', 'User', $this->userId, $old, array_diff_key($data, ['password_hash' => '']));
        } else {
            $new = User::create($data);
            logAudit('create', 'User', $new->user_id, null, array_diff_key($data, ['password_hash' => '']));
        }

        Cache::forget('stats:users');
        Cache::forget('audit:users');
        Cache::forget('lookup:users_active');
        Cache::forget('lookup:users_notif');
        Cache::forget('lookup:inspectors');

        session()->flash('success', $this->userId ? 'User updated.' : 'User created.');
        return redirect()->route('users.index');
    }

    public function render()
    {
        $roles = Cache::remember('users:roles', 600, fn() =>
            Role::orderBy('role_name')->get(['role_id', 'role_name'])
        );

        return view('livewire.users.user-form', compact('roles'))
            ->extends('layouts.app');
    }
}

