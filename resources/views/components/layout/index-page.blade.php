@props([
    'entity',
    'title' => null,
    'createRoute' => null,
    'createButtonText' => null,
    'parentContext' => null,
])

@php
    $title = $title ?? \Illuminate\Support\Str::title($entity);
    $createRoute = $createRoute ?? route($entity . '.create');
    $createButtonText = $createButtonText ?? 'Add ' . \Illuminate\Support\Str::singular(\Illuminate\Support\Str::title($entity));
@endphp

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <x-entity.header :entity="$entity" :title="$title">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-ui.button 
                :href="$createRoute" 
                variant="primary" 
                :entity="$entity"
                icon="plus">
                {{ $createButtonText }}
            </x-ui.button>
        @endcan
    </x-entity.header>

    @if(session('status'))
        <x-ui.alert :message="session('status')" type="success" :entity="$entity" />
    @endif

    @if(is_array($parentContext) && $parentContext !== [])
        <x-list.parent-context-header
            :breadcrumbs="$parentContext['breadcrumbs'] ?? []"
            :title="$parentContext['title'] ?? null"
            :description="$parentContext['description'] ?? null"
            :parent-label="$parentContext['parent_label'] ?? null"
            :parent-value="$parentContext['parent_value'] ?? null"
            :parent-url="$parentContext['parent_url'] ?? null"
            :back-url="$parentContext['back_url'] ?? null"
            :back-label="$parentContext['back_label'] ?? 'Back'"
        />
    @endif

    {{ $slot }}
</div>
