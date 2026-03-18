<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfficeChildAssign extends Model
{
    use HasFactory;

    protected $fillable = [
        'adopted_id',
        'office_id',
        'Assigned_date'
    ];

    public function adoptedChild(): BelongsTo
    {
        return $this->belongsTo(AdoptedChild::class, 'adopted_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(OfficeChildVisit::class, 'officeasign_id');
    }
}
