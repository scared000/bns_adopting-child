<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdoptedChild extends Model
{
    use HasFactory;

    protected $fillable = [
        'firstname',
        'lastname',
        'middlename',
        'suffix',
        'profile_path',
        'birthdate',
        'Birthplace',
        'nutritional_status',
        'lcr_registered',
        'breastfed',
        'v_suplemented',
        'barangay_id',
        'actual_weight',
        'actual_height'
    ];

    public function familyProfiles(): HasMany
    {
        return $this->hasMany(FamilyProfile::class, 'child_id');
    }

    // Assuming one status record per child, use HasMany if a child can have a history of statuses
    public function familyStatus(): HasOne
    {
        return $this->hasOne(FamilyStatus::class, 'child_id');
    }

    public function immunizations(): HasMany
    {
        return $this->hasMany(Immunizations::class, 'child_id');
    }

    public function officeAssignments(): HasMany
    {
        return $this->hasMany(OfficeChildAssign::class, 'adopted_id');
    }
}
