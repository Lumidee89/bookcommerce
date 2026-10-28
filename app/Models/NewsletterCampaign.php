<?php

namespace App\Models;

use App\Enums\SubscriberStatus;
use App\Support\Markdown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsletterCampaign extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['interest_ids' => 'array', 'sent_at' => 'datetime'];

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Only confirmed, still-subscribed contacts ever receive a campaign (BR-013, BR-014). */
    public function audience(): Builder
    {
        return NewsletterSubscriber::query()
            ->where('status', SubscriberStatus::Subscribed)
            ->when($this->interest_ids, fn ($q, $ids) => $q->whereHas('interests', fn ($q) => $q->whereIn('newsletter_interests.id', $ids)));
    }

    public function bodyHtml(): string
    {
        return Markdown::render($this->body);
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }
}
