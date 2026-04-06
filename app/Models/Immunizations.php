<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Immunizations extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['child_id', 'vaccine_description', 'dose_1', 'dose_2', 'dose_3', 'status', 'remarks'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('immunization')
            ->setDescriptionForEvent(fn (string $e) => "Immunization record was {$e}");
    }

    protected $fillable = [
        'child_id',
        'vaccine_description',
        'dose_1',
        'dose_2',
        'dose_3',
        'status',
        'remarks',
    ];

    protected $casts = [
        'dose_1' => 'date',
        'dose_2' => 'date',
        'dose_3' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->status = ($model->dose_1 && $model->dose_2 && $model->dose_3)
                ? 'complete'
                : 'incomplete';
        });
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(AdoptedChild::class, 'child_id');
    }
}
