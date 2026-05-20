<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add dropshipping related columns without using ->after() to avoid
            // relying on a specific existing column order which can fail when
            // running migrations in different environments or on existing DBs.
            $table->string('supplier_id')->nullable();
            $table->string('supplier_order_reference')->nullable();
            $table->string('supplier_tracking_number')->nullable();
            $table->text('supplier_response')->nullable();
            $table->unsignedBigInteger('shipping_method_id')->nullable();
            $table->boolean('is_dropshipped')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_id',
                'supplier_order_reference',
                'supplier_tracking_number',
                'supplier_response',
                'shipping_method_id',
                'is_dropshipped',
            ]);
        });
    }
};
