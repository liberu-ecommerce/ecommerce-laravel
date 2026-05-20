<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('seoable_type');
            $table->unsignedBigInteger('seoable_id');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('og_type')->default('website');
            $table->string('twitter_card')->default('summary');
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots')->default('index,follow');
            $table->json('structured_data')->nullable();
            $table->string('focus_keyword')->nullable();
            $table->integer('seo_score')->default(0);
            $table->timestamps();

            $table->index(['seoable_type', 'seoable_id']);
            $table->index(['seo_score']);
            $table->index(['focus_keyword']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
    }
};