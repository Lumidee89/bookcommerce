<?php

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Services\Bookings\BookingService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('bookings:expire-holds', function (BookingService $bookings) {
    $this->info($bookings->expireHolds().' expired hold(s) released.');
})->purpose('Release consultation slots whose payment hold has lapsed');

Artisan::command('bookings:send-reminders', function (BookingService $bookings) {
    $this->info($bookings->sendReminders().' reminder(s) sent.');
})->purpose('Email reminders for consultations starting within 24 hours');

Artisan::command('articles:publish-scheduled', function () {
    $count = Article::where('status', ArticleStatus::Scheduled)->where('published_at', '<=', now())
        ->update(['status' => ArticleStatus::Published]);
    $this->info("{$count} scheduled article(s) published.");
})->purpose('Mark scheduled articles whose publication time has passed as published');

Schedule::command('bookings:expire-holds')->everyMinute()->withoutOverlapping();
Schedule::command('bookings:send-reminders')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('articles:publish-scheduled')->everyFiveMinutes();
Schedule::command('queue:prune-failed --hours=720')->daily();
