<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'Item_description',
        'item_quantity',
        'item_amount'
    ];

    public function officeVisit(): BelongsTo
    {
        return $this->belongsTo(OfficeChildVisit::class, 'visit_id');
    }
}
