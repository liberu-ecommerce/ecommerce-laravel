<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// A refund can be created (pending) before a reason is recorded, so `reason`
// must be nullable — the original migration made it NOT NULL with no default.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('reason')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('reason')->nullable(false)->change();
        });
    }
};
