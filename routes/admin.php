<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

/*
| Every admin route is authorised on the server by permission (BR-015, BR-016).
| The owner role passes all checks via Gate::before.
*/

$resource = function (string $name, string $controller) {
    Route::resource($name, $controller)->except('show')->parameters([$name => 'record']);
};

Route::middleware(['auth', 'verified', 'permission:access admin'])->prefix('admin')->name('admin.')->group(function () use ($resource) {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::middleware('permission:manage content')->group(function () use ($resource) {
        $resource('pages', Admin\PageController::class);
        $resource('profile-sections', Admin\ProfileSectionController::class);
        $resource('speaker-documents', Admin\SpeakerDocumentController::class);
    });

    Route::middleware('permission:manage articles')->group(function () use ($resource) {
        $resource('articles', Admin\ArticleController::class);
        $resource('article-categories', Admin\ArticleCategoryController::class);
    });

    Route::middleware('permission:manage media')->group(function () use ($resource) {
        $resource('videos', Admin\VideoController::class);
        $resource('video-categories', Admin\VideoCategoryController::class);
        $resource('albums', Admin\AlbumController::class);
        Route::post('albums/{album}/photos', [Admin\PhotoController::class, 'store'])->name('albums.photos.store');
        Route::put('photos/{photo}', [Admin\PhotoController::class, 'update'])->name('photos.update');
        Route::delete('photos/{photo}', [Admin\PhotoController::class, 'destroy'])->name('photos.destroy');
    });

    Route::middleware('permission:manage events')->group(fn () => $resource('events', Admin\EventController::class));

    Route::middleware('permission:manage profile')->group(function () use ($resource) {
        $resource('awards', Admin\AwardController::class);
        $resource('testimonials', Admin\TestimonialController::class);
    });

    Route::middleware('permission:manage books')->group(function () use ($resource) {
        $resource('books', Admin\BookController::class);
        $resource('book-categories', Admin\BookCategoryController::class);
        Route::post('books/{book:id}/formats', [Admin\BookFormatController::class, 'store'])->name('books.formats.store');
        Route::put('formats/{format}', [Admin\BookFormatController::class, 'update'])->name('formats.update');
        Route::delete('formats/{format}', [Admin\BookFormatController::class, 'destroy'])->name('formats.destroy');
    });

    Route::post('formats/{format}/stock', [Admin\BookFormatController::class, 'adjustStock'])->middleware('permission:manage inventory')->name('formats.stock');

    Route::middleware('permission:manage orders')->group(function () use ($resource) {
        Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
        Route::put('orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');
        Route::put('orders/{order}/shipping', [Admin\OrderController::class, 'updateShipping'])->name('orders.shipping');
        Route::post('orders/{order}/resend-receipt', [Admin\OrderController::class, 'resendReceipt'])->name('orders.resend');
        Route::post('downloads/{download}/renew', [Admin\OrderController::class, 'renewDownload'])->name('downloads.renew');
        Route::post('downloads/{download}/revoke', [Admin\OrderController::class, 'revokeDownload'])->name('downloads.revoke');
        $resource('delivery-options', Admin\DeliveryOptionController::class);
    });

    Route::post('orders/{order}/refunds', [Admin\OrderController::class, 'refund'])->middleware('permission:manage refunds')->name('orders.refund');

    Route::middleware('permission:manage consultations')->group(function () use ($resource) {
        $resource('services', Admin\ConsultationServiceController::class);
        Route::get('availability', [Admin\AvailabilityController::class, 'edit'])->name('availability.edit');
        Route::put('availability', [Admin\AvailabilityController::class, 'updateRules'])->name('availability.update');
        Route::post('availability/exceptions', [Admin\AvailabilityController::class, 'storeException'])->name('availability.exceptions.store');
        Route::delete('availability/exceptions/{exception}', [Admin\AvailabilityController::class, 'destroyException'])->name('availability.exceptions.destroy');
    });

    Route::middleware('permission:manage bookings')->group(function () {
        Route::get('bookings', [Admin\BookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{booking}', [Admin\BookingController::class, 'show'])->name('bookings.show');
        Route::put('bookings/{booking}/meeting', [Admin\BookingController::class, 'updateMeeting'])->name('bookings.meeting');
        Route::put('bookings/{booking}/status', [Admin\BookingController::class, 'updateStatus'])->name('bookings.status');
        Route::put('bookings/{booking}/reschedule', [Admin\BookingController::class, 'reschedule'])->name('bookings.reschedule');
    });

    Route::middleware('permission:manage enquiries')->group(function () {
        foreach (['enquiries' => Admin\EnquiryController::class, 'invitations' => Admin\InvitationController::class] as $name => $controller) {
            Route::get($name, [$controller, 'index'])->name("{$name}.index");
            Route::get("{$name}/{record}", [$controller, 'show'])->name("{$name}.show");
            Route::put("{$name}/{record}/status", [$controller, 'updateStatus'])->name("{$name}.status");
            Route::delete("{$name}/{record}", [$controller, 'destroy'])->name("{$name}.destroy");
        }
    });

    Route::post('notes', [Admin\NoteController::class, 'store'])->name('notes.store');

    Route::middleware('permission:manage subscribers')->group(function () use ($resource) {
        Route::get('subscribers/export', [Admin\SubscriberController::class, 'export'])->name('subscribers.export');
        Route::get('subscribers', [Admin\SubscriberController::class, 'index'])->name('subscribers.index');
        Route::delete('subscribers/{record}', [Admin\SubscriberController::class, 'destroy'])->name('subscribers.destroy');
        $resource('newsletter-interests', Admin\NewsletterInterestController::class);
        $resource('newsletters', Admin\NewsletterCampaignController::class);
        Route::post('newsletters/{record}/test', [Admin\NewsletterCampaignController::class, 'sendTest'])->middleware('throttle:10,1')->name('newsletters.test');
        Route::post('newsletters/{record}/send', [Admin\NewsletterCampaignController::class, 'send'])->name('newsletters.send');
    });

    Route::middleware('permission:manage settings')->group(function () {
        Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware('permission:manage users')->group(fn () => $resource('users', Admin\UserController::class));

    Route::get('audit-logs', [Admin\AuditLogController::class, 'index'])->middleware('permission:view audit logs')->name('audit-logs.index');
});
