<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
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
        'release_date',
        'views',
        'is_legacy',
        'legacy_issue_number',
        'table_of_contents',
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
        'release_date' => 'date',
        'is_legacy' => 'boolean',
    ];

    /**
     * Relação com o usuário que criou a edição
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Páginas (imagens) desta edição — usado para edições legado/acervo.
     */
    public function pages(): HasMany
    {
        return $this->hasMany(EditionPage::class)->orderBy('sort_order');
    }

    /**
     * Textos integrais desta edição (uma matéria por página/spread).
     */
    public function articles(): HasMany
    {
        return $this->hasMany(EditionArticle::class)->orderBy('sort_order');
    }

    public static function isAbsoluteUrl(?string $value): bool
    {
        return (bool) $value && Str::startsWith($value, ['http://', 'https://']);
    }

    /**
     * URL da capa (storage público ou URL absoluta).
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if (! $this->cover_image) {
            return null;
        }

        if (self::isAbsoluteUrl($this->cover_image)) {
            return $this->cover_image;
        }

        return Storage::url($this->cover_image);
    }

    /**
     * Verifica se a edição pode ser acessada por não-assinantes
     * Não-assinantes só podem acessar edições publicadas há mais de 5 meses
     */
    public function canBeAccessedByNonSubscribers(): bool
    {
        if ($this->is_legacy && config('revista.legacy_counts_as_free_tier', true)) {
            return true;
        }

        // Usa release_date se disponível, caso contrário cai para published_at
        $baseDate = $this->release_date ?? $this->published_at;

        if (! $baseDate) {
            return false;
        }

        // Verifica se foi publicada há mais de 5 meses
        $fiveMonthsAgo = now()->subMonths(5);

        return $baseDate->lte($fiveMonthsAgo);
    }

    /**
     * Acesso público (sem login) ao conteúdo da edição legado.
     */
    public function allowsLegacyPublicAccess(): bool
    {
        return $this->is_legacy && config('revista.legacy_public_access', false);
    }

    /**
     * Scope para filtrar edições acessíveis por não-assinantes
     */
    public function scopeAccessibleByNonSubscribers($query)
    {
        $fiveMonthsAgo = now()->subMonths(5);

        return $query->where(function ($q) use ($fiveMonthsAgo) {
            if (config('revista.legacy_counts_as_free_tier', true)) {
                $q->where('is_legacy', true);
            }
            $q->orWhere(function ($q2) use ($fiveMonthsAgo) {
                $q2->where('release_date', '<=', $fiveMonthsAgo)
                    ->orWhere(function ($q3) use ($fiveMonthsAgo) {
                        $q3->whereNull('release_date')
                            ->where('published_at', '<=', $fiveMonthsAgo);
                    });
            });
        });
    }

    /**
     * Edições cuja vigência de acesso exige assinatura (inverso do acesso “grátis” após ~5 meses).
     */
    public function scopeExclusiveForSubscribers($query)
    {
        $cutoff = now()->subMonths(5);

        $query = $query->where(function ($q) use ($cutoff) {
            $q->where(function ($q2) {
                $q2->whereNull('release_date')->whereNull('published_at');
            })->orWhere(function ($q2) use ($cutoff) {
                $q2->whereNotNull('release_date')
                    ->where('release_date', '>', $cutoff);
            })->orWhere(function ($q2) use ($cutoff) {
                $q2->whereNull('release_date')
                    ->whereNotNull('published_at')
                    ->where('published_at', '>', $cutoff);
            });
        });

        if (config('revista.legacy_counts_as_free_tier', true)) {
            $query->where('is_legacy', false);
        }

        return $query;
    }

    public function scopeNonLegacy($query)
    {
        return $query->where('is_legacy', false);
    }

    public function scopeLegacy($query)
    {
        return $query->where('is_legacy', true);
    }

    /**
     * Acessor para obter a URL do arquivo PDF
     */
    public function getPdfFileUrlAttribute(): ?string
    {
        if (! $this->pdf_file) {
            return null;
        }

        // Se o arquivo começa com http:// ou https://, é uma URL externa
        if (Str::startsWith($this->pdf_file, ['http://', 'https://'])) {
            return $this->pdf_file;
        }

        // Caso contrário, é um arquivo local no storage
        return Storage::url($this->pdf_file);
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
