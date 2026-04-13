<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
        'release_date' => 'date',
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
     * Scope para filtrar edições acessíveis por não-assinantes
     */
    public function scopeAccessibleByNonSubscribers($query)
    {
        $fiveMonthsAgo = now()->subMonths(5);

        return $query->where(function ($q) use ($fiveMonthsAgo) {
            $q->where('release_date', '<=', $fiveMonthsAgo)
                ->orWhere(function ($q2) use ($fiveMonthsAgo) {
                    $q2->whereNull('release_date')
                        ->where('published_at', '<=', $fiveMonthsAgo);
                });
        });
    }

    /**
     * Edições cuja vigência de acesso exige assinatura (inverso do acesso “grátis” após ~5 meses).
     */
    public function scopeExclusiveForSubscribers($query)
    {
        $cutoff = now()->subMonths(5);

        return $query->where(function ($q) use ($cutoff) {
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
