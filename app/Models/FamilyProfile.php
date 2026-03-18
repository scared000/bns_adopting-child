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
        'firstname',
        'lastname',
        'middlename',
        'suffix',
        'relationship',
        'birthdate',
        'highest_grade',
        'occupation',
        'actual_weight',
        'nutrietion_status'
    ];

    public function adoptedChild(): BelongsTo
    {
        return $this->belongsTo(AdoptedChild::class, 'child_id');
    }
}
