<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfficeChildVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_assign_id',
        'office_id',
        'adopted_id',
        'bns_id',
        'visit_date',
        'visit_address',
        'visit_documentation',
        'height',
        'weight',
        'status'
    ];

    protected $casts = [
        'visit_documentation' => 'array',
        'visit_date' => 'date',
    ];

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['child', 'bns', 'office', 'visitItems']);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(AdoptedChild::class, 'adopted_id');
    }

    public function officeAssignment(): BelongsTo
    {
        return $this->belongsTo(OfficeChildAssign::class, 'office_assign_id');
    }

    public function bns(): BelongsTo
    {
        return $this->belongsTo(BaranggayNutritionScholars::class, 'bns_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
    public function visitItems(): HasMany
    {
        return $this->hasMany(VisitItems::class, 'visit_id');
    }
}
