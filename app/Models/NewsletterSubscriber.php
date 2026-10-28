<?php

namespace App\Models;

use App\Enums\SubscriberStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsletterSubscriber extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['token'];

    protected $casts = [
        'status' => SubscriberStatus::class,
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'provider_synced_at' => 'datetime',
    ];

    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(NewsletterInterest::class, 'newsletter_interest_subscriber');
    }

    public function consents(): HasMany
    {
        return $this->hasMany(SubscriptionConsent::class)->latest('id');
    }

    public function unsubscribeUrl(): string
    {
        return route('newsletter.unsubscribe', $this->token);
    }
}
