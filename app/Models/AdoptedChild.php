<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdoptedChild extends Model
{
    use HasFactory;
    protected $appends = ['age'];

    protected $fillable = [
        'firstname',
        'lastname',
        'middlename',
        'suffix',
        'profile_path',
        'birthdate',
        'sex',
        'age_months',
        'height_cm',
        'weight_kg',
        'birthplace',
        'nutritional_status',
        'lcr_registered',
        'breastfed',
        'v_suplemented',
        'barangay_id',
        'purok',
        'municipality_id',
    ];


    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->birthdate
                ? Carbon::parse($this->birthdate)->age
                : null,
        );
    }

    public function familyProfiles(): HasMany
    {
        return $this->hasMany(FamilyProfile::class, 'child_id');
    }

    // Assuming one status record per child, use HasMany if a child can have a history of statuses
    public function familyStatus(): HasMany
    {
        return $this->hasMany(FamilyStatus::class, 'child_id');
    }

    public function immunizations(): HasMany
    {
        return $this->hasMany(Immunizations::class, 'child_id');
    }

    public function officeAssignments(): HasMany
    {
        return $this->hasMany(OfficeChildAssign::class, 'adopted_id');
    }

    public function motherProfile(): HasOne
    {
        return $this->hasOne(FamilyProfile::class, 'child_id')
            ->where('type', 'mother');
    }

    public function fatherProfile(): HasOne
    {
        return $this->hasOne(FamilyProfile::class, 'child_id')
            ->where('type', 'father');
    }
    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyProfile::class, 'child_id')
            ->where('type', 'fam_member');
    }

    public function childVisit(): HasMany
    {
        return $this->hasMany(OfficeChildVisit::class, 'adopted_id');
    }
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id','brgyCode');
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'municipality_id','citymunCode');
    }
}
