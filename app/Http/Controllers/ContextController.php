<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\IndexContextRequest;
use App\Http\Requests\Api\SetDefaultContextRequest;
use App\Http\Requests\Api\ShowContextRequest;
use App\Http\Requests\Api\StoreContextRequest;
use App\Http\Requests\Api\UpdateContextRequest;
use App\Http\Resources\ContextResource;
use App\Models\Context;

class ContextController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexContextRequest $request)
    {
        $includes = $request->getIncludeParams();
        $pagination = $request->getPaginationParams();

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
     *
     * @return ContextResource
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
        $includes = $request->getIncludeParams();
        if (! empty($includes)) {
            $context->load($includes);
        }

        return new ContextResource($context);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return ContextResource
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
     *
     * @return ContextResource
     */
    public function setDefault(SetDefaultContextRequest $request, Context $context)
    {
        $validated = $request->validated();

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

        return new \App\Http\Resources\MessageResource(['message' => 'Default context cleared']);
    }

    /**
     * Get the default context.
     */
    public function getDefault()
    {
        $context = Context::default()->first();
        if (! $context) {
            return response()->json(
                (new \App\Http\Resources\MessageResource(['message' => 'No default context found']))->toArray(request()),
                404
            );
        }

        return new ContextResource($context);
    }
}
