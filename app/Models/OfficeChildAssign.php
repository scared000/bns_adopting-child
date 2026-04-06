<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OfficeChildAssign extends Model
{
    use LogsActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['adopted_id', 'bns_id', 'office_id', 'assigned_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('child_assignment')
            ->setDescriptionForEvent(fn (string $e) => "Child assignment was {$e}");
    }

    protected $fillable = [
        'adopted_id',
        'bns_id',
        'office_id',
        'assigned_date',
    ];
    protected $casts = [
        'assigned_date' => 'date',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(AdoptedChild::class, 'adopted_id');
    }

    public function bns(): BelongsTo
    {
        return $this->belongsTo(BaranggayNutritionScholars::class, 'bns_id');
    }
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id','brgyCode');
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'municipality_id','citymunCode');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
    public function visits(): HasMany
    {
        return $this->hasMany(OfficeChildVisit::class, 'office_assign_id');
    }
}
