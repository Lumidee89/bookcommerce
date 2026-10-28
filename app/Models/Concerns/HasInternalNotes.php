<?php

namespace App\Models\Concerns;

use App\Models\InternalNote;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasInternalNotes
{
    public function notes(): MorphMany
    {
        return $this->morphMany(InternalNote::class, 'notable')->latest();
    }
}
