<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AvailableImage;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AvailableImageController extends Controller
{
    use SearchAndPaginate;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of available images.
     */
    public function index(Request $request): View
    {
        $perPage = $this->resolvePerPage($request);
        $search = trim((string) $request->query('q'));

        $query = AvailableImage::query()->orderBy('created_at', 'desc');

        if ($search !== '') {
            $query->where('comment', 'LIKE', "%{$search}%");
        }

        $availableImages = $query->paginate($perPage)->withQueryString();

        return view('available-images.index', compact('availableImages', 'search'));
    }

    /**
     * Display the specified available image.
     */
    public function show(AvailableImage $availableImage): View
    {
        return view('available-images.show', compact('availableImage'));
    }

    /**
     * Returns the image file for direct viewing (e.g., for use in <img> src attribute).
     */
    public function view(AvailableImage $availableImage)
    {
        $disk = config('localstorage.available.images.disk');
        $path = $availableImage->path;

        if (! \Illuminate\Support\Facades\Storage::disk($disk)->exists($path)) {
            abort(404, 'Image not found');
        }

        $fullPath = \Illuminate\Support\Facades\Storage::disk($disk)->path($path);
        $mimeType = mime_content_type($fullPath);

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=3600',
            'Content-Disposition' => 'inline',
        ]);
    }

    /**
     * Returns the file to the caller for download.
     */
    public function download(AvailableImage $availableImage)
    {
        $disk = config('localstorage.available.images.disk');
        $path = $availableImage->path;

        if (! \Illuminate\Support\Facades\Storage::disk($disk)->exists($path)) {
            abort(404, 'Image not found');
        }

        $fullPath = \Illuminate\Support\Facades\Storage::disk($disk)->path($path);
        $filename = basename($path);

        return response()->download($fullPath, $filename);
    }
}
