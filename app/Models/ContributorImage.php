<?php

namespace App\Models;

use App\Traits\HasDisplayOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContributorImage extends Model
{
    use HasDisplayOrder, HasFactory, HasUuids;

    protected $fillable = [
        'contributor_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'alt_text',
        'display_order',
    ];

    protected $casts = [
        'size' => 'integer',
        'display_order' => 'integer',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Get the contributor this image belongs to.
     */
    public function contributor(): BelongsTo
    {
        return $this->belongsTo(Contributor::class);
    }

    /**
     * Get a query builder scoped to this image's siblings (same contributor_id).
     *
     * @return Builder<static>
     */
    protected function getSiblingsQuery(): Builder
    {
        return static::where('contributor_id', $this->contributor_id);
    }

    /**
     * Attach an available image to a contributor, preserving the ID.
     */
    public static function attachFromAvailableImage(AvailableImage $availableImage, string $contributorId, ?string $altText = null): static
    {
        return DB::transaction(function () use ($availableImage, $contributorId, $altText) {
            $displayOrder = static::getNextDisplayOrderFor(['contributor_id' => $contributorId]);

            // Move file from available storage to pictures storage
            $availableDisk = config('localstorage.available.images.disk');
            $availableDir = trim(config('localstorage.available.images.directory'), '/');
            $picturesDisk = config('localstorage.pictures.disk');
            $picturesDir = trim(config('localstorage.pictures.directory'), '/');

            $filename = $availableImage->path;

            // Move the file from images/ to pictures/
            Storage::disk($picturesDisk)->writeStream(
                $picturesDir.'/'.$filename,
                Storage::disk($availableDisk)->readStream($availableDir.'/'.$filename)
            );
            Storage::disk($availableDisk)->delete($availableDir.'/'.$filename);

            $contributorImage = static::create([
                'id' => $availableImage->id,
                'contributor_id' => $contributorId,
                'path' => $filename,
                'original_name' => $availableImage->original_name ?? '',
                'mime_type' => $availableImage->mime_type ?? '',
                'size' => $availableImage->size ?? 0,
                'alt_text' => $altText ?? $availableImage->comment,
                'display_order' => $displayOrder,
            ]);

            $availableImage->delete();

            return $contributorImage;
        });
    }

    /**
     * Detach this image and convert it back to an available image, preserving the ID.
     */
    public function detachToAvailableImage(): AvailableImage
    {
        return $this->getConnection()->transaction(function () {
            $picturesDisk = config('localstorage.pictures.disk');
            $picturesDir = trim(config('localstorage.pictures.directory'), '/');
            $availableDisk = config('localstorage.available.images.disk');
            $availableDir = trim(config('localstorage.available.images.directory'), '/');

            $filename = $this->path;

            // Move the file from pictures/ back to images/
            Storage::disk($availableDisk)->writeStream(
                $availableDir.'/'.$filename,
                Storage::disk($picturesDisk)->readStream($picturesDir.'/'.$filename)
            );
            Storage::disk($picturesDisk)->delete($picturesDir.'/'.$filename);

            $availableImage = AvailableImage::create([
                'id' => $this->id,
                'path' => $filename,
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
