<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edition_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained('editions')->cascadeOnDelete();
            $table->string('page_label', 32)->nullable();
            $table->string('title');
            $table->string('slug');
            $table->longText('body_html')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['edition_id', 'sort_order']);
            $table->unique(['edition_id', 'slug']);
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edition_articles');
    }
};
