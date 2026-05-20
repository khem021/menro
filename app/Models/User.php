<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'full_name',
        'email',
        'username',
        'password_hash',
        'avatar',
        'role_id',
        'status',
    ];

    protected $hidden = [
        'password_hash',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function wasteGenerators(): HasMany
    {
        return $this->hasMany(WasteGenerator::class, 'created_by', 'user_id');
    }

    public function wasteEntries(): HasMany
    {
        return $this->hasMany(WasteEntry::class, 'encoded_by', 'user_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'inspector_id', 'user_id');
    }

    public function collectionSchedules(): HasMany
    {
        return $this->hasMany(CollectionSchedule::class, 'created_by', 'user_id');
    }

    public function reportedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'reported_by', 'user_id');
    }

    public function assignedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'assigned_to', 'user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id', 'user_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'generated_by', 'user_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getRoleNameAttribute(): string
    {
        return $this->role->role_name ?? 'Unknown';
    }

    public function isAdmin(): bool
    {
        return $this->role_name === 'System Administrator';
    }

    public function hasRole(string $role): bool
    {
        return $this->role_name === $role;
    }
}
