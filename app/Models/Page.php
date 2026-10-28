<?php

namespace App\Models;

use App\Support\Markdown;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_published' => 'boolean'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function bodyHtml(): string
    {
        return Markdown::render($this->body);
    }
}
