<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BookCategory extends Model
{
    use HasSlug;

    protected $guarded = ['id'];

    protected function slugSource(): string
    {
        return 'name';
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }
}
