<?php

namespace App\Models;

use App\Enums\EnquiryCategory;
use App\Enums\EnquiryStatus;
use App\Models\Concerns\HasInternalNotes;
use Illuminate\Database\Eloquent\Model;

class ContactEnquiry extends Model
{
    use HasInternalNotes;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => EnquiryStatus::class,
        'category' => EnquiryCategory::class,
    ];
}
