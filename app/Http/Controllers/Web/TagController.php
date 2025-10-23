<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreTagRequest;
use App\Http\Requests\Web\UpdateTagRequest;
use App\Models\Tag;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TagController extends Controller
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
        [$tags, $search] = $this->searchAndPaginate(Tag::query(), $request);

        return view('tags.index', compact('tags', 'search'));
    }

    public function show(Tag $tag): View
    {
        return view('tags.show', compact('tag'));
    }

    public function create(): View
    {
        return view('tags.create');
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $tag = Tag::create($request->validated());

        return redirect()->route('tags.show', $tag)
            ->with('success', 'Tag created successfully');
    }

    public function edit(Tag $tag): View
    {
        return view('tags.edit', compact('tag'));
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $tag->update($request->validated());

        return redirect()->route('tags.show', $tag)
            ->with('success', 'Tag updated successfully');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('tags.index')
            ->with('success', 'Tag deleted successfully');
    }
}
