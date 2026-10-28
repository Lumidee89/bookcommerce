<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailabilityRule extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean', 'day_of_week' => 'integer'];
}
