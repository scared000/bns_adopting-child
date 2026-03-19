<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'type',
        'firstname',
        'lastname',
        'middlename',
        'suffix',
        'relation',
        'birthdate',
        'educational_attainment',
        'occupation',
        'fam_member_fullname',
        'fam_member_actual_weight',
        'fam_member_nutrition_status'
    ];

    public function adoptedChild(): BelongsTo
    {
        return $this->belongsTo(AdoptedChild::class, 'child_id');
    }
}
