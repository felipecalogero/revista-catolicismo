<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditionPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'edition_id',
        'label',
        'image_path',
        'sort_order',
        'is_spread',
    ];

    protected $casts = [
        'is_spread' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (Str::startsWith($this->image_path, ['http://', 'https://'])) {
            return $this->image_path;
        }

        return Storage::url($this->image_path);
    }
}
