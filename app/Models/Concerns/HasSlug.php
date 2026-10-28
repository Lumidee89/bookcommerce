<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            if (blank($model->slug)) {
                $model->slug = $model->uniqueSlug(Str::slug($model->{$model->slugSource()}) ?: Str::random(8));
            }
        });
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    protected function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
