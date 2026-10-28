<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\BookFormat;
use App\Services\Shop\CartService;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function show(): Response
    {
        return Inertia::render('checkout/cart', [
            'summary' => $this->cart->summary()->toArray(),
            'seo' => Seo::make('Your cart'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'format_id' => ['required', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $format = BookFormat::with(['book', 'prices'])->where('is_active', true)->findOrFail($data['format_id']);

        if (! $format->book->isPurchasable()) {
            return back()->with('error', 'This book is not available for purchase.');
        }
        if ($format->book->formatPrice($format, $this->cart->currency()) === null) {
            return back()->with('error', "This format is not sold in {$this->cart->currency()}. Try switching currency.");
        }
        if (! $format->inStock($data['quantity'] ?? 1)) {
            return back()->with('error', 'Sorry, this format is out of stock.');
        }

        $this->cart->add($format, $format->is_digital ? 1 : ($data['quantity'] ?? 1));

        return back()->with('success', "“{$format->book->title}” added to your cart.");
    }

    public function update(Request $request, int $formatId): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:50']]);
        $this->cart->update($formatId, $data['quantity']);

        return back();
    }

    public function destroy(int $formatId): RedirectResponse
    {
        $this->cart->update($formatId, 0);

        return back();
    }

    public function currency(Request $request): RedirectResponse
    {
        $data = $request->validate(['currency' => ['required', Rule::in(config('platform.currencies'))]]);
        $this->cart->setCurrency($data['currency']);

        return back();
    }
}
