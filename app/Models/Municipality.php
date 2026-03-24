<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Municipality extends Model
{
    protected $fillable = [
        'psgcCode',
        'citymunDesc',
        'regDesc',
        'provCode',
        'citymunCode'
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'provCode', 'provCode');
    }

    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class, 'citymunCode', 'citymunCode');
    }
}
