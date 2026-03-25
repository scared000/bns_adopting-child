<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    use HasFactory;

    protected $table = 'offices';

    protected $fillable = [
        'department_code',
        'ffunccod',
        'office',
        'short_name',
        'within_capitol',
        'empl_id',
        'designation',
        'location',
        'can_be_multiple_services',
        'assigned_character',
        'queuing_ip_address',
        'window_count',
    ];

    protected $casts = [
        'within_capitol'           => 'boolean',
        'can_be_multiple_services' => 'boolean',
        'window_count'             => 'integer',
    ];

    public function children(): hasMany
    {
        return $this->hasMany(OfficeChildAssign::class, 'office_id');
    }

    public function childVisit(): BelongsTo
    {
        return $this->belongsTo(OfficeChildVisit::class, 'office_assign_id');
    }
}
