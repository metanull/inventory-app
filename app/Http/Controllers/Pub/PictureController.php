<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PictureController extends Controller
{
    /**
     * Serve a picture from the public pictures storage.
     *
     * Route: GET /pub/{filename}  (filename constrained to {uuid}.jpg)
     * No model lookup — the UUID is the bare filename on disk.
     */
    public function show(Request $request, string $filename): BinaryFileResponse|Response
    {
        // Reject query string parameters — public image URLs must be clean
        if ($request->query->count() > 0) {
            abort(400, 'Query parameters are not accepted.');
        }

        $disk = Config::string('localstorage.pictures.disk');
        $directory = trim(Config::string('localstorage.pictures.directory'), '/');
        $storagePath = $directory.'/'.$filename;

        if (! Storage::disk($disk)->exists($storagePath)) {
            abort(404);
        }

        $fullPath = Storage::disk($disk)->path($storagePath);
        $lastModifiedTimestamp = Storage::disk($disk)->lastModified($storagePath);
        $etag = '"'.md5($filename.':'.$lastModifiedTimestamp).'"';
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
