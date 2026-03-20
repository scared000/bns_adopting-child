<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhoGrowthStandard extends Model
{
    protected $fillable = ['indicator', 'sex', 'key_value', 'l', 'm', 's'];
}
