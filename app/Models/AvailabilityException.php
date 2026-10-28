<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailabilityException extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['date' => 'date'];

    public function blocksWholeDay(): bool
    {
        return $this->start_time === null || $this->end_time === null;
    }
}
