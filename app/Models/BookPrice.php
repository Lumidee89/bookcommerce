<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookPrice extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['amount' => 'integer'];

    public function format(): BelongsTo
    {
        return $this->belongsTo(BookFormat::class, 'book_format_id');
    }
}
