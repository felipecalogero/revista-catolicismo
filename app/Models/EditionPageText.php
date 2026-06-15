<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EditionPageText extends Model
{
    use HasFactory;

    protected $fillable = [
        'edition_id',
        'page_label',
        'page_number',
        'body_html',
        'manually_edited',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'manually_edited' => 'boolean',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }
}
