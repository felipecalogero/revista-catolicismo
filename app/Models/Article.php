<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'content',
        'image',
        'video_url',
        'category', // Mantido para compatibilidade
        'author',
        'published',
        'published_at',
        'views',
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Relação com o usuário que criou o artigo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relação com a categoria
     */
    public function categoryRelation(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Acessor para obter o nome da categoria
     */
    public function getCategoryNameAttribute(): ?string
    {
        return $this->categoryRelation ? $this->categoryRelation->name : $this->category;
    }

    /**
     * Acessor para obter a URL da imagem (suporta URLs externas e arquivos locais)
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // Se a imagem começa com http:// ou https://, é uma URL externa
        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        // Caso contrário, é um arquivo local no storage
        return Storage::url($this->image);
    }

    /**
     * Verifica se o artigo pode ser acessado por não-assinantes
     * Não-assinantes só podem acessar artigos publicados há mais de 5 meses
     */
    public function canBeAccessedByNonSubscribers(): bool
    {
        if (!$this->published_at) {
            return false;
        }

        $fiveMonthsAgo = now()->subMonths(5);
        return $this->published_at->lte($fiveMonthsAgo);
    }

    /**
     * Scope para filtrar artigos acessíveis por não-assinantes
     */
    public function scopeAccessibleByNonSubscribers($query)
    {
        $fiveMonthsAgo = now()->subMonths(5);
        return $query->where('published_at', '<=', $fiveMonthsAgo);
    }

    /**
     * Retorna uma prévia do conteúdo (primeiros parágrafos ou caracteres)
     */
    public function getPreview(int $maxParagraphs = 3): string
    {
        $content = strip_tags($this->content, '<p><br><strong><em><b><i><u>');
        
        // Tenta pegar os primeiros parágrafos
        preg_match_all('/<p[^>]*>.*?<\/p>/is', $content, $paragraphMatches);
        $paragraphs = $paragraphMatches[0];
        
        if (count($paragraphs) > $maxParagraphs) {
            $preview = implode('', array_slice($paragraphs, 0, $maxParagraphs));
            // Adiciona "..." no final
            $preview .= '<p class="text-gray-500 italic">...</p>';
            return $preview;
        }
        
        // Se não houver parágrafos suficientes, limita por caracteres
        $plainText = strip_tags($content);
        if (strlen($plainText) > 500) {
            $preview = substr($plainText, 0, 500);
            // Tenta cortar em uma palavra completa
            $lastSpace = strrpos($preview, ' ');
            if ($lastSpace !== false) {
                $preview = substr($preview, 0, $lastSpace);
            }
            return '<p>' . nl2br(htmlspecialchars($preview)) . '...</p>';
        }
        
        return $content;
    }

    /**
     * Boot do modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });

        static::updating(function ($article) {
            if ($article->isDirty('title') && empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }
}
