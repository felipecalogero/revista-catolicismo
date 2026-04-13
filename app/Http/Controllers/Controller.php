<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Monta o padrão para WHERE ... LIKE, escapando % e _ do termo informado.
     */
    protected function searchLikePattern(string $search): string
    {
        return '%'.addcslashes(trim($search), '%_\\').'%';
    }
}
