<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as Notifier;

class AdminRecipient
{
    public static function email(): ?string
    {
        return config('platform.admin_notification_email') ?: Setting::get('contact.email');
    }

    public static function notify(Notification $notification): void
    {
        if ($email = self::email()) {
            Notifier::route('mail', $email)->notify($notification);
        }
    }
}
