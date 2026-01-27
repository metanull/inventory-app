<?php

namespace App\Models;

use App\Traits\HasDisplayOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PartnerTranslationImage extends Model
{
    use HasDisplayOrder, HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'partner_translation_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'alt_text',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Get the columns that should automatically receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Get the partner translation this image belongs to.
     */
    public function partnerTranslation(): BelongsTo
    {
        return $this->belongsTo(PartnerTranslation::class);
    }

    /**
     * Get the next available display order for a partner translation.
     *
     * @deprecated Use static::getNextDisplayOrderFor(['partner_translation_id' => $id]) instead
     */
    public static function getNextDisplayOrderForPartnerTranslation(string $partnerTranslationId): int
    {
        return static::getNextDisplayOrderFor(['partner_translation_id' => $partnerTranslationId]);
    }

    /**
     * Get a query builder scoped to this image's siblings (same partner_translation_id).
     *
     * @return Builder<static>
     */
    protected function getSiblingsQuery(): Builder
    {
        return static::where('partner_translation_id', $this->partner_translation_id);
    }

    /**
     * Tighten the display order for all images of a partner translation, eliminating gaps.
     *
     * @deprecated Use tightenOrdering() instead
     */
    public function tightenOrderingForPartnerTranslation(): void
    {
        $this->tightenOrdering();
    }

    /**
     * Attach an available image to a partner translation, preserving the ID.
     */
    public static function attachFromAvailableImage(AvailableImage $availableImage, string $partnerTranslationId, ?string $altText = null): static
    {
        return DB::transaction(function () use ($availableImage, $partnerTranslationId, $altText) {
            $displayOrder = static::getNextDisplayOrderForPartnerTranslation($partnerTranslationId);

            // Move file from available storage to pictures storage
            $availableDisk = config('localstorage.available.images.disk');
            $availableDir = trim(config('localstorage.available.images.directory'), '/');
            $picturesDisk = config('localstorage.pictures.disk');
            $picturesDir = trim(config('localstorage.pictures.directory'), '/');

            $filename = $availableImage->path; // Already just filename

            // Move the file from images/ to pictures/
            \Illuminate\Support\Facades\Storage::disk($picturesDisk)->writeStream(
                $picturesDir.'/'.$filename,
                \Illuminate\Support\Facades\Storage::disk($availableDisk)->readStream($availableDir.'/'.$filename)
            );
            \Illuminate\Support\Facades\Storage::disk($availableDisk)->delete($availableDir.'/'.$filename);

            $partnerTranslationImage = static::create([
                'id' => $availableImage->id, // Preserve the ID
                'partner_translation_id' => $partnerTranslationId,
                'path' => $filename, // Keep filename unchanged
                'original_name' => $availableImage->original_name ?? '',
                'mime_type' => $availableImage->mime_type ?? '',
                'size' => $availableImage->size ?? 0,
                'alt_text' => $altText ?? $availableImage->comment,
                'display_order' => $displayOrder,
            ]);

            $availableImage->delete();

            return $partnerTranslationImage;
        });
    }

    /**
     * Detach this image and convert it back to an available image, preserving the ID.
     */
    public function detachToAvailableImage(): AvailableImage
    {
        return $this->getConnection()->transaction(function () {
            // Move file from pictures storage back to available storage
            $picturesDisk = config('localstorage.pictures.disk');
            $picturesDir = trim(config('localstorage.pictures.directory'), '/');
            $availableDisk = config('localstorage.available.images.disk');
            $availableDir = trim(config('localstorage.available.images.directory'), '/');

            $filename = $this->path; // Already just filename

            // Move the file from pictures/ back to images/
            \Illuminate\Support\Facades\Storage::disk($availableDisk)->writeStream(
                $availableDir.'/'.$filename,
                \Illuminate\Support\Facades\Storage::disk($picturesDisk)->readStream($picturesDir.'/'.$filename)
            );
            \Illuminate\Support\Facades\Storage::disk($picturesDisk)->delete($picturesDir.'/'.$filename);

            $availableImage = AvailableImage::create([
                'id' => $this->id, // Preserve the ID
                'path' => $filename, // Keep filename unchanged
                'original_name' => $this->original_name,
                'mime_type' => $this->mime_type,
                'size' => $this->size,
                'comment' => $this->alt_text,
            ]);

            $this->delete();

            return $availableImage;
        });
    }
}
