<?php

namespace App\Http\Livewire\Users;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
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
            // withoutTrashed() so an archived account stops reserving its
            // username and email - the partial unique indexes on the table say
            // the same thing, and the validator has to agree with them.
            'email'     => ['nullable', 'email', 'max:255',
                            Rule::unique('users', 'email')->ignore($this->userId, 'user_id')->withoutTrashed()],
            'username'  => ['required', 'string', 'max:100',
                            Rule::unique('users', 'username')->ignore($this->userId, 'user_id')->withoutTrashed()],
            'password'  => $pwRule,
            'role_id'   => 'required|integer|exists:roles,role_id',
            'status'    => 'required|in:active,inactive',
        ];
    }

    public function save()
    {
        // mount() does not re-run on Livewire action requests, and route
        // middleware never sees them, so the check has to live here too.
        requireRole('System Administrator');

        $this->validate();

        // With only two roles, /users is the only thing the administrator role
        // still owns. Demoting or deactivating the last active administrator
        // makes this page unreachable and there is no way back through the UI.
        if ($this->userId && $this->wouldStrandTheLastAdmin()) {
            $this->addError('role_id', 'This is the only active administrator. Promote someone else first.');

            return;
        }

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

            // Resetting someone's password signs their other sessions out. When
            // an admin resets their own, keep this session alive.
            if (isset($data['password_hash']) && (int) $this->userId === (int) session('auth_user_id')) {
                session(['auth_pw' => passwordFingerprint($data['password_hash'])]);
            }
        } else {
            $new = User::create($data);
            logAudit('create', 'User', $new->user_id, null, array_diff_key($data, ['password_hash' => '']));
        }

        session()->flash('success', $this->userId ? 'User updated.' : 'User created.');
        return redirect()->route('users.index');
    }

    /**
     * True when this edit would take the last active administrator out of the
     * role, whether by changing the role or by deactivating the account.
     */
    private function wouldStrandTheLastAdmin(): bool
    {
        $adminRoleId = (int) Role::where('role_name', 'System Administrator')->value('role_id');
        $current     = User::findOrFail($this->userId);

        $wasAdmin   = (int) $current->role_id === $adminRoleId && $current->status === 'active';
        $staysAdmin = (int) $this->role_id === $adminRoleId && $this->status === 'active';

        if (! $wasAdmin || $staysAdmin) {
            return false;
        }

        return User::where('role_id', $adminRoleId)
            ->where('status', 'active')
            ->where('user_id', '!=', $this->userId)
            ->doesntExist();
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

