<?php

namespace App\Admin;

class Column
{
    public static function make(string $key, string $label, string $type = 'text'): array
    {
        return ['key' => $key, 'label' => $label, 'type' => $type];
    }

    /** A status pill. Tones: neutral, success, warning, danger, info. */
    public static function badge(string $label, string $tone = 'neutral'): array
    {
        return ['label' => $label, 'tone' => $tone];
    }

    public static function tone(string $status): string
    {
        return match ($status) {
            'paid', 'completed', 'confirmed', 'published', 'approved', 'subscribed', 'success', 'accepted', 'resolved', 'delivered', 'available', 'free' => 'success',
            'pending_payment', 'pending', 'scheduled', 'under_review', 'in_progress', 'awaiting_response', 'follow_up_required', 'processing', 'ready_for_dispatch', 'forthcoming', 'refund_pending' => 'warning',
            'cancelled', 'payment_failed', 'failed', 'rejected', 'declined', 'expired', 'requires_attention', 'unsubscribed', 'refunded', 'no_show' => 'danger',
            'new', 'shipped' => 'info',
            default => 'neutral',
        };
    }
}
