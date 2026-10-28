<?php

use App\Http\Controllers\Checkout\CartController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Checkout\DownloadController;
use App\Http\Controllers\Checkout\OrderController;
use App\Http\Controllers\Checkout\PaymentController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\BookController;
use App\Http\Controllers\Public\BookingController;
use App\Http\Controllers\Public\ConsultationController;
use App\Http\Controllers\Public\EnquiryController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsletterController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('about', [PageController::class, 'about'])->name('about');
Route::get('pages/{page}', [PageController::class, 'show'])->name('pages.show');
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

// Bookstore and checkout
Route::get('books', [BookController::class, 'index'])->name('books.index');
Route::get('books/{book}', [BookController::class, 'show'])->name('books.show');

Route::get('cart', [CartController::class, 'show'])->name('cart.show');
Route::post('cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('cart/{formatId}', [CartController::class, 'update'])->whereNumber('formatId')->name('cart.update');
Route::delete('cart/{formatId}', [CartController::class, 'destroy'])->whereNumber('formatId')->name('cart.destroy');
Route::post('currency', [CartController::class, 'currency'])->name('currency.update');

Route::get('checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');
Route::get('orders/{order}', [OrderController::class, 'show'])->middleware('signed')->name('orders.show');
Route::post('orders/{order}/pay', [CheckoutController::class, 'retry'])->middleware('throttle:checkout')->name('checkout.retry');

Route::get('downloads/{token}', [DownloadController::class, 'show'])->middleware('throttle:downloads')->name('downloads.show');
Route::post('downloads/{token}/renew', [DownloadController::class, 'requestRenewal'])->middleware('throttle:forms')->name('downloads.renew');

// Payments
Route::get('payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');
Route::post('payments/webhook/paystack', [PaymentController::class, 'webhook'])->name('payments.webhook');
Route::get('payments/sandbox/{transaction:reference}', [PaymentController::class, 'sandbox'])->name('payments.sandbox');
Route::post('payments/sandbox/{transaction:reference}', [PaymentController::class, 'simulate'])->name('payments.sandbox.simulate');

// Consultations
Route::get('consultations', [ConsultationController::class, 'index'])->name('consultations.index');
Route::get('consultations/{service}', [ConsultationController::class, 'show'])->name('consultations.show');
Route::get('consultations/{service}/dates', [ConsultationController::class, 'dates'])->middleware('throttle:60,1')->name('consultations.dates');
Route::get('consultations/{service}/slots', [ConsultationController::class, 'slots'])->middleware('throttle:60,1')->name('consultations.slots');
Route::post('consultations/{service}/book', [ConsultationController::class, 'book'])->middleware('throttle:forms')->name('consultations.book');

Route::get('bookings/{booking}', [BookingController::class, 'manage'])->middleware('signed')->name('bookings.manage');
Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->middleware('throttle:forms')->name('bookings.cancel');
Route::post('bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->middleware('throttle:forms')->name('bookings.reschedule');
Route::post('bookings/{booking}/pay', [BookingController::class, 'pay'])->middleware('throttle:checkout')->name('bookings.pay');

// Content
Route::get('articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('articles/{article}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('videos', [VideoController::class, 'index'])->name('videos.index');
Route::get('videos/{video}', [VideoController::class, 'show'])->name('videos.show');
Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('gallery/{album}', [GalleryController::class, 'show'])->name('gallery.show');
Route::get('events', [EventController::class, 'index'])->name('events.index');
Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');

// Enquiries
Route::get('speaking', [EnquiryController::class, 'speaking'])->name('speaking');
Route::post('speaking', [EnquiryController::class, 'storeSpeaking'])->middleware('throttle:forms')->name('speaking.store');
Route::get('contact', [EnquiryController::class, 'contact'])->name('contact');
Route::post('contact', [EnquiryController::class, 'storeContact'])->middleware('throttle:forms')->name('contact.store');

// Newsletter
Route::post('newsletter', [NewsletterController::class, 'store'])->middleware('throttle:forms')->name('newsletter.store');
Route::get('newsletter/confirm/{token}', [NewsletterController::class, 'confirm'])->middleware('signed')->name('newsletter.confirm');
Route::get('newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribeForm'])->name('newsletter.unsubscribe');
Route::post('newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe.store');

Route::redirect('dashboard', 'admin')->name('dashboard');

require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
