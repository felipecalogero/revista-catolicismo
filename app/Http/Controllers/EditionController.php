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

        // Verifica acesso do usuário
        $user = auth()->user();
        
        // Se a edição foi publicada há mais de 5 meses, todos têm acesso completo
        if ($edition->canBeAccessedByNonSubscribers()) {
            $hasFullAccess = true;
            // Para edições antigas, permitir download para usuários autenticados (não precisa ser assinante)
            $canDownload = $user !== null;
        } else {
            // Edições recentes: apenas assinantes têm acesso completo e podem baixar
            if ($user) {
                $hasFullAccess = $user->canAccessEdition($edition);
                $canDownload = $user->canAccessEditions(); // Pode baixar se tiver assinatura ativa
            } else {
                $hasFullAccess = false; // Não-assinantes veem apenas prévia
                $canDownload = false;
            }
        }

        $edition->increment('views');

        return view('editions.show', compact('edition', 'hasFullAccess', 'canDownload'));
    }

    /**
     * Faz o download do PDF da edição
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

        // Se a edição foi publicada há mais de 5 meses, qualquer usuário autenticado pode baixar
        if ($edition->canBeAccessedByNonSubscribers()) {
            // Permite download para qualquer usuário autenticado
        } else {
            // Para edições recentes, apenas assinantes podem baixar
            if (!$user->canAccessEditions()) {
                return redirect()->route('subscriptions.plans')
                    ->with('error', 'Você precisa de uma assinatura ativa para baixar esta edição.');
            }
        }

        if (!$edition->pdf_file || !Storage::disk('public')->exists($edition->pdf_file)) {
            abort(404, 'Arquivo PDF não encontrado.');
        }

        return Storage::disk('public')->download($edition->pdf_file, $edition->slug . '.pdf');
    }

    /**
     * Visualiza o PDF da edição em formato revista
     */
    public function viewMagazine(string $slug)
    {
        $edition = Edition::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        $user = auth()->user();

        // Verifica se o usuário está autenticado
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Você precisa estar logado para visualizar esta edição.');
        }

        // Se a edição foi publicada há mais de 5 meses, qualquer usuário autenticado pode visualizar
        if ($edition->canBeAccessedByNonSubscribers()) {
            // Permite visualização para qualquer usuário autenticado
        } else {
            // Para edições recentes, apenas assinantes podem visualizar
            if (!$user->canAccessEditions()) {
                return redirect()->route('subscriptions.plans')
                    ->with('error', 'Você precisa de uma assinatura ativa para visualizar esta edição.');
            }
        }

        // Verifica se o PDF existe
        if (!$edition->pdf_file || !Storage::disk('public')->exists($edition->pdf_file)) {
            abort(404, 'Arquivo PDF não encontrado.');
        }

        return view('editions.magazine', compact('edition'));
    }
}
