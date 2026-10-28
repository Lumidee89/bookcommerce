<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsletterRequest;
use App\Models\NewsletterSubscriber;
use App\Services\Newsletter\NewsletterService;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewsletterController extends Controller
{
    public function __construct(private readonly NewsletterService $newsletter) {}

    public function store(NewsletterRequest $request): RedirectResponse
    {
        $this->newsletter->subscribe($request->validated('email'), $request->validated('name'), $request->validated('interests') ?? [], $request);

        return back()->with('success', 'Almost done — please check your inbox and confirm your subscription.');
    }

    public function confirm(Request $request, string $token): Response
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();
        $this->newsletter->confirm($subscriber, $request);

        return Inertia::render('public/newsletter-status', ['status' => 'confirmed', 'seo' => Seo::make('Subscription confirmed')]);
    }

    public function unsubscribeForm(string $token): Response
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();

        return Inertia::render('public/newsletter-status', [
            'status' => $subscriber->status->value === 'unsubscribed' ? 'unsubscribed' : 'confirm-unsubscribe',
            'token' => $token,
            'seo' => Seo::make('Unsubscribe'),
        ]);
    }

    public function unsubscribe(Request $request, string $token): RedirectResponse
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();
        $this->newsletter->unsubscribe($subscriber, $request);

        return back()->with('success', 'You have been unsubscribed and will no longer receive marketing emails.');
    }
}
