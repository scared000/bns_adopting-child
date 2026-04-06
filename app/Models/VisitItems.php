<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VisitItems extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['visit_id', 'Item_description', 'item_quantity', 'item_amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('visit_item')
            ->setDescriptionForEvent(fn (string $e) => "Visit item was {$e}");
    }
    protected $fillable = [
        'visit_id',
        'Item_description',
        'item_quantity',
        'item_amount'
    ];

    public function officeVisit(): BelongsTo
    {
        return $this->belongsTo(OfficeChildVisit::class, 'visit_id');
    }
}
