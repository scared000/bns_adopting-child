<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaranggayNutritionScholars extends Model
{
    use HasFactory;

    protected $table = 'barangay_nutrition_scholars';
    protected $fillable = [
        'firstname',
        'lastname',
        'barangay_name',
        'barangay_code',
        'middlename',
        'suffix',
        'profile_path',
        'barangay_id',
        'purok',
        'municipality_id',
    ];

    public function officeVisits(): HasMany
    {
        return $this->hasMany(OfficeChildVisit::class, 'bns_id');
    }

    public function childAssignments(): HasMany
    {
        return $this->hasMany(OfficeChildAssign::class, 'bns_id');
    }
}
