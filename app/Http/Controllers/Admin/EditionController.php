<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Services\PdfCompressor;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $editions = Edition::orderBy('release_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                             ->orWhere('description', 'like', "%{$search}%");
            })
            ->paginate(15)
            ->withQueryString();
            
        return view('admin.editions.index', compact('editions', 'search'));
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
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:25600', // 25MB
            'pdf_file' => 'required|mimes:pdf|max:112640', // 110MB
            'published' => 'boolean',
            'release_month' => 'required|integer|min:1|max:12',
            'release_year' => 'required|integer|min:1951|max:' . (date('Y') + 2),
        ]);

        // Verificar se os arquivos foram realmente recebidos (detectar limite do PHP/Server)
        if (!$request->hasFile('cover_image') || !$request->hasFile('pdf_file')) {
            $message = 'Os arquivos não foram recebidos corretamente. Verifique se o tamanho total não excede o limite permitido pelo servidor (100MB).';
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'error' => 'file_not_received',
                    'php_upload_max' => ini_get('upload_max_filesize'),
                    'php_post_max' => ini_get('post_max_size'),
                ], 422);
            }
            return back()->withErrors(['pdf_file' => $message]);
        }


        try {
            // Upload da imagem da capa
            $coverImagePath = $request->file('cover_image')->store('editions/covers', 'public');

            // Upload do arquivo PDF
            $pdfFilePath = $request->file('pdf_file')->store('editions/pdfs', 'public');

            // Processar Imagem da Capa (Comprimir e Converter para WebP)
            try {
                $imageService = new ImageService();
                $newCoverPath = $imageService->processStorageImage($coverImagePath, 'public', 80, true);
                if ($newCoverPath) {
                    $coverImagePath = $newCoverPath;
                }
            } catch (\Exception $e) {
                \Log::warning('Falha ao otimizar imagem da capa, mantendo original', [
                    'file' => $coverImagePath,
                    'error' => $e->getMessage()
                ]);
            }

            // Comprimir PDF após upload (se falhar, mantém o original)
            try {
                $compressor = new PdfCompressor();
                $compressor->compressStorageFile($pdfFilePath, 'public', 'ebook');
            } catch (\Exception $e) {
                // Se a compressão falhar, continua com o arquivo original
                \Log::warning('Falha ao comprimir PDF, mantendo original', [
                    'file' => $pdfFilePath,
                    'error' => $e->getMessage()
                ]);
            }
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
            'release_date' => $validated['release_year'] . '-' . str_pad($validated['release_month'], 2, '0', STR_PAD_LEFT) . '-01',
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
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:25600', // 25MB
            'pdf_file' => 'nullable|mimes:pdf|max:112640', // 110MB
            'published' => 'boolean',
            'release_month' => 'required|integer|min:1|max:12',
            'release_year' => 'required|integer|min:1951|max:' . (date('Y') + 2),
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

            // Processar Imagem da Capa (Comprimir e Converter para WebP)
            try {
                $imageService = new ImageService();
                $newCoverPath = $imageService->processStorageImage($coverImagePath, 'public', 80, true);
                if ($newCoverPath) {
                    $coverImagePath = $newCoverPath;
                }
            } catch (\Exception $e) {
                \Log::warning('Falha ao otimizar imagem da capa, mantendo original', [
                    'file' => $coverImagePath,
                    'error' => $e->getMessage()
                ]);
            }

            // Comprimir PDF após upload (se falhar, mantém o original)
            try {
                $compressor = new PdfCompressor();
                $compressor->compressStorageFile($pdfFilePath, 'public', 'ebook');
            } catch (\Exception $e) {
                // Se a compressão falhar, continua com o arquivo original
                \Log::warning('Falha ao comprimir PDF, mantendo original', [
                    'file' => $pdfFilePath,
                    'error' => $e->getMessage()
                ]);
            }
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
            'release_date' => $validated['release_year'] . '-' . str_pad($validated['release_month'], 2, '0', STR_PAD_LEFT) . '-01',
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
     * Publish the specified edition.
     */
    public function publish(string $id)
    {
        $edition = Edition::findOrFail($id);

        if ($edition->published) {
            return redirect()->route('admin.editions.index')
                ->with('error', 'Esta edição já está publicada.');
        }

        $edition->update([
            'published' => true,
            'published_at' => $edition->published_at ?? now(),
        ]);

        return redirect()->route('admin.editions.index')
            ->with('success', 'Edição publicada com sucesso!');
    }

    /**
     * Unpublish the specified edition (change to draft).
     */
    public function unpublish(string $id)
    {
        $edition = Edition::findOrFail($id);

        if (!$edition->published) {
            return redirect()->route('admin.editions.index')
                ->with('error', 'Esta edição já está como rascunho.');
        }

        $edition->update([
            'published' => false,
        ]);

        return redirect()->route('admin.editions.index')
            ->with('success', 'Edição alterada para rascunho com sucesso!');
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
