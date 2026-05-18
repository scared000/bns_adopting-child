<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BnsProfile extends Model
{
    use SoftDeletes;

    protected $table = 'bns_profiles';

    protected $fillable = [
        // Personal
        'user_id',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'municipality_id',
        'barangay_id',
        'date_of_birth',
        'place_of_birth',
        'sex',
        'civil_status',
        'educational_attainment',
        'contact_number',
        'email_or_facebook',
        'home_address',
        // Service
        'date_started',
        'years_of_service',
        'bns_id_number',
        'monthly_honorarium',
        // Documents
        'pds_document',
        'appointment_order',
        'oath_of_office',
        'certificate_of_training',
        'psa_birth_certificate',
        'diploma_or_tor',
        'service_record',
        // Meta
        'status',
    ];

    protected $casts = [
        'date_of_birth'           => 'date',
        'date_started'            => 'date',
        'monthly_honorarium'      => 'decimal:2',
        'pds_document'            => 'array',
        'appointment_order'       => 'array',
        'oath_of_office'          => 'array',
        'certificate_of_training' => 'array',
        'psa_birth_certificate'   => 'array',
        'diploma_or_tor'          => 'array',
        'service_record'          => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(BnsTraining::class);
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->last_name}, {$this->first_name} {$this->middle_name}")
        );
    }

    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date_of_birth?->age
        );
    }
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'municipality_id', 'citymunCode');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'brgyCode');
    }


    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }


    public static function educationalAttainmentOptions(): array
    {
        return [
            'elementary_graduate'  => 'Elementary Graduate',
            'high_school_level'    => 'High School Level',
            'high_school_graduate' => 'High School Graduate',
            'college_level'        => 'College Level',
            'college_graduate'     => 'College Graduate',
            'vocational'           => 'Vocational',
            'post_graduate'        => 'Post Graduate',
        ];
    }
}
