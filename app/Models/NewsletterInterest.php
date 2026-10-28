<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NewsletterInterest extends Model
{
    use HasSlug;

    protected $guarded = ['id'];

    protected function slugSource(): string
    {
        return 'name';
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(NewsletterSubscriber::class, 'newsletter_interest_subscriber');
    }
}
