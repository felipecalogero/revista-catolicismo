<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $editions = Edition::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.editions.index', compact('editions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.editions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'pdf_file' => 'required|mimes:pdf|max:102400', // 100MB (102400 KB)
            'published' => 'boolean',
        ]);

        try {
            // Upload da imagem da capa
            $coverImagePath = $request->file('cover_image')->store('editions/covers', 'public');

            // Upload do arquivo PDF
            $pdfFilePath = $request->file('pdf_file')->store('editions/pdfs', 'public');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Erro ao fazer upload: ' . $e->getMessage(),
                    'error' => 'upload_failed',
                    'exception' => get_class($e),
                ], 500);
            }
            return back()->withErrors(['pdf_file' => 'Erro ao fazer upload: ' . $e->getMessage()]);
        }

        // Gerar slug único
        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $counter = 1;
        while (Edition::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $edition = Edition::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'cover_image' => $coverImagePath,
            'pdf_file' => $pdfFilePath,
            'published' => $request->has('published'),
            'published_at' => $request->has('published') ? now() : null,
        ]);

        // Se for requisição AJAX, retornar JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Edição criada com sucesso!',
                'redirect' => route('admin.editions.index')
            ]);
        }

        return redirect()->route('admin.editions.index')
            ->with('success', 'Edição criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $edition = Edition::findOrFail($id);
        return view('admin.editions.show', compact('edition'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $edition = Edition::findOrFail($id);
        return view('admin.editions.edit', compact('edition'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $edition = Edition::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'pdf_file' => 'nullable|mimes:pdf|max:102400', // 100MB (102400 KB)
            'published' => 'boolean',
        ]);

        // Upload da nova imagem da capa se fornecida
        if ($request->hasFile('cover_image')) {
            // Deletar imagem antiga
            if ($edition->cover_image) {
                Storage::disk('public')->delete($edition->cover_image);
            }
            $coverImagePath = $request->file('cover_image')->store('editions/covers', 'public');
        } else {
            $coverImagePath = $edition->cover_image;
        }

        // Upload do novo arquivo PDF se fornecido
        if ($request->hasFile('pdf_file')) {
            // Deletar PDF antigo
            if ($edition->pdf_file) {
                Storage::disk('public')->delete($edition->pdf_file);
            }
            $pdfFilePath = $request->file('pdf_file')->store('editions/pdfs', 'public');
        } else {
            $pdfFilePath = $edition->pdf_file;
        }

        // Atualizar slug se o título mudou
        $slug = $edition->slug;
        if ($edition->title !== $validated['title']) {
            $slug = Str::slug($validated['title']);
            $originalSlug = $slug;
            $counter = 1;
            while (Edition::where('slug', $slug)->where('id', '!=', $edition->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $edition->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'cover_image' => $coverImagePath,
            'pdf_file' => $pdfFilePath,
            'published' => $request->has('published'),
            'published_at' => $request->has('published') && !$edition->published_at ? now() : $edition->published_at,
        ]);

        // Se for requisição AJAX, retornar JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Edição atualizada com sucesso!',
                'redirect' => route('admin.editions.index')
            ]);
        }

        return redirect()->route('admin.editions.index')
            ->with('success', 'Edição atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $edition = Edition::findOrFail($id);

        // Deletar arquivos
        if ($edition->cover_image) {
            Storage::disk('public')->delete($edition->cover_image);
        }
        if ($edition->pdf_file) {
            Storage::disk('public')->delete($edition->pdf_file);
        }

        $edition->delete();

        return redirect()->route('admin.editions.index')
            ->with('success', 'Edição deletada com sucesso!');
    }
}
