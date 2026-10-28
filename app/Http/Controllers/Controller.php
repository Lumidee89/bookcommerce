<?php

namespace App\Http\Controllers;

use App\Services\Shop\CartService;

abstract class Controller
{
    protected function activeCurrency(): string
    {
        return app(CartService::class)->currency();
    }
}
