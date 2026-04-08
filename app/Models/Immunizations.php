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
            ->logOnly([
                'child_id', 'vaccine_description',
                'dose_1', 'dose_2', 'dose_3', 'dose_4', 'dose_5',
                'status', 'remarks'
            ])
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
        'dose_4',
        'dose_5',
        'total_doses',
        'status',
        'remarks',
    ];

    protected $casts = [
        'dose_1' => 'date',
        'dose_2' => 'date',
        'dose_3' => 'date',
        'dose_4' => 'date',
        'dose_5' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $maxRequired = $model->total_doses ?? 3;
            $isComplete = true;

            for ($i = 1; $i <= $maxRequired; $i++) {
                $value = $model->{"dose_$i"};
                if (is_null($value) || trim((string)$value) === '') {
                    $isComplete = false;
                    break;
                }
            }
            $model->status = $isComplete ? 'complete' : 'incomplete';
        });
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(AdoptedChild::class, 'child_id');
    }
}
