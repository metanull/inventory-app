<?php

namespace App\Http\Controllers\Pub;

use App\Contracts\StreamableImageFile;
use App\Http\Controllers\Controller;
use App\Models\CollectionImage;
use App\Models\ContributorImage;
use App\Models\ItemImage;
use App\Models\PartnerImage;
use App\Models\PartnerLogo;
use App\Models\PartnerTranslationImage;
use App\Models\TimelineEventImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PictureController extends Controller
{
    /**
     * Maps URL type segment to the Eloquent model class that owns the image.
     *
     * @var array<string, class-string<Model&StreamableImageFile>>
     */
    private const MODEL_MAP = [
        'item-picture' => ItemImage::class,
        'collection-picture' => CollectionImage::class,
        'partner-picture' => PartnerImage::class,
        'partner-translation-picture' => PartnerTranslationImage::class,
        'contributor-picture' => ContributorImage::class,
        'timeline-event-picture' => TimelineEventImage::class,
        'partner-logo' => PartnerLogo::class,
    ];

    /**
     * Serve a public picture by model type and UUID.
     *
     * Route: GET /pub/{type}/{filename}
     * where {filename} is constrained to {uuid}.jpg by the route definition.
     */
    public function show(Request $request, string $type, string $filename): BinaryFileResponse|Response
    {
        // Reject query string parameters — public image URLs must be clean
        if ($request->query->count() > 0) {
            abort(400, 'Query parameters are not accepted.');
        }

        // Resolve model class from the URL type segment
        $modelClass = self::MODEL_MAP[$type] ?? null;
        if ($modelClass === null) {
            abort(404);
        }

        // Strip the .jpg suffix to obtain the UUID primary key
        $id = substr($filename, 0, -4);

        /** @var (Model&StreamableImageFile)|null $image */
        $image = $modelClass::find($id);
        if ($image === null) {
            abort(404);
        }

        $disk = $image->imageDisk();
        $storagePath = $image->imageStoragePath();

        if (! Storage::disk($disk)->exists($storagePath)) {
            abort(404);
        }

        $fullPath = Storage::disk($disk)->path($storagePath);
        $lastModifiedTimestamp = Storage::disk($disk)->lastModified($storagePath);
        $etag = '"'.md5($id.':'.$lastModifiedTimestamp).'"';
        $lastModifiedHttp = gmdate('D, d M Y H:i:s', $lastModifiedTimestamp).' GMT';

        // Honour conditional GET requests so clients can revalidate cheaply
        $ifNoneMatch = $request->header('If-None-Match');
        if ($ifNoneMatch !== null && $ifNoneMatch === $etag) {
            return response('', 304);
        }

        if ($ifNoneMatch === null) {
            $ifModifiedSince = $request->header('If-Modified-Since');
            if ($ifModifiedSince !== null) {
                $since = strtotime($ifModifiedSince);
                if ($since !== false && $lastModifiedTimestamp <= $since) {
                    return response('', 304);
                }
            }
        }

        return response()->file($fullPath, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400, must-revalidate',
            'Last-Modified' => $lastModifiedHttp,
            'ETag' => $etag,
        ]);
    }
}
