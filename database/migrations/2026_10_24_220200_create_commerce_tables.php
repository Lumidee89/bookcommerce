<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_options', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->char('currency', 3);
            $table->unsignedBigInteger('fee');
            $table->unsignedSmallInteger('estimated_days_min')->nullable();
            $table->unsignedSmallInteger('estimated_days_max')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('access_token', 64);
            $table->string('customer_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->char('currency', 3);
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('delivery_fee')->default(0);
            $table->unsignedBigInteger('total');
            $table->string('status')->default('pending_payment')->index();
            $table->boolean('requires_shipping')->default(false);
            $table->foreignId('delivery_option_id')->nullable()->constrained()->nullOnDelete();
            $table->string('delivery_option_name')->nullable();
            $table->string('delivery_estimate')->nullable();
            $table->string('shipping_address_line1')->nullable();
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_postal_code')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('shipping_carrier')->nullable();
            $table->string('shipping_reference')->nullable();
            $table->text('customer_notes')->nullable();
            $table->boolean('stock_applied')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('book_format_id')->nullable()->constrained()->nullOnDelete();
            // Purchase-time snapshots (BR-005).
            $table->string('title');
            $table->string('format_label');
            $table->boolean('is_digital');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total');
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable');
            $table->string('gateway');
            $table->string('reference')->unique();
            $table->string('gateway_reference')->nullable();
            $table->unsignedBigInteger('amount');
            $table->char('currency', 3);
            $table->string('status')->default('pending')->index();
            $table->string('channel')->nullable();
            $table->string('failure_reason')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->char('currency', 3);
            $table->string('reason')->nullable();
            $table->string('status')->default('pending');
            $table->string('gateway_reference')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('digital_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_format_id')->nullable()->constrained()->nullOnDelete();
            $table->string('token', 64)->unique();
            $table->string('email');
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('max_downloads');
            $table->timestamp('expires_at');
            $table->timestamp('last_downloaded_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('download_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_download_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['download_events', 'digital_downloads', 'refunds', 'transactions', 'order_status_histories', 'order_items', 'orders', 'delivery_options'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
