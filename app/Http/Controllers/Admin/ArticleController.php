<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = \App\Models\Category::orderBy('name')->get();
        return view('admin.articles.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'video_url' => 'nullable|url',
            'category_id' => 'nullable|exists:categories,id',
            'new_category' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'published' => 'boolean',
        ]);

        // Upload da imagem
        $imagePath = $request->file('image')->store('articles', 'public');

        // Gerar slug único
        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $counter = 1;
        while (Article::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Criar nova categoria se fornecida
        $categoryId = $validated['category_id'] ?? null;
        if (empty($categoryId) && !empty($validated['new_category'])) {
            $newCategorySlug = Str::slug($validated['new_category']);
            $originalCategorySlug = $newCategorySlug;
            $categoryCounter = 1;
            while (\App\Models\Category::where('slug', $newCategorySlug)->exists()) {
                $newCategorySlug = $originalCategorySlug . '-' . $categoryCounter;
                $categoryCounter++;
            }

            $newCategory = \App\Models\Category::create([
                'name' => $validated['new_category'],
                'slug' => $newCategorySlug,
            ]);
            $categoryId = $newCategory->id;
        }

        $article = Article::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'content' => $validated['content'],
            'image' => $imagePath,
            'video_url' => $validated['video_url'] ?? null,
            'category_id' => $categoryId,
            'category' => $categoryId ? \App\Models\Category::find($categoryId)->name : null, // Manter para compatibilidade
            'author' => $validated['author'] ?? null,
            'published' => $request->has('published'),
            'published_at' => $request->has('published') ? now() : null,
        ]);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artigo criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $article = Article::findOrFail($id);
        return view('admin.articles.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $article = Article::findOrFail($id);
        $categories = \App\Models\Category::orderBy('name')->get();
        return view('admin.articles.edit', compact('article', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $article = Article::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'video_url' => 'nullable|url',
            'category_id' => 'nullable|exists:categories,id',
            'new_category' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'published' => 'boolean',
        ]);

        // Upload da nova imagem se fornecida
        if ($request->hasFile('image')) {
            // Deletar imagem antiga
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $imagePath = $request->file('image')->store('articles', 'public');
        } else {
            $imagePath = $article->image;
        }

        // Atualizar slug se o título mudou
        $slug = $article->slug;
        if ($article->title !== $validated['title']) {
            $slug = Str::slug($validated['title']);
            $originalSlug = $slug;
            $counter = 1;
            while (Article::where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Criar nova categoria se fornecida
        $categoryId = $validated['category_id'] ?? null;
        if (empty($categoryId) && !empty($validated['new_category'])) {
            $newCategorySlug = Str::slug($validated['new_category']);
            $originalCategorySlug = $newCategorySlug;
            $categoryCounter = 1;
            while (\App\Models\Category::where('slug', $newCategorySlug)->exists()) {
                $newCategorySlug = $originalCategorySlug . '-' . $categoryCounter;
                $categoryCounter++;
            }

            $newCategory = \App\Models\Category::create([
                'name' => $validated['new_category'],
                'slug' => $newCategorySlug,
            ]);
            $categoryId = $newCategory->id;
        }

        $article->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'content' => $validated['content'],
            'image' => $imagePath,
            'video_url' => $validated['video_url'] ?? null,
            'category_id' => $categoryId,
            'category' => $categoryId ? \App\Models\Category::find($categoryId)->name : null, // Manter para compatibilidade
            'author' => $validated['author'] ?? null,
            'published' => $request->has('published'),
            'published_at' => $request->has('published') && !$article->published_at ? now() : $article->published_at,
        ]);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artigo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $article = Article::findOrFail($id);

        // Deletar imagem
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }

        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artigo deletado com sucesso!');
    }
}
