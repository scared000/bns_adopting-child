<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
