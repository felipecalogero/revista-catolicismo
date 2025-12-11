<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Buscar as últimas 10 edições publicadas
        $editions = Edition::where('published', true)
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Transformar em formato compatível com o componente
        $revistas = $editions->map(function ($edition) {
            return [
                'id' => $edition->id,
                'titulo' => $edition->title,
                'edicao' => $edition->title,
                'data' => $edition->published_at ? $edition->published_at->format('d/m/Y') : $edition->created_at->format('d/m/Y'),
                'capa' => $edition->cover_image ? \Illuminate\Support\Facades\Storage::url($edition->cover_image) : '',
                'destaque' => $edition->published_at && $edition->published_at->isToday(),
                'slug' => $edition->slug,
            ];
        })->toArray();

        return view('home', compact('revistas'));
    }
}
