<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreAuthorRequest;
use App\Http\Requests\Web\UpdateAuthorRequest;
use App\Models\Author;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    use SearchAndPaginate;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:'.Permission::VIEW_DATA->value)->only(['index', 'show']);
        $this->middleware('permission:'.Permission::CREATE_DATA->value)->only(['create', 'store']);
        $this->middleware('permission:'.Permission::UPDATE_DATA->value)->only(['edit', 'update']);
        $this->middleware('permission:'.Permission::DELETE_DATA->value)->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $query = Author::query();
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('internal_name', 'LIKE', "%{$search}%");
            });
        }

        $allowedSortFields = ['name', 'created_at'];
        $sort = (string) $request->query('sort', 'created_at');
        $dir = strtolower((string) $request->query('dir', 'desc'));
        if (! in_array($sort, $allowedSortFields, true)) {
            $sort = 'created_at';
        }
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'desc';
        }

        $perPage = $this->resolvePerPage($request);
        $authors = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        return view('authors.index', compact('authors', 'search', 'sort', 'dir'));
    }

    public function show(Author $author): View
    {
        return view('authors.show', compact('author'));
    }

    public function create(): View
    {
        return view('authors.create');
    }

    public function store(StoreAuthorRequest $request): RedirectResponse
    {
        $author = Author::create($request->validated());

        return redirect()->route('authors.show', $author)->with('success', 'Author created successfully');
    }

    public function edit(Author $author): View
    {
        return view('authors.edit', compact('author'));
    }

    public function update(UpdateAuthorRequest $request, Author $author): RedirectResponse
    {
        $author->update($request->validated());

        return redirect()->route('authors.show', $author)->with('success', 'Author updated successfully');
    }

    public function destroy(Author $author): RedirectResponse
    {
        $author->delete();

        return redirect()->route('authors.index')->with('success', 'Author deleted successfully');
    }
}
