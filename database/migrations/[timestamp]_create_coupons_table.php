<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 10, 2);
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->integer('max_uses')->nullable();
            $table->decimal('min_purchase_amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}