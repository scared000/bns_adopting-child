<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barangay extends Model
{
    protected $primaryKey = 'brgyCode';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'brgyCode',
        'brgyDesc',
        'regCode',
        'provCode',
        'citymunCode'
    ];

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class,  'citymunCode', 'citymunCode');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'provCode', 'provCode');
    }

    public function adoptedChildren(): HasMany
    {
        return $this->hasMany(AdoptedChild::class,'barangay_id', 'brgyDesc');
    }

    public function bns(): HasMany
    {
        return $this->hasMany(BaranggayNutritionScholars::class, 'barangay_id', 'brgyDesc');
    }

    public function childAsignments(): HasMany
    {
        return $this->hasMany(OfficeChildAssign::class, 'barangay_id', 'brgyDesc');
    }
}
