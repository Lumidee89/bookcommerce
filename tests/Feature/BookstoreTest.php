<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\DeliveryOption;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\TestGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BookstoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    private function checkout(array $overrides = []): TestResponse
    {
        return $this->post('/checkout', $overrides + ['name' => 'Ada Obi', 'email' => 'ada@example.com', 'accept_terms' => true]);
    }

    public function test_forthcoming_books_cannot_be_added_to_the_cart()
    {
        $book = $this->makeBook(['paperback' => [800000, 5]], ['sales_status' => 'forthcoming']);

        $this->post('/cart', ['format_id' => $this->format($book, 'paperback')->id])->assertSessionHas('error');
        $this->assertSame([], session('cart.items', []));
    }

    public function test_unpublished_books_are_not_visible()
    {
        $book = $this->makeBook([], ['publication_status' => 'draft', 'title' => 'Hidden']);
        $this->get("/books/{$book->slug}")->assertNotFound();
    }

    public function test_totals_are_calculated_on_the_server_and_prices_are_snapshotted()
    {
        $book = $this->makeBook();
        $this->post('/cart', ['format_id' => $this->format($book, 'pdf')->id]);

        $this->checkout(['total' => 1])->assertRedirect();

        $order = Order::with('items')->sole();
        $this->assertSame(500000, $order->total);
        $this->assertSame(OrderStatus::PendingPayment, $order->status);

        $this->format($book, 'pdf')->prices()->update(['amount' => 999999]);
        $this->assertSame(500000, $order->items->first()->unit_price);
    }

    public function test_verified_payment_marks_order_paid_once_and_issues_downloads_and_stock()
    {
        $book = $this->makeBook();
        $this->post('/cart', ['format_id' => $this->format($book, 'pdf')->id]);
        $this->post('/cart', ['format_id' => $this->format($book, 'paperback')->id, 'quantity' => 2]);

        $this->checkout(['phone' => '0800', 'delivery_option_id' => $this->deliveryOption()->id, 'address_line1' => '1 Road', 'city' => 'Lagos', 'country' => 'Nigeria']);
        $transaction = Transaction::sole();
        $this->assertSame(900000 * 2 + 500000 + 250000, $transaction->amount);

        // The browser returning from the gateway proves nothing on its own.
        $this->get('/payments/callback?reference='.$transaction->reference);
        $this->assertSame(OrderStatus::PendingPayment, Order::sole()->status);

        app(PaymentGateway::class)->simulate($transaction, 'success');
        $this->get('/payments/callback?reference='.$transaction->reference)->assertRedirect();
        $this->get('/payments/callback?reference='.$transaction->reference);

        $order = Order::sole();
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertCount(1, $order->downloads);
        $this->assertSame(8, $this->format($book, 'paperback')->fresh()->stock_quantity);
        $this->assertSame(1, $order->statusHistory()->where('to_status', 'paid')->count());
    }

    public function test_amount_mismatch_is_rejected()
    {
        $book = $this->makeBook(['pdf' => [500000]]);
        $this->post('/cart', ['format_id' => $this->format($book, 'pdf')->id]);
        $this->checkout();
        $transaction = Transaction::sole();

        $transaction->amount = 100;
        app(TestGateway::class)->simulate($transaction, 'success');
        $this->get('/payments/callback?reference='.$transaction->reference);

        $this->assertSame('failed', $transaction->fresh()->status->value);
        $this->assertFalse(Order::sole()->isPaid());
    }

    public function test_downloads_require_payment_and_respect_limits()
    {
        config(['platform.downloads.max_attempts' => 2]);
        $book = $this->makeBook(['pdf' => [500000]]);
        $this->post('/cart', ['format_id' => $this->format($book, 'pdf')->id]);
        $this->checkout();
        $transaction = Transaction::sole();
        app(TestGateway::class)->simulate($transaction, 'success');
        $this->get('/payments/callback?reference='.$transaction->reference);

        $download = Order::sole()->downloads()->sole();
        $this->get("/downloads/{$download->token}")->assertOk()->assertDownload();
        $this->get("/downloads/{$download->token}")->assertOk();
        $this->get("/downloads/{$download->token}")->assertStatus(410);
        $this->get('/downloads/not-a-real-token')->assertNotFound();
    }

    public function test_order_page_requires_signed_link_with_token()
    {
        $book = $this->makeBook(['pdf' => [500000]]);
        $this->post('/cart', ['format_id' => $this->format($book, 'pdf')->id]);
        $this->checkout();
        $order = Order::sole();

        $this->get("/orders/{$order->reference}")->assertForbidden();
        $this->get($order->customerUrl())->assertOk();
    }

    public function test_stock_cannot_be_oversold_at_checkout()
    {
        $book = $this->makeBook(['paperback' => [900000, 1]]);
        $this->post('/cart', ['format_id' => $this->format($book, 'paperback')->id, 'quantity' => 3])->assertSessionHas('error');
    }

    public function test_paystack_webhook_requires_valid_signature()
    {
        $this->postJson('/payments/webhook/paystack', ['event' => 'charge.success'])->assertUnauthorized();
    }

    private function deliveryOption()
    {
        return DeliveryOption::create(['name' => 'Lagos', 'currency' => 'NGN', 'fee' => 250000, 'is_active' => true]);
    }
}
