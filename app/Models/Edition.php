<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Edition extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'pdf_file',
        'published',
        'published_at',
        'views',
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Relação com o usuário que criou a edição
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se a edição pode ser acessada por não-assinantes
     * Não-assinantes só podem acessar edições publicadas há mais de 5 meses
     */
    public function canBeAccessedByNonSubscribers(): bool
    {
        if (!$this->published_at) {
            return false;
        }

        // Verifica se foi publicada há mais de 5 meses
        $fiveMonthsAgo = now()->subMonths(5);
        return $this->published_at->lte($fiveMonthsAgo);
    }

    /**
     * Scope para filtrar edições acessíveis por não-assinantes
     */
    public function scopeAccessibleByNonSubscribers($query)
    {
        $fiveMonthsAgo = now()->subMonths(5);
        return $query->where('published_at', '<=', $fiveMonthsAgo);
    }

    /**
     * Boot do modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($edition) {
            if (empty($edition->slug)) {
                $edition->slug = Str::slug($edition->title);
            }
        });

        static::updating(function ($edition) {
            if ($edition->isDirty('title') && empty($edition->slug)) {
                $edition->slug = Str::slug($edition->title);
            }
        });
    }
}
