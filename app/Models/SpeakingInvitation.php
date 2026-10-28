<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use App\Models\Concerns\HasInternalNotes;
use Illuminate\Database\Eloquent\Model;

class SpeakingInvitation extends Model
{
    use HasInternalNotes;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => InvitationStatus::class,
        'proposed_date' => 'date',
    ];
}
