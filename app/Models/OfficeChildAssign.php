<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeChildAssign extends Model
{
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
}
