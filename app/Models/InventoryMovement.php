<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $guarded = ['id'];

    public function format(): BelongsTo
    {
        return $this->belongsTo(BookFormat::class, 'book_format_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
