<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoCategory extends Model
{
    use HasSlug;

    protected $guarded = ['id'];

    protected function slugSource(): string
    {
        return 'name';
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
}
