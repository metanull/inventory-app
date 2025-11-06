{{--
    Related Counts Sidebar Card
    Displays count of related entities
--}}

@props([
    'model',
    'entity',
])

@php
    $counts = [];
    
    // Check for common relationships
    if (method_exists($model, 'children') && $model->children()->exists()) {
        $counts['Children'] = $model->children->count();
    }
    
    if (method_exists($model, 'images') && $model->images()->exists()) {
        $counts['Images'] = $model->images()->count();
    }
    
    if (method_exists($model, 'translations') && $model->translations()->exists()) {
        $counts['Translations'] = $model->translations->count();
    }
    
    if (method_exists($model, 'links') && $model->links()->exists()) {
        $counts['Links'] = $model->links->count();
    }
    
    if (method_exists($model, 'tags') && $model->tags()->exists()) {
        $counts['Tags'] = $model->tags->count();
    }
    
    if (method_exists($model, 'items') && $model->items()->exists()) {
        $counts['Items'] = $model->items()->count();
    }
@endphp

@if(!empty($counts))
    <x-sidebar.card title="Related Items" icon="chart-bar">
        <dl class="space-y-2">
            @foreach($counts as $label => $count)
                @if($count > 0)
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-600">{{ $label }}</dt>
                        <dd class="font-semibold text-gray-900">{{ $count }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </x-sidebar.card>
@endif
