<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'email',
    'password',
    'firstname',
    'lastname',
    'barangay_name',
    'barangay_code',
    'middlename',
    'suffix',
    'profile_path',
    'barangay_id',
    'purok',
    'municipality_id',
    'office_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasName
{
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    public function getFilamentName(): string
    {
        return trim("{$this->firstname} {$this->lastname}") ?: $this->email;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['firstname', 'lastname', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "User was {$eventName}");
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function canImpersonate(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function canBeImpersonated(): bool
    {
        return !$this->hasRole('super_admin');
    }

    public function getImpersonateRedirectTo(): string
    {
        if ($this->hasAnyRole('super_admin', 'admin')) {
            return route('filament.admin.pages.dashboard');
        }

        return route('filament.admin.auth.profile');
    }

    public function bnsRecord(): HasOne
    {
        return $this->hasOne(BaranggayNutritionScholars::class, 'user_id');
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'municipality_id', 'citymunCode');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'brgyCode');
    }
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function bnsProfile(): HasOne
    {
        return $this->hasOne(BnsProfile::class);
    }

    public function isBns(): bool
    {
        return $this->hasRole('bns');
    }
}
