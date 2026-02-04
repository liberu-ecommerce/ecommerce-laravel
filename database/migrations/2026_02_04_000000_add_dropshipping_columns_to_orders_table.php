<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_cost')) {
                $table->decimal('shipping_cost', 10, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'supplier_id')) {
                $table->string('supplier_id')->nullable()->after('shipping_method_id');
            }
            if (!Schema::hasColumn('orders', 'supplier_reference')) {
                $table->string('supplier_reference')->nullable()->after('supplier_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }
            if (Schema::hasColumn('orders', 'supplier_id')) {
                $table->dropColumn('supplier_id');
            }
            if (Schema::hasColumn('orders', 'supplier_reference')) {
                $table->dropColumn('supplier_reference');
            }
        });
    }
};
