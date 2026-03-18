<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EditionController extends Controller
{
    /**
     * Lista todas as edições
     */
    public function index()
    {
        $editions = Edition::where('published', true)
            ->orderBy('release_date', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return view('editions.index', compact('editions'));
    }

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
        $requiresLoginOnly = false;
        
        // Se a edição foi lançada há mais de 5 meses, qualquer usuário LOGADO tem acesso completo
        if ($edition->canBeAccessedByNonSubscribers()) {
            if ($user) {
                $hasFullAccess = true;
                $canDownload = true;
            } else {
                $hasFullAccess = false;
                $canDownload = false;
                // Flag especial para indicar que basta logar para ter acesso (sem precisar de assinatura)
                $requiresLoginOnly = true;
            }
        } else {
            // Edições recentes: apenas assinantes têm acesso completo e podem baixar
            $requiresLoginOnly = false;
            if ($user) {
                $hasFullAccess = $user->canAccessEdition($edition);
                $canDownload = $user->canAccessEditions();
            } else {
                $hasFullAccess = false;
                $canDownload = false;
            }
        }

        $edition->increment('views');

        // Busca outras edições recentes
        $otherEditions = Edition::where('published', true)
            ->where('id', '!=', $edition->id)
            ->orderBy('release_date', 'desc')
            ->orderBy('published_at', 'desc')
            ->take(6)
            ->get();

        return view('editions.show', compact('edition', 'hasFullAccess', 'canDownload', 'otherEditions', 'requiresLoginOnly'));
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
