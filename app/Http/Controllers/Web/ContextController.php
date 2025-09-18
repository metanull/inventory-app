<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreContextRequest;
use App\Http\Requests\Web\UpdateContextRequest;
use App\Models\Context;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContextController extends Controller
{
    use SearchAndPaginate;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        [$contexts, $search] = $this->searchAndPaginate(Context::query(), $request);

        return view('contexts.index', compact('contexts', 'search'));
    }

    public function show(Context $context): View
    {
        return view('contexts.show', compact('context'));
    }

    public function create(): View
    {
        return view('contexts.create');
    }

    public function store(StoreContextRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $makeDefault = (bool) ($payload['is_default'] ?? false);
        unset($payload['is_default']);
        $context = Context::create($payload);
        if ($makeDefault) {
            $context->setDefault();
        }

        return redirect()->route('contexts.show', $context)->with('success', 'Context created successfully');
    }

    public function edit(Context $context): View
    {
        return view('contexts.edit', compact('context'));
    }

    public function update(UpdateContextRequest $request, Context $context): RedirectResponse
    {
        $payload = $request->validated();
        $makeDefault = (bool) ($payload['is_default'] ?? false);
        unset($payload['is_default']);
        $context->update($payload);
        if ($makeDefault) {
            $context->setDefault();
        }

        return redirect()->route('contexts.show', $context)->with('success', 'Context updated successfully');
    }

    public function destroy(Context $context): RedirectResponse
    {
        if ($context->is_default) {
            return redirect()->route('contexts.show', $context)->with('error', 'Cannot delete the default context.');
        }
        $context->delete();

        return redirect()->route('contexts.index')->with('success', 'Context deleted successfully');
    }
}
