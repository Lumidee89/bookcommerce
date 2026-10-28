<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('speaking_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('contact_name');
            $table->string('organisation');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('event_name');
            $table->text('event_purpose')->nullable();
            $table->date('proposed_date')->nullable();
            $table->string('timezone')->nullable();
            $table->string('venue')->nullable();
            $table->string('country')->nullable();
            $table->string('location')->nullable();
            $table->string('audience_type')->nullable();
            $table->string('audience_size')->nullable();
            $table->string('requested_topic')->nullable();
            $table->string('speaking_duration')->nullable();
            $table->string('budget')->nullable();
            $table->text('travel_arrangements')->nullable();
            $table->text('additional_information')->nullable();
            $table->string('status')->default('new')->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('contact_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('category')->index();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('new')->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('internal_notes', function (Blueprint $table) {
            $table->id();
            $table->morphs('notable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('newsletter_interests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('token', 64)->unique();
            $table->string('source')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamp('provider_synced_at')->nullable();
            $table->string('provider_id')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_interest_subscriber', function (Blueprint $table) {
            $table->foreignId('newsletter_subscriber_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_interest_id')->constrained()->cascadeOnDelete();
            $table->primary(['newsletter_subscriber_id', 'newsletter_interest_id'], 'subscriber_interest_primary');
        });

        Schema::create('subscription_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_subscriber_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->text('consent_text')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action')->index();
            $table->nullableMorphs('auditable');
            $table->string('description');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        foreach (['audit_logs', 'subscription_consents', 'newsletter_interest_subscriber', 'newsletter_subscribers',
            'newsletter_interests', 'internal_notes', 'contact_enquiries', 'speaking_invitations'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
