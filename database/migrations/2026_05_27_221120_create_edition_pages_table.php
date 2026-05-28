<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edition_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained('editions')->cascadeOnDelete();
            $table->string('label', 32);
            $table->string('image_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_spread')->default(false);
            $table->timestamps();

            $table->index(['edition_id', 'sort_order']);
            $table->unique(['edition_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edition_pages');
    }
};
