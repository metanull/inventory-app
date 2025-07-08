<?php

namespace App\Http\Controllers;

use App\Http\Resources\PictureResource;
use App\Models\AvailableImage;
use App\Models\Detail;
use App\Models\Item;
use App\Models\Partner;
use App\Models\Picture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PictureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PictureResource::collection(Picture::all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Picture $picture)
    {
        return new PictureResource($picture);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Picture $picture)
    {
        $validated = $request->validate([
            /** @ignoreParam */
            'id' => 'prohibited',
            'internal_name' => 'required|string|max:255',
            'backward_compatibility' => 'nullable|string|max:255',
            'copyright_text' => 'nullable|string|max:1000',
            'copyright_url' => 'nullable|url|max:255',
            /** @ignoreParam */
            'path' => 'prohibited',
            /** @ignoreParam */
            'upload_name' => 'prohibited',
            /** @ignoreParam */
            'upload_extension' => 'prohibited',
            /** @ignoreParam */
            'upload_mime_type' => 'prohibited',
            /** @ignoreParam */
            'upload_size' => 'prohibited',
            /** @ignoreParam */
            'pictureable_type' => 'prohibited',
            /** @ignoreParam */
            'pictureable_id' => 'prohibited',
        ]);

        $picture->update($validated);
        $picture->refresh();

        return new PictureResource($picture);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Picture $picture)
    {
        // Delete the physical file
        $picturesDisk = config('localstorage.pictures.disk', 'public');
        if (Storage::disk($picturesDisk)->exists($picture->path)) {
            Storage::disk($picturesDisk)->delete($picture->path);
        }

        $picture->delete();

        return response()->noContent();
    }

    /**
     * Attach an AvailableImage to an Item.
     */
    public function attachToItem(Request $request, Item $item)
    {
        return $this->attachPicture($request, $item);
    }

    /**
     * Attach an AvailableImage to a Detail.
     */
    public function attachToDetail(Request $request, Detail $detail)
    {
        return $this->attachPicture($request, $detail);
    }

    /**
     * Attach an AvailableImage to a Partner.
     */
    public function attachToPartner(Request $request, Partner $partner)
    {
        return $this->attachPicture($request, $partner);
    }

    /**
     * Common method to attach a picture to any pictureable model.
     */
    private function attachPicture(Request $request, $pictureable)
    {
        $validated = $request->validate([
            'available_image_id' => 'required|uuid|exists:available_images,id',
            'internal_name' => 'required|string|max:255',
            'backward_compatibility' => 'nullable|string|max:255',
            'copyright_text' => 'nullable|string|max:1000',
            'copyright_url' => 'nullable|url|max:255',
        ]);

        return DB::transaction(function () use ($validated, $pictureable) {
            // Get the AvailableImage
            $availableImage = AvailableImage::findOrFail($validated['available_image_id']);

            // Define storage disks and directories
            $availableImagesDisk = config('localstorage.available.images.disk', 'public');
            $availableImagesDir = config('localstorage.available.images.directory', 'images');
            $picturesDisk = config('localstorage.pictures.disk', 'public');
            $picturesDir = config('localstorage.pictures.directory', 'pictures');

            // Get the filename from the available image path
            $filename = basename($availableImage->path);

            // Create the new path for the picture
            $newPath = $picturesDir.'/'.$filename;

            // Move the file from available images to pictures directory
            if (! Storage::disk($availableImagesDisk)->exists($availableImage->path)) {
                return response()->json(['error' => 'Image file not found'], 404);
            }

            // Copy the file to the pictures directory
            Storage::disk($picturesDisk)->writeStream(
                $newPath,
                Storage::disk($availableImagesDisk)->readStream($availableImage->path)
            );

            // Get file information
            $fullPath = Storage::disk($availableImagesDisk)->path($availableImage->path);
            $fileInfo = getimagesize($fullPath);

            // Create the Picture record
            $picture = new Picture([
                'internal_name' => $validated['internal_name'],
                'backward_compatibility' => $validated['backward_compatibility'] ?? null,
                'copyright_text' => $validated['copyright_text'] ?? null,
                'copyright_url' => $validated['copyright_url'] ?? null,
                'path' => $newPath,
                'upload_name' => $filename,
                'upload_extension' => pathinfo($filename, PATHINFO_EXTENSION),
                'upload_mime_type' => $fileInfo['mime'] ?? 'image/jpeg',
                'upload_size' => Storage::disk($availableImagesDisk)->size($availableImage->path),
            ]);

            // Associate with the pictureable model
            $pictureable->pictures()->save($picture);

            // Delete the available image file and record
            Storage::disk($availableImagesDisk)->delete($availableImage->path);
            $availableImage->delete();

            return new PictureResource($picture);
        });
    }

    /**
     * Returns the file to the caller for download.
     */
    public function download(Picture $picture)
    {
        $picturesDisk = config('localstorage.pictures.disk', 'public');
        $path = $picture->path;

        if (! Storage::disk($picturesDisk)->exists($path)) {
            abort(404, 'Picture file not found');
        }

        $fullPath = Storage::disk($picturesDisk)->path($path);
        $filename = $picture->upload_name;

        return response()->download($fullPath, $filename);
    }

    /**
     * Returns the picture file for direct viewing (e.g., for use in <img> src attribute).
     */
    public function view(Picture $picture)
    {
        $picturesDisk = config('localstorage.pictures.disk', 'public');
        $path = $picture->path;

        if (! Storage::disk($picturesDisk)->exists($path)) {
            abort(404, 'Picture file not found');
        }

        $fileContent = Storage::disk($picturesDisk)->get($path);
        $mimeType = $picture->upload_mime_type;

        return response($fileContent, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="'.$picture->upload_name.'"')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Detach a Picture from an Item and convert it back to AvailableImage.
     */
    public function detachFromItem(Request $request, Item $item, Picture $picture)
    {
        return $this->detachPicture($request, $item, $picture);
    }

    /**
     * Detach a Picture from a Detail and convert it back to AvailableImage.
     */
    public function detachFromDetail(Request $request, Detail $detail, Picture $picture)
    {
        return $this->detachPicture($request, $detail, $picture);
    }

    /**
     * Detach a Picture from a Partner and convert it back to AvailableImage.
     */
    public function detachFromPartner(Request $request, Partner $partner, Picture $picture)
    {
        return $this->detachPicture($request, $partner, $picture);
    }

    /**
     * Common method to detach a picture from any pictureable model and convert it back to AvailableImage.
     */
    private function detachPicture(Request $request, $pictureable, Picture $picture)
    {
        $validated = $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        // Validate that the picture belongs to the given pictureable model
        if ($picture->pictureable_type !== get_class($pictureable) || $picture->pictureable_id !== $pictureable->id) {
            return response()->json(['error' => 'Picture does not belong to this model'], 422);
        }

        return DB::transaction(function () use ($validated, $picture) {
            // Define storage disks and directories
            $picturesDisk = config('localstorage.pictures.disk', 'public');
            $picturesDir = config('localstorage.pictures.directory', 'pictures');
            $availableImagesDisk = config('localstorage.available.images.disk', 'public');
            $availableImagesDir = config('localstorage.available.images.directory', 'images');

            // Verify the picture file exists
            if (! Storage::disk($picturesDisk)->exists($picture->path)) {
                return response()->json(['error' => 'Picture file not found'], 404);
            }

            // Get the filename from the picture path
            $filename = basename($picture->path);

            // Create the new path for the available image
            $newPath = $availableImagesDir.'/'.$filename;

            // Move the file from pictures directory to available images directory
            Storage::disk($availableImagesDisk)->writeStream(
                $newPath,
                Storage::disk($picturesDisk)->readStream($picture->path)
            );

            // Create the AvailableImage record
            $availableImage = AvailableImage::create([
                'path' => $newPath,
                'comment' => $validated['comment'] ?? "Detached from {$picture->pictureable_type} ({$picture->pictureable_id})",
            ]);

            // Delete the picture file and record
            Storage::disk($picturesDisk)->delete($picture->path);
            $picture->delete();

            return response()->json([
                'message' => 'Picture detached successfully',
                'available_image' => [
                    'id' => $availableImage->id,
                    'path' => $availableImage->path,
                    'comment' => $availableImage->comment,
                    'created_at' => $availableImage->created_at,
                    'updated_at' => $availableImage->updated_at,
                ],
            ]);
        });
    }
}
