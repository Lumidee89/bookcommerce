<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->string('author')->default('Yemi Adebanjo');
            $table->longText('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->json('topics')->nullable();
            $table->string('isbn')->nullable();
            $table->string('publisher')->nullable();
            $table->date('published_on')->nullable();
            $table->string('sample_path')->nullable();
            $table->longText('table_of_contents')->nullable();
            // Publication status and sales status are deliberately independent (PRD 5.3).
            $table->string('publication_status')->default('draft')->index();
            $table->string('sales_status')->default('display_only')->index();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('book_book_category', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['book_id', 'book_category_id']);
        });

        Schema::create('book_formats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('label')->nullable();
            $table->string('sku')->unique();
            $table->boolean('is_digital')->default(false);
            // Path on the private "protected" disk; never publicly reachable.
            $table->string('file_path')->nullable();
            $table->integer('stock_quantity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('book_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_format_id')->constrained()->cascadeOnDelete();
            $table->char('currency', 3);
            // Minor units (kobo / cents). Never floats.
            $table->unsignedBigInteger('amount');
            $table->timestamps();
            $table->unique(['book_format_id', 'currency']);
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_format_id')->constrained()->cascadeOnDelete();
            $table->integer('change');
            $table->integer('balance_after')->nullable();
            $table->string('reason');
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['inventory_movements', 'book_prices', 'book_formats', 'book_book_category', 'books', 'book_categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
