<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * exchange_rate was decimal(10,6): only 4 integer digits (max 9999.999999).
     * Real FX rates overflow that (IDR ~16,500, VND ~25,000, LBP ~89,000) and MySQL
     * rejects the insert with error 1264 "Out of range value". SQLite ignores decimal
     * precision, so the test suite never sees it. Widen to decimal(20,10).
     *
     * NOTE (MySQL verification required): uses ->change() on money columns.
     */
    public function up(): void
    {
        if (Schema::hasColumn('currencies', 'exchange_rate')) {
            Schema::table('currencies', function (Blueprint $table) {
                $table->decimal('exchange_rate', 20, 10)->default(1.0)->change();
            });
        }

        if (Schema::hasColumn('orders', 'exchange_rate')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('exchange_rate', 20, 10)->default(1.0)->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('currencies', 'exchange_rate')) {
            Schema::table('currencies', function (Blueprint $table) {
                $table->decimal('exchange_rate', 10, 6)->default(1.0)->change();
            });
        }

        if (Schema::hasColumn('orders', 'exchange_rate')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('exchange_rate', 10, 6)->default(1.0)->change();
            });
        }
    }
};
