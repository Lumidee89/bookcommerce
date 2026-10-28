<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\ContactEnquiry;
use App\Models\Order;
use App\Models\SpeakingInvitation;
use App\Models\User;
use App\Services\Newsletter\MailchimpProvider;
use App\Services\Newsletter\NewsletterProvider;
use App\Services\Newsletter\NullNewsletterProvider;
use App\Services\Payments\NullGateway;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaystackGateway;
use App\Services\Payments\TestGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function ($app) {
            return match (config('platform.payments.gateway')) {
                'paystack' => new PaystackGateway(config('platform.payments.paystack')),
                // The sandbox can never be used in production, whatever the .env says.
                'test' => $app->environment('production') ? new NullGateway : new TestGateway,
                default => new NullGateway,
            };
        });

        $this->app->singleton(NewsletterProvider::class, function () {
            return match (config('platform.newsletter.provider')) {
                'mailchimp' => new MailchimpProvider(config('platform.newsletter.mailchimp')),
                default => new NullNewsletterProvider,
            };
        });
    }

    public function boot(): void
    {
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        // Short, stable type names for polymorphic columns (transactions, notes, roles).
        Relation::morphMap([
            'user' => User::class,
            'order' => Order::class,
            'booking' => Booking::class,
            'enquiry' => ContactEnquiry::class,
            'invitation' => SpeakingInvitation::class,
        ]);

        // The owner role holds every permission, including ones added later.
        Gate::before(fn (User $user) => $user->hasRole('owner') ? true : null);

        RateLimiter::for('forms', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('downloads', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
    }
}
