<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_digital' => 'boolean'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function format(): BelongsTo
    {
        return $this->belongsTo(BookFormat::class, 'book_format_id');
    }

    public function download(): HasOne
    {
        return $this->hasOne(DigitalDownload::class);
    }
}
