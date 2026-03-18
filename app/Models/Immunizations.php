<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Immunizations extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'description',
        'first_dose',
        'second_dose',
        'third_dose'
    ];

    public function adoptedChild(): BelongsTo
    {
        return $this->belongsTo(AdoptedChild::class, 'child_id');
    }
}
