<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfficeChildVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_assign_id',
        'bns_id',
        'visit_date',
        'visit_address',
        'visit_documentation',
        'height',
        'weight',
        'status'
    ];

    protected $casts = [
        'visit_documentation' => 'array', // Automatically casts the JSON column to an array
    ];

    public function officeAssignment(): BelongsTo
    {
        return $this->belongsTo(OfficeChildAssign::class, 'officeasign_id');
    }

    public function barangayNutritionScholar(): BelongsTo
    {
        return $this->belongsTo(BarangayNutritionScholar::class, 'bns_id');
    }

    public function visitItems(): HasMany
    {
        return $this->hasMany(VisitItem::class, 'visit_id');
    }
}
