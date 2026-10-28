<?php

namespace App\Http\Middleware;

use App\Models\NewsletterInterest;
use App\Models\Setting;
use App\Services\Payments\PaymentGateway;
use App\Services\Shop\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->hasRole('owner') ? ['*'] : $user->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            'site' => fn () => [
                'tagline' => Setting::get('site.tagline'),
                'supporting_line' => Setting::get('site.supporting_line', 'Powered by Jolax Global Leadership Center'),
                'contact' => [
                    'email' => Setting::get('contact.email'),
                    'phone' => Setting::get('contact.phone'),
                    'address' => Setting::get('contact.address'),
                ],
                'social' => array_filter(Setting::get('social', [])),
            ],
            'cart' => fn () => [
                'count' => app(CartService::class)->count(),
                'currency' => app(CartService::class)->currency(),
            ],
            'currencies' => config('platform.currencies'),
            'newsletterInterests' => fn () => NewsletterInterest::orderBy('sort_order')->get(['id', 'name']),
            'payments' => fn () => [
                'enabled' => app(PaymentGateway::class)->isConfigured(),
                'sandbox' => app(PaymentGateway::class)->name() === 'test',
            ],
            'analytics' => ['ga' => config('platform.analytics.ga_measurement_id')],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
