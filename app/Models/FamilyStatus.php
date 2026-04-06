<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FamilyStatus extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'monthly_income', 'source_income', 'water_source', 'toilet_facility'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('family_status')
            ->setDescriptionForEvent(fn (string $e) => "Family status was {$e}");
    }

    protected $fillable = [
        'child_id',
        'status',
        'type_of_marriage',
        'monthly_income',
        'source_income',
        'phil_member',
        'family_plan_method',
        'have_electricity',
        'water_source',
        'toilet_facility',
        'roofing',
        'walls',
        'flooring'
    ];

    public function adoptedChild(): BelongsTo
    {
        return $this->belongsTo(AdoptedChild::class, 'child_id');
    }
}
