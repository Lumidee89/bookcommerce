<?php

namespace App\Enums;

enum InvitationStatus: string
{
    use HasOptions;

    case New = 'new';
    case UnderReview = 'under_review';
    case FollowUpRequired = 'follow_up_required';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Completed = 'completed';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::UnderReview => 'Under review',
            self::FollowUpRequired => 'Follow-up required',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
            self::Completed => 'Completed',
            self::Closed => 'Closed',
        };
    }
}
