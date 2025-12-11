<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EditionController extends Controller
{
    /**
     * Exibe uma edição individual
     */
    public function show(string $slug)
    {
        $edition = Edition::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        return view('editions.show', compact('edition'));
    }

    /**
     * Faz o download do PDF da edição (requer assinatura)
     */
    public function download(string $slug)
    {
        $edition = Edition::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        // TODO: Verificar se o usuário tem assinatura ativa
        // Por enquanto, permite download para usuários autenticados
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado e ter uma assinatura ativa para baixar esta edição.');
        }

        if (!$edition->pdf_file || !Storage::disk('public')->exists($edition->pdf_file)) {
            abort(404, 'Arquivo PDF não encontrado.');
        }

        return Storage::disk('public')->download($edition->pdf_file, $edition->slug . '.pdf');
    }
}
