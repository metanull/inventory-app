<?php

namespace App\Models;

use App\Traits\HasJsonFields;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributorTranslation extends Model
{
    use HasFactory, HasJsonFields, HasUuids;

    protected $fillable = [
        'contributor_id',
        'language_id',
        'context_id',
        'name',
        'description',
        'link',
        'alt_text',
        'extra',
        'backward_compatibility',
    ];

    protected $casts = [
        'extra' => 'object',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Get the extra field decoded as an associative array.
     */
    protected function extraDecoded(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->normalizedJson('extra')
        );
    }

    /**
     * Get the contributor that owns the translation.
     */
    public function contributor(): BelongsTo
    {
        return $this->belongsTo(Contributor::class);
    }

    /**
     * Get the language of the translation.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Get the context of the translation.
     */
    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }
}
