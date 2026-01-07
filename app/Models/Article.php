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
