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

        $edition->increment('views');

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

        $user = auth()->user();

        // Verifica se o usuário está autenticado
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Você precisa estar logado para baixar esta edição.');
        }

        // Verifica se o usuário pode acessar edições (tem assinatura ativa ou é admin)
        if (!$user->canAccessEditions()) {
            return redirect()->route('subscriptions.plans')
                ->with('error', 'Você precisa de uma assinatura ativa para baixar esta edição.');
        }

        if (!$edition->pdf_file || !Storage::disk('public')->exists($edition->pdf_file)) {
            abort(404, 'Arquivo PDF não encontrado.');
        }

        return Storage::disk('public')->download($edition->pdf_file, $edition->slug . '.pdf');
    }
}
