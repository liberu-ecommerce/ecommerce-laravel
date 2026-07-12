<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ReviewController::vote() increments these; the original table never had them.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            foreach (['helpful_votes', 'unhelpful_votes'] as $col) {
                if (!Schema::hasColumn('reviews', $col)) {
                    $table->unsignedInteger($col)->default(0)->after('approved');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['helpful_votes', 'unhelpful_votes']);
        });
    }
};
