<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EditionArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'edition_id',
        'page_label',
        'title',
        'slug',
        'body_html',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug) && ! empty($article->title)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }
}
