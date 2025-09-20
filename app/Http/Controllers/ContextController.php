<?php

namespace App\Http\Controllers;

use App\Http\Requests\Context\IndexContextRequest;
use App\Http\Requests\Context\ShowContextRequest;
use App\Http\Requests\Context\StoreContextRequest;
use App\Http\Requests\Context\UpdateContextRequest;
use App\Http\Resources\ContextResource;
use App\Models\Context;
use App\Support\Includes\AllowList;
use App\Support\Includes\IncludeParser;
use App\Support\Pagination\PaginationParams;
use Illuminate\Http\Request;

class ContextController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexContextRequest $request)
    {
        $includes = IncludeParser::fromRequest($request, AllowList::for('context'));
        $pagination = PaginationParams::fromRequest($request);

        $query = Context::query()->with($includes);
        $paginator = $query->paginate(
            $pagination['per_page'],
            ['*'],
            'page',
            $pagination['page']
        );

        return ContextResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContextRequest $request)
    {
        $validated = $request->validated();
        $context = Context::create($validated);
        $context->refresh();

        return new ContextResource($context);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowContextRequest $request, Context $context)
    {
        return new ContextResource($context);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContextRequest $request, Context $context)
    {
        $validated = $request->validated();
        $context->update($validated);
        $context->refresh();

        return new ContextResource($context);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Context $context)
    {
        $context->delete();

        return response()->noContent();
    }

    /**
     * Set or unset a context as the default one.
     */
    public function setDefault(Request $request, Context $context)
    {
        $validated = $request->validate([
            'is_default' => 'required|boolean',
        ]);

        if ($validated['is_default'] === true) {
            $context->setDefault();
        } else {
            $context->unsetDefault();
        }
        $context->refresh();

        return new ContextResource($context);
    }

    /**
     * Clear the default flag from any context.
     */
    public function clearDefault()
    {
        Context::clearDefault();

        return response()->json(['message' => 'Default context cleared']);
    }

    /**
     * Get the default context.
     */
    public function getDefault()
    {
        $context = Context::default()->first();
        if (! $context) {
            return response()->json(['message' => 'No default context found'], 404);
        }

        return new ContextResource($context);
    }
}
