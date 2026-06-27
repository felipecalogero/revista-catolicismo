<?php

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $orphans = Article::query()
            ->whereNull('category_id')
            ->where(function ($q) {
                $q->whereNull('category')->orWhere('category', '');
            })
            ->get();

        if ($orphans->isEmpty()) {
            return;
        }

        $fallback = Category::query()->where('slug', 'geral')->first()
            ?? Category::query()->orderBy('id')->first();

        if (! $fallback) {
            $fallback = Category::create([
                'name' => 'Geral',
                'slug' => 'geral',
                'description' => 'Categoria geral',
            ]);
        }

        foreach ($orphans as $article) {
            $article->update([
                'category_id' => $fallback->id,
                'category' => $fallback->name,
            ]);
        }
    }

    public function down(): void
    {
        // Não reverte atribuição de categoria — dados editoriais.
    }
};
