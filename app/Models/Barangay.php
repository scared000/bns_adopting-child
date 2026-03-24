<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barangay extends Model
{
    protected $fillable = [
        'brgyCode',
        'brgyDesc',
        'regCode',
        'provCode',
        'citymunCode'
    ];

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'citymunCode', 'citymunCode');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'provCode', 'provCode');
    }
}
