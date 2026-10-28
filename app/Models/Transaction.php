<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => TransactionStatus::class,
        'gateway_response' => 'array',
        'verified_at' => 'datetime',
        'amount' => 'integer',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
