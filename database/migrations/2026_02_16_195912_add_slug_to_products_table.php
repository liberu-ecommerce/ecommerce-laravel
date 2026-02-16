<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->index('slug');
        });

        // Generate slugs for existing products
        DB::table('products')->orderBy('id')->chunk(100, function ($products) {
            foreach ($products as $product) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['slug' => Str::slug($product->name)]);
            }
        });

        // Make slug non-nullable after populating
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropColumn('slug');
        });
    }
};
