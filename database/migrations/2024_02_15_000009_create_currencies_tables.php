<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Currencies table
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // USD, EUR, GBP
            $table->string('name');
            $table->string('symbol');
            $table->string('symbol_position')->default('before'); // before, after
            $table->integer('decimal_places')->default(2);
            $table->string('thousand_separator')->default(',');
            $table->string('decimal_separator')->default('.');
            $table->decimal('exchange_rate', 10, 6)->default(1.000000);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('rate_updated_at')->nullable();
            $table->timestamps();
        });

        // Add currency to orders
        if (!Schema::hasColumn('orders', 'currency_code')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('currency_code', 3)->default('USD');
                $table->decimal('exchange_rate', 10, 6)->default(1.000000);
            });
        }

        // Add currency to products for multi-currency pricing
        if (!Schema::hasColumn('products', 'currency_code')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('currency_code', 3)->default('USD');
            });
        }

        // Product prices in multiple currencies
        Schema::create('product_currency_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('currency_code', 3);
            $table->decimal('price', 10, 2);
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'currency_code', 'product_variant_id'], 'product_currency_unique');
            $table->foreign('currency_code')->references('code')->on('currencies')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_currency_prices');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('currency_code');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'exchange_rate']);
        });

        Schema::dropIfExists('currencies');
    }
};
