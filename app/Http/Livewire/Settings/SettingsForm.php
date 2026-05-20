<?php

namespace App\Http\Livewire\Settings;

use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

class SettingsForm extends Component
{
    use WithFileUploads;

    // ── Active tab ────────────────────────────────────────────────────────────
    public string $activeTab = 'profile';

    // ── Profile ───────────────────────────────────────────────────────────────
    public string $full_name = '';
    public string $email     = '';
    public string $username  = '';

    // ── Avatar ────────────────────────────────────────────────────────────────
    public $photo = null;           // Livewire temp upload

    // ── Password ──────────────────────────────────────────────────────────────
    public string $current_password          = '';
    public string $new_password              = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        if (!isAdmin()) {
            abort(403, 'Access denied.');
        }

        $user = $this->currentUser();
        $this->full_name = $user->full_name;
        $this->email     = $user->email ?? '';
        $this->username  = $user->username;
    }

    // ── Save profile ──────────────────────────────────────────────────────────
    public function saveProfile(): void
    {
        $user = $this->currentUser();

        $this->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'nullable|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
            'username'  => 'required|string|max:100|unique:users,username,' . $user->user_id . ',user_id',
        ]);

        $old = $user->only(['full_name', 'email', 'username']);

        $user->update([
            'full_name' => $this->full_name,
            'email'     => $this->email ?: null,
            'username'  => $this->username,
        ]);

        logAudit('update', 'User (profile)', $user->user_id, $old, [
            'full_name' => $this->full_name,
            'email'     => $this->email,
            'username'  => $this->username,
        ]);

        $this->activeTab = 'profile';
        session()->flash('success', 'Profile updated successfully.');
    }

    // ── Save avatar ───────────────────────────────────────────────────────────
    public function saveAvatar(): void
    {
        $this->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = $this->currentUser();

        // Delete old avatar file if it exists
        if ($user->avatar && file_exists(storage_path('app/public/avatars/' . $user->avatar))) {
            unlink(storage_path('app/public/avatars/' . $user->avatar));
        }

        $filename = $this->photo->store('avatars', 'public');
        $basename = basename($filename);

        $user->update(['avatar' => $basename]);

        logAudit('update', 'User (avatar)', $user->user_id, ['avatar' => $user->getOriginal('avatar')], ['avatar' => $basename]);

        $this->photo     = null;
        $this->activeTab = 'avatar';
        session()->flash('success', 'Profile picture updated successfully.');
    }

    // ── Remove avatar ─────────────────────────────────────────────────────────
    public function removeAvatar(): void
    {
        $user = $this->currentUser();

        if ($user->avatar && file_exists(storage_path('app/public/avatars/' . $user->avatar))) {
            unlink(storage_path('app/public/avatars/' . $user->avatar));
        }

        $user->update(['avatar' => null]);

        logAudit('update', 'User (avatar)', $user->user_id, null, ['avatar' => null]);

        $this->activeTab = 'avatar';
        session()->flash('success', 'Profile picture removed.');
    }

    // ── Save password ─────────────────────────────────────────────────────────
    public function savePassword(): void
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        $user = $this->currentUser();

        if (! password_verify($this->current_password, $user->password_hash)) {
            $this->addError('current_password', 'The current password is incorrect.');
            return;
        }

        $user->update(['password_hash' => bcrypt($this->new_password)]);

        logAudit('update', 'User (password)', $user->user_id, null, ['password' => '(changed)']);

        $this->current_password          = '';
        $this->new_password              = '';
        $this->new_password_confirmation = '';

        $this->activeTab = 'password';
        session()->flash('success', 'Password changed successfully.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function currentUser(): User
    {
        return User::findOrFail(session('auth_user_id'));
    }

    public function render()
    {
        return view('livewire.settings.settings-form', [
            'user' => $this->currentUser(),
        ])->extends('layouts.app');
    }
}
