<?php

namespace App\Http\Livewire\Notifications;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public int $perPage = 20;

    // Send notification form (admin/menro only)
    public bool $showForm = false;
    public string $form_title   = '';
    public string $form_message = '';
    public string $form_type    = 'info';
    public string $form_target  = 'all'; // all | role | user
    public string $form_role    = '';
    public string $form_user_id = '';

    protected $rules = [
        'form_title'   => 'required|string|max:200',
        'form_message' => 'required|string',
        'form_type'    => 'required|in:info,warning,danger,success',
        'form_target'  => 'required|in:all,role,user',
        'form_role'    => 'nullable|string',
        'form_user_id' => 'nullable|integer',
    ];

    private function bustUnreadCache(): void
    {
        Cache::forget('nav:unread:' . session('auth_user_id'));
    }

    public function markRead($id)
    {
        Notification::where('notification_id', $id)
            ->where('user_id', session('auth_user_id'))
            ->update(['is_read' => true, 'read_at' => now()]);
        $this->bustUnreadCache();
    }

    public function markAllRead()
    {
        Notification::where('user_id', session('auth_user_id'))
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        $this->bustUnreadCache();
        session()->flash('success', 'All notifications marked as read.');
    }

    public function deleteNotification($id)
    {
        Notification::where('notification_id', $id)
            ->where('user_id', session('auth_user_id'))
            ->delete();
        $this->bustUnreadCache();
    }

    public function deleteAll()
    {
        Notification::where('user_id', session('auth_user_id'))->delete();
        $this->bustUnreadCache();
        session()->flash('success', 'All notifications deleted.');
    }

    public function sendNotification()
    {
        $this->validate();

        $userIds = match ($this->form_target) {
            'role'  => User::whereHas('role', fn($q) => $q->where('role_name', $this->form_role))
                           ->where('status', 'active')
                           ->pluck('user_id'),
            'user'  => collect([$this->form_user_id]),
            default => User::where('status', 'active')->pluck('user_id'),
        };

        $rows = $userIds->map(fn($uid) => [
            'user_id'    => $uid,
            'title'      => $this->form_title,
            'message'    => $this->form_message,
            'type'       => $this->form_type,
            'is_read'    => false,
            'read_at'    => null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all();

        Notification::insert($rows);

        // Bust nav unread count cache for all affected users
        foreach ($userIds as $uid) {
            Cache::forget('nav:unread:' . $uid);
        }

        $this->reset(['form_title', 'form_message', 'form_type', 'form_target', 'form_role', 'form_user_id', 'showForm']);
        session()->flash('success', 'Notification sent to ' . count($rows) . ' user(s).');
    }

    public function render()
    {
        $userId = session('auth_user_id');

        $notifications = Notification::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        $unreadCount = Cache::remember('nav:unread:' . $userId, 60, fn() =>
            Notification::where('user_id', $userId)->where('is_read', false)->count()
        );

        $roles = Cache::remember('lookup:roles', 600, fn() =>
            \App\Models\Role::orderBy('role_name')->pluck('role_name')
        );
        $users = Cache::remember('lookup:users_notif', 300, fn() =>
            User::orderBy('full_name')->get(['user_id', 'full_name', 'username'])
        );

        return view('livewire.notifications.notification-index',
            compact('notifications', 'unreadCount', 'roles', 'users'))
            ->extends('layouts.app');
    }
}
