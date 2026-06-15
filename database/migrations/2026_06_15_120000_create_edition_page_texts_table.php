<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edition_page_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained('editions')->cascadeOnDelete();
            $table->string('page_label', 32);
            $table->unsignedInteger('page_number')->nullable();
            $table->longText('body_html')->nullable();
            $table->boolean('manually_edited')->default(false);
            $table->timestamps();

            $table->unique(['edition_id', 'page_label']);
            $table->index(['edition_id', 'page_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edition_page_texts');
    }
};
