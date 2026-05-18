<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BnsTraining extends Model
{
    protected $table = 'bns_trainings';

    protected $fillable = [
        'bns_profile_id',
        'title',
        'date_attended',
        'conducted_by',
        'venue',
        'duration_hours',
        'certificate',
        'remarks',
    ];

    protected $casts = [
        'date_attended' => 'date',
        'certificate'   => 'array',
    ];

    public function bnsProfile(): BelongsTo
    {
        return $this->belongsTo(BnsProfile::class);
    }

    /** Common BNS training titles for pre-fill suggestions */
    public static function commonTitles(): array
    {
        return [
            'Basic Course for BNS',
            'Nutrition in Emergencies',
            'Operation Timbang (OPT) Plus',
            'PIMAM Training',
            'IYCF Training',
            'Supplementary Feeding Program',
            'Nutrition Counseling',
        ];
    }
}
