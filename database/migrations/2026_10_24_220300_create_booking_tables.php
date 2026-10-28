<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('summary', 500)->nullable();
            $table->longText('description')->nullable();
            $table->string('audience')->nullable();
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedBigInteger('fee')->default(0);
            $table->char('currency', 3)->default('NGN');
            $table->string('delivery_method')->default('online');
            $table->text('preparation_instructions')->nullable();
            $table->text('cancellation_policy')->nullable();
            $table->text('rescheduling_policy')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('availability_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('availability_exceptions', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            // Null times = the whole day is blocked; otherwise these replace the usual working hours.
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('manage_token', 64);
            $table->foreignId('consultation_service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_name');
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at');
            $table->string('client_timezone')->default('Africa/Lagos');
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('organisation')->nullable();
            $table->text('needs')->nullable();
            $table->unsignedBigInteger('fee')->default(0);
            $table->char('currency', 3)->default('NGN');
            $table->string('status')->default('pending_payment')->index();
            $table->string('delivery_method')->nullable();
            $table->string('meeting_url')->nullable();
            $table->text('meeting_details')->nullable();
            $table->timestamp('hold_expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->unsignedInteger('reschedule_count')->default(0);
            $table->timestamp('reminder_sent_at')->nullable();
            // Holds the slot start while the booking is active; a unique backstop against double booking (BR-008).
            $table->string('slot_lock')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('booking_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['booking_status_histories', 'bookings', 'availability_exceptions', 'availability_rules', 'consultation_services'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
