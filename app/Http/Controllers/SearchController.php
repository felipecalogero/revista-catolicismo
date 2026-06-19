<?php

namespace App\Http\Controllers;

use App\Services\SiteSearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        protected SiteSearchService $searchService
    ) {}

    /**
     * Busca global no site (estilo Google): matérias, edições
     * (título + conteúdo das páginas) e acervo histórico.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $type = $this->normalizeType((string) $request->input('type', 'all'));

        if ($q === '') {
            return view('search.index', [
                'q' => '',
                'type' => 'all',
                'results' => null,
            ]);
        }

        $results = $this->searchService->search($q, $type, $request);

        return view('search.index', [
            'q' => $q,
            'type' => $type,
            'results' => $results,
        ]);
    }

    protected function normalizeType(string $type): string
    {
        // "magazine" unificado em "editions"
        if ($type === 'magazine') {
            return 'editions';
        }

        return match ($type) {
            'articles', 'editions', 'archive' => $type,
            default => 'all',
        };
    }
}
