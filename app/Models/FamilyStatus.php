<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'status',
        'type_of_marraige',
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
